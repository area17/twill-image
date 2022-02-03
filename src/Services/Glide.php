<?php

namespace A17\Twill\Image\Services;

use A17\Twill\Services\MediaLibrary\Glide as TwillGlide;
use Illuminate\Config\Repository as Config;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class Glide extends TwillGlide
{
    public function __construct(Config $config, Application $app, Request $request)
    {
        $config->set('twill.glide.source', $config->get('twill-image.glide.source'));
        $config->set('twill.glide.base_path', $config->get('twill-image.glide.base_path'));

        parent::__construct($config, $app, $request);
    }
}
