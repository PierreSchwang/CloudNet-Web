<?php

namespace App\Http\Middleware;

use App\Services\CloudNetService;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Session;

class CloudNetAuthenticationMiddleware
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
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return Response|RedirectResponse
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (session("cn-session") == null) {
            throw new AuthenticationException("No session present");
        }

        $result = $this->service->renewSession();
        if (!$result) {
            throw new AuthenticationException("Failed to renew JWT");
        }
        Session::put('cn-session', $result);
        Session::save();

        return $next($request);
    }
}
