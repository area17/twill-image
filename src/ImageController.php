<?php

namespace A17\Twill\Image;

use A17\Twill\Models\Media;
use A17\Twill\Image\Models\Source;
use A17\Twill\Image\Models\Image;

class ImageController
{
    public function sources(
        $object,
        $role,
        $args = [],
        $preset = null
    ): array {
        // TODO
        $medias = $object->imageObjects($role, $args['crop'] ?? 'default');

        return $medias->map(
            function ($media) use ($object, $role, $args, $preset) {
                $source = new Source($object, $role, $args, $preset, $media);

                return $source->toArray();
            }
        )->toArray();
    }

    public function source(
        $object,
        $role,
        $args = [],
        $preset = null,
        Media $media = null
    ): array {
        $source = new Source($object, $role, $args, $preset, $media);

        return $source->toArray();
    }

    public function render($imageSource, $args = [])
    {
        $args['layout'] = $args['layout'] ?? 'fullWidth';
        $image = new Image($imageSource, $args);

        return $image->view();
    }

    public function image(
        $object,
        $role,
        $args = [],
        $preset = null,
        Media $media = null
    ) {
        $source = $this->source($object, $role, $args, $preset, $media);

        return $this->render($source, $args);
    }
}
