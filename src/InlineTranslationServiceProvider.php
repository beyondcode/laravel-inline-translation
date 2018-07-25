<?php

namespace BeyondCode\InlineTranslation;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class InlineTranslationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('inline-translation.php'),
            ], 'config');
        }

        $this->loadTranslationsFrom(__DIR__.'/translations', 'laravel-inline-translation');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'inline-translation');

        $this->registerMiddleware(InlineTranslationMiddleware::class);

        $this->addRoute();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'inline-translation');

        $this->app->singleton(InlineTranslation::class);
    }


    /**
     * Register the middleware
     *
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $kernel = $this->app[Kernel::class];
        $kernel->pushMiddleware($middleware);
    }

    protected function addRoute()
    {
        app('router')->post('/_beyondcode/translation', InlineTranslationController::class.'@store');
    }
}
