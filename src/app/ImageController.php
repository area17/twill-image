<?php

namespace Croustille\Image;

use Illuminate\View\View;
use A17\Twill\Models\Model;
use A17\Twill\Models\Media;
use Croustille\Image\Models\TwillImageSource;
use Croustille\Image\Models\Image;

class ImageController
{
    /**
     * Helper to return a View of component croustille-image for responsive fluid
     * images.
     *
     * @param A17\Twill\Models\Model $model
     * @param string $role
     * @param string $crop
     * @param array $args
     * @param A17\Twill\Models\Media $media
     * @return Illuminate\View\View
     */
    private function image(Model $model, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $source = new TwillImageSource($model, $role, $crop, $media);

        $image = new Image($source, $args);

        return $image->view();
    }

    public function fullWidth(Model $model, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $args['layout'] = 'fullWidth';

        return $this->image($model, $role, $crop, $args, $media);
    }

    public function constrained(Model $model, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $args['layout'] = 'constrained';

        return $this->image($model, $role, $crop, $args, $media);
    }

    public function fixed(Model $model, string $role, string $crop = 'default', $args = [], Media $media = null): View
    {
        $args['layout'] = 'fixed';

        return $this->image($model, $role, $crop, $args, $media);
    }
}
