<?php

namespace A17\Twill\Image;

use A17\Twill\Models\Media;
use A17\Twill\Image\Models\Source;
use A17\Twill\Image\Models\Image;

class ImageController
{
    public function source($object, $args = [], Media $media = null): array
    {
        $source = new Source($object, $args, $media);

        return $source->toArray();
    }

    public function render($imageSource, $args = [])
    {
        $args['layout'] = $args['layout'] ?? 'fullWidth';
        $image = new Image($imageSource, $args);

        return $image->view();
    }
}
