<?php
namespace Nava\Dinlr\Laravel;

use Illuminate\Support\ServiceProvider;
use Nava\Dinlr\Client;
use Nava\Dinlr\Config;

class DinlrServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/dinlr.php', 'dinlr'
        );

        $this->app->singleton(Config::class, function ($app) {
            return new Config($app['config']->get('dinlr'));
        });

        $this->app->singleton(Client::class, function ($app) {
            return new Client($app->make(Config::class));
        });

        $this->app->alias(Client::class, 'dinlr');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/dinlr.php' => config_path('dinlr.php'),
        ], 'config');
    }
}
