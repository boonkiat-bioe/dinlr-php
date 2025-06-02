<?php
namespace Nava\Dinlr\Laravel;

use Illuminate\Support\ServiceProvider;
use Nava\Dinlr\OAuthClient;
use Nava\Dinlr\OAuthConfig;

class OAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/dinlr-oauth.php', 'dinlr-oauth'
        );

        $this->app->singleton(OAuthConfig::class, function ($app) {
            return new OAuthConfig($app['config']->get('dinlr-oauth'));
        });

        $this->app->singleton(OAuthClient::class, function ($app) {
            return new OAuthClient($app->make(OAuthConfig::class));
        });

        $this->app->alias(OAuthClient::class, 'dinlr-oauth');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/dinlr-oauth.php' => config_path('dinlr-oauth.php'),
        ], 'config');
    }
}
