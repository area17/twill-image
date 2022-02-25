<?php

namespace A17\Twill\Image;

use A17\Twill\Image\Models\StaticImage;

class TwillStaticImage
{
    public function make($args)
    {
        return StaticImage::makeFromSrc($args);
    }
}
