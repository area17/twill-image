<?php

namespace A17\Twill\Image;

use Illuminate\Support\Facades\Facade;

class ImageFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'twill.image';
    }
}
