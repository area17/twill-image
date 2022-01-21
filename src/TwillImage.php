<?php

namespace A17\Twill\Image;

use A17\Twill\Models\Block;
use A17\Twill\Models\Media;
use A17\Twill\Models\Model;
use A17\Twill\Image\Models\Image;
use A17\Twill\Image\Models\StaticImage;
use A17\Twill\Image\ViewModels\ImageViewModel;

class TwillImage
{
    /**
     * @param object|Model|Block $object
     * @param string $role
     * @param null|Media $media
     * @return Image
     */
    public function make($object, $role, $media = null)
    {
        return new Image($object, $role, $media);
    }

    public function makeStatic($src, $preset = [])
    {
        return StaticImage::makeFromSrc($src, $preset);
    }

    /**
     * @param Image|array $data
     * @param array $overrides
     * @return string
     */
    public function render($data, $overrides = [])
    {
        $viewModel = new ImageViewModel($data, $overrides);

        return view('twill-image::wrapper', $viewModel);
    }
}
