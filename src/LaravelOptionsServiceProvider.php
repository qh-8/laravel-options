<?php

namespace Qh\LaravelOptions;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Qh\LaravelOptions\Contracts\Repository as RepositoryContract;

class LaravelOptionsServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/create_options_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_options_table.php'),
            ], 'option-migrations');
        }

        $this->app['options']->boot();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton('options', function ($app) {
            return new Repository($app);
        });
        $this->app->alias('options', RepositoryContract::class);
    }
}
