<?php

namespace A17\Twill\Image;

use Illuminate\Support\ServiceProvider;

class ImageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('twill.image', function ($app) {
            return $app->make('A17\Twill\Image\ImageController');
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/twill-image.php', 'twill-image');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'image');
        $this->publishes([
            __DIR__.'/../config/twill-image.php' => config_path('twill-image.php'),
        ], 'config');
        $this->publishes(
            [__DIR__.'/../dist/twill-image.js' => public_path('twill-image.js')],
            'js'
        );
    }
}
