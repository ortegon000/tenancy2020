<?php

namespace App\Providers;

use App\Tenant\User as TenantUser;
use Hyn\Tenancy\Environment;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (app(Environment::class)->hostname()) {
            auth()->getProvider()->setModel(TenantUser::class);
        }
    }
}
