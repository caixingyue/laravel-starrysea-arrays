<?php

namespace Starrysea\Arrays;

use Illuminate\Support\ServiceProvider;

class ArraysServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Arrays', function () {
            return new Arrays();
        });
    }
}
