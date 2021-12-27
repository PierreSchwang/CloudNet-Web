<?php

namespace App\Services;

use App\Models\User;
use Auth;
use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Log;

class CloudNetService
{

    private Client $client;
    private string $endpoint;

    public function __construct()
    {
        $this->endpoint = config("soon") ?: "http://192.168.56.1:2812/api/v2/";
        $this->client = new Client();
    }

    public function renewSession(): string|bool
    {
        return $this->newRequest("POST", "session/refresh", fn($response) => $response["token"]);
    }

    public function getNodes(): mixed
    {
        return $this->newRequest("GET", "node", fn($response) => $response);
    }

    /**
     * @throws GuzzleException
     */
    public function tryLogin(string $username, string $password): string|bool
    {
        $basicAuth = base64_encode($username . ":" . $password);
        try {
            $response = $this->client->post($this->endpoint . "auth",
                [
                    RequestOptions::HEADERS => [
                        "Authorization" => "Basic " . $basicAuth
                    ]
                ]);
        } catch (ClientException) {
            // On Client errors (4xx)
            return false;
        } catch (ServerException $exception) {
            // On server errors (5xx)
            Log::error("Something went wrong while requesting CloudNET-REST", [
                "exception" => $exception
            ]);
            return false;
        } catch (ConnectException $exception) {
            // REST endpoint is not available
            Log::error("Couldn't reach CloudNET-REST endpoint", [
                "exception" => $exception
            ]);
            return false;
        }
        if ($response->getStatusCode() != 200) {
            return false;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (!$body['success']) {
            return false;
        }

        return $body['token'];
    }

    /**
     * Tries to create and send a request.
     * @param string $method The HTTP method to use for the request.
     * @param string $path The path to call in relation to the base REST path.
     * @param callable $transformer The transformer to call onto the json body / object.
     * @return mixed Either `false` on error or the result from $transformer.
     */
    private function newRequest(string $method, string $path, callable $transformer): mixed
    {
        // Append authorization headers if valid session exists
        $headers = [];
        if (session("cn-session") != null) {
            $headers = [
                "Authorization" => "Bearer " . session("cn-session")
            ];
        }
        try {
            $response = $this->client->request($method, $this->endpoint . $path, [
                RequestOptions::HEADERS => $headers
            ]);
            $decoded = json_decode($response->getBody()->getContents(), true);
            if ($decoded == NULL) {
                Log::error('Failed to read JSON data', [
                    "data" => $response->getBody()->getContents()
                ]);
            }
            return $transformer($decoded);
        } catch (ClientException $exception) {
            // Destroy session if Forbidden response
            if ($exception->getResponse()->getStatusCode() == 403) {
                Auth::logout();
            }
            // TODO: Handle permission errors
            return false;
        } catch (ConnectException|ServerException $exception) {
            Log::error("Failed to instantiate a new request to $path", [
                "exception" => $exception
            ]);
            die('Can\'t handle anymore requests - Invalid CloudNET response received.');
        } catch (GuzzleException $exception) {
            Log::error("An unhandled exception occurred", [
                "exception" => $exception
            ]);
            return false;
        }
    }

}
