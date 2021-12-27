<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Authentication\LoginRequest;
use App\Services\CloudNetService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Session;

class LoginController extends Controller
{

    private CloudNetService $service;

    /**
     * @param CloudNetService $service
     */
    public function __construct(CloudNetService $service)
    {
        $this->service = $service;
    }


    /**
     * @throws GuzzleException
     */
    public function handleLoginPost(LoginRequest $request): Redirector|Application|RedirectResponse
    {
        $data = $request->validated();
        $result = $this->service->tryLogin($data['username'], $data['password']);
        if (!$result) {
            return back()->withErrors([
                'invalid.login' => 'Invalid credentials'
            ]);
        }
        Session::put('cn-session', $result);
        Session::save();
        return redirect('/');
    }

}
