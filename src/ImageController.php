<?php

namespace Croustille\Image;

use Illuminate\View\View;
use A17\Twill\Models\Media;
use Croustille\Image\Models\TwillImageSource;
use Croustille\Image\Models\Image;

class ImageController
{
    /**
     * Helper to return a View of component twill-image for responsive fluid
     * images.
     *
     * @param $object
     * @param string $role
     * @param string $crop
     * @param array $args
     * @param A17\Twill\Models\Media $media
     * @return Illuminate\View\View
     */
    private function image($object, string $role, string $crop = 'default', $args = [], Media $media = null): Image
    {
        $source = new TwillImageSource($object, $role, $crop, $media);
        $image = new Image($source, $args);

        return $image;
    }

    public function fullWidth($object, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $args['layout'] = 'fullWidth';
        $image = $this->image($object, $role, $crop, $args, $media);

        return $image->view();
    }

    public function constrained($object, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $args['layout'] = 'constrained';
        $image = $this->image($object, $role, $crop, $args, $media);

        return $image->view();
    }

    public function fixed($object, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $args['layout'] = 'fixed';
        $image = $this->image($object, $role, $crop, $args, $media);

        return $image->view();
    }

    public function getSourceData($object, string $role, string $crop = 'default', $args = [], Media $media = null): array
    {
        $source = new TwillImageSource($object, $role, $crop, $media);
        $image = new Image($source, $args);

        return $image->getSourceData();
    }

    public function fromData($data, $args = [])
    {
        $args['layout'] = $args['layout'] ?? 'fullWidth';
        $image = new Image($data, $args);
        return $image->view();
    }
}
