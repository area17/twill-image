<?php

namespace A17\Twill\Image\Providers;

use Illuminate\Routing\Router;
use A17\Twill\Image\Http\Controllers\GlideController;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();
    }

    /**
     * @param Router $router
     * @return void
     */
    public function map(Router $router)
    {
        if (config('twill-image.static_image_support')) {
            $basePath = config('twill-image.glide.base_path');

            $router->get("/$basePath/{path}", GlideController::class)
                ->where('path', '.*');
        }
    }
}
