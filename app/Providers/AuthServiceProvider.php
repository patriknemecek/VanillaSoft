<?php

namespace App\Providers;

use App\Auth\ApiTokenAuthGuard;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        Auth::viaRequest('api_token', function (Request $request) {
            $userId = $request->route()->parameter('user');
            $apiToken = $request->get('api_token');

            $query = User::whereHas('tokens', function ($query) use ($apiToken) {
                return $query->where('token', $apiToken);
            });

            return $query->find($userId);
        });
    }
}
