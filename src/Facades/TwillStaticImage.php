<?php

namespace A17\Twill\Image\Facades;

use Illuminate\Support\Facades\Facade;

class TwillStaticImage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'twill.static-image';
    }
}
