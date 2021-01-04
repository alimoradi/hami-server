<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Libraries\SmsAccountVerification;
use App\Interfaces\AccountVerifier;
use App\User;

class AccountVerificationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {


    }
}
