<?php

namespace A17\Twill\Image\Models;

use A17\Twill\Models\Media;
use A17\Twill\Models\Model;
use A17\Twill\Image\Services\MediaSrc;
use Illuminate\Contracts\Support\Arrayable;
use A17\Twill\Image\Exceptions\ImageException;
use A17\Twill\Image\Facades\TwillImage;

class Image implements Arrayable
{
    protected $object;

    protected $role;

    protected $media;

    protected $crop;

    protected $width;

    protected $height;

    protected $sources = [];

    protected $layout = "fullWidth";

    protected $sizes;

    /**
     * @param object|Model $object
     * @param string $role
     * @param null|Media $media
     */
    public function __construct($object, $role, $media = null)
    {
        $this->object = $object;

        $this->role = $role;

        $this->media = $media;

        $this->mediaSrcService = new MediaSrc(
            $this->object,
            $this->role,
            $this->media
        );
    }

    /**
     * @param array|string $preset
     * @return $this
     */
    public function preset($preset)
    {
        if (is_array($preset)) {
            $this->applyPreset($preset);
        } elseif (config()->has("twill-image.presets.$preset")) {
            $this->applyPreset(config("twill-image.presets.$preset"));
        } else {
            throw new ImageException("Invalid preset value. Preset must be an array or a string correspondig to an image preset key in the configuration file.");
        }

        return $this;
    }

    public function crop($crop)
    {
        $this->crop = $crop;

        return $this;
    }

    public function width($width)
    {
        $this->width = $width;

        return $this;
    }

    public function height($height)
    {
        $this->height = $height;

        return $this;
    }

    public function sizes($sizes)
    {
        $this->sizes = $sizes;

        return $this;
    }

    public function layout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    public function sources($sources)
    {
        $this->sources = [];

        foreach ($sources as $source) {
            if (!isset($source['media_query']) && isset($source['mediaQuery'])) {
                throw new ImageException("Media query is mandatory in sources.");
            }

            if (!isset($source['crop'])) {
                throw new ImageException("Crop name is mandatory in sources.");
            }

            $this->sources[] = [
                "mediaQuery" => $source['media_query'] ?? $source['mediaQuery'],
                "crop" => $source['crop'],
                "image" => $this->mediaSrcService->generate(
                    $source['crop'],
                    $source['width'] ?? null,
                    $source['height'] ?? null,
                )->toArray()
            ];
        }
    }

    public function render()
    {
        return TwillImage::render($this->toArray());
    }

    public function toArray()
    {
        $arr = [
            "layout" => $this->layout,
            "sizes" => $this->sizes,
            "image" => $this->mediaSrcService->generate(
                $this->crop,
                $this->width,
                $this->height
            )->toArray(),
            "sources" => $this->sources,
        ];

        return array_filter($arr);
    }

    protected function applyPreset($preset)
    {
        if (!isset($preset)) {
            return;
        }

        if (isset($preset['crop'])) {
            $this->crop($preset['crop']);
        }

        if (isset($preset['width'])) {
            $this->width($preset['width']);
        }

        if (isset($preset['height'])) {
            $this->height($preset['height']);
        }

        if (isset($preset['sizes'])) {
            $this->sizes($preset['sizes']);
        }

        if (isset($preset['layout'])) {
            $this->layout($preset['layout']);
        }

        if (isset($preset['sources'])) {
            $this->sources($preset['sources']);
        }
    }
}
