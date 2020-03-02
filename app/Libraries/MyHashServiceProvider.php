<?php
namespace App\Libraries;
use Illuminate\Support\ServiceProvider;
class MyHashServiceProvider extends ServiceProvider {
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('hash', function() {
          return new MyHasher();
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return array('hash');
    }
}
