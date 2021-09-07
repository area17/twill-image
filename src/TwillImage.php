<?php

namespace A17\Twill\Image;

use A17\Twill\Models\Media;
use A17\Twill\Models\Model;
use A17\Twill\Image\Models\Image;
use A17\Twill\Image\ViewModels\ImageViewModel;
use Illuminate\Contracts\Support\Arrayable;

class TwillImage
{
    /**
     * @param object|Model $object
     * @param string $role
     * @param null|Media $media
     * @return Image
     */
    public function image($object, $role, $media = null)
    {
        return new Image($object, $role, $media);
    }

    /**
     * @param Image|array $data
     * @param array $overrides
     * @return string
     */
    public function render($data, $overrides = [])
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        $viewModel = new ImageViewModel($data, $overrides);

        return view('image::wrapper', $viewModel);
    }
}
