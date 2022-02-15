<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use App\Libraries\AccessManagerService;
use App\Libraries\BlueRoomVoiceCall;
use App\Libraries\SmsAccountVerification;

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
    public function register()
    {
        $this->app->bind('App\Interfaces\AccountVerifier', function($app){
            return new SmsAccountVerification();
        });
        $this->app->bind('App\Interfaces\VoiceCallMaker', function($app){
            return new BlueRoomVoiceCall();
        });

        $this->app->bind('App\Interfaces\UserAccessManager', function($app){
            return new AccessManagerService();
        });
    }
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        Gate::define('see-user', function ($user) {
            return $user->id == auth()->user()->id;
        });
        Passport::tokensCan(
            config("scopes.all")
        );

        //
    }
}
