<?php

namespace App\Providers;

use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Auth::provider('noop', fn() => new class implements UserProvider {
            public function retrieveById($identifier)
            {
            }

            public function retrieveByToken($identifier, $token)
            {
            }

            public function updateRememberToken(Authenticatable $user, $token)
            {
            }

            public function retrieveByCredentials(array $credentials)
            {
            }

            public function validateCredentials(Authenticatable $user, array $credentials)
            {
            }
        });
    }
}
