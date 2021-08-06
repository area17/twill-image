<?php

namespace A17\Twill\Image;

use A17\Twill\Models\Media;
use A17\Twill\Image\Models\TwillImageSource;
use A17\Twill\Image\Models\Image;

class ImageController
{
    public function source($object, $args = [], Media $media = null): array
    {
        $source = new TwillImageSource($object, $args, $media);
        $image = new Image($source, $args);

        return $image->getSourceData();
    }

    public function render($data, $args = [])
    {
        $args['layout'] = $args['layout'] ?? 'fullWidth';
        $image = new Image($data, $args);

        return $image->view();
    }
}
