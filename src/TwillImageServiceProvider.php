<?php

namespace A17\Twill\Image;

use Illuminate\Support\ServiceProvider;
use A17\Twill\Image\Providers\RouteServiceProvider;

class TwillImageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('twill.image', function ($app) {
            return $app->make('A17\Twill\Image\TwillImage');
        });

        if (config('twill-image.static_image_support')) {
            $this->app->singleton('twill.static-image', function ($app) {
                return $app->make('A17\Twill\Image\TwillStaticImage');
            });
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../config/twill-image.php',
            'twill-image',
        );

        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'twill-image');
        $this->publishes(
            [
                __DIR__ . '/../config/twill-image.php' => config_path(
                    'twill-image.php',
                ),
            ],
            'config',
        );
    }
}
