<?php

namespace Croustille\Image\Models;

use Exception;
use A17\Twill\Models\Media;
use Croustille\Image\Models\Interfaces\ImageSource;

class TwillImageSource implements ImageSource
{
    protected $model;

    protected $role;

    protected $crop;

    protected $media;

    protected $profile;

    protected $imageArray;

    /**
     * Build a ImageSource to be used with Croustille\Image\Models\Image
     *
     * @param [A17\Twill\Models\Model] $model of type Media, Block, module, etc.
     * @param string $role Twill role defined in Block crops or mediaParams
     * @param string $crop Twill crop defined in Block crops or mediaParams
     * @param Media $media Twill Media instance
     */
    public function __construct($model, string $role, string $crop = 'default', Media $media = null)
    {
        if (!method_exists($model, 'imageAsArray') || !method_exists($model, 'image') || !method_exists($model, 'imageAltText') || !method_exists($model, 'imageCaption')) {
            throw new Exception("Model doesn't have methods 'image', 'imageAltText', 'imageCaption' and/or 'imageAsArray'", 1);
        }

        $this->model = $model;

        $this->role = $role;

        $this->crop = $crop;

        $this->media = $media;

        $this->imageArray = $this->model->imageAsArray($this->role, $this->crop, [], $this->media);

        $profile = config("images.roles.$this->role");
        $this->profile = config("images.profiles.$profile");
    }

    public function width()
    {
        return $this->imageArray['width'];
    }

    public function height()
    {
        return $this->imageArray['height'];
    }

    public function alt()
    {
        return $this->model->imageAltText($this->role, $this->media);
    }

    public function caption()
    {
        return $this->model->imageCaption($this->role, $this->media);
    }

    public function srcSets()
    {
        $srcSets = [];

        foreach ($this->sources() ?? [] as $sources) {
            $srcset = [];
            foreach ($sources['sources'] as $source) {
                if (isset($source['src']) && isset($source['width'])) {
                    $srcset[] = sprintf('%s %dw', $source['src'], $source['width']);
                }
            }
            $srcSets[] = [
                'srcset' => join(',', $srcset),
                'type' => $sources['type'],
                'mediaQuery' => $sources['mediaQuery'],
                'crop' => $sources['crop']
            ];
        }

        return $srcSets;
    }

    public function defaultSrc()
    {
        $defaultWidth = $this->profile['default_width'] ?? 2000;

        return $this->model->image($this->role, $this->crop, ['w' => $defaultWidth], false, false, $this->media);
    }

    public function sizesAttr()
    {
        return $this->profile['sizes'] ?? null;
    }

    public function lqip()
    {
        return $this->model->lowQualityImagePlaceholder($this->role, $this->crop);
    }

    public function dataAttr()
    {
        $data = [];
        $cropRatios = [];

        foreach ($this->crops() as $crop) {
            $image_object = $this->model->imageObject($this->role, $crop);
            $cropRatios[] = $this->role . '-' . $crop . '-' . $image_object->pivot->ratio ?? null;
        }

        $data['role-crop-ratio'] = join(' ', $cropRatios);
        $data['role'] = $this->role;

        return $data;
    }

    private function sources()
    {
        $sources = [];

        // webp
        if (config('images.webp_support')) {
            foreach ($this->profile['sources'] ?? [] as $source) {
                $sources[] = [
                    'mediaQuery' => $source['media_query'] ?? 'default',
                    'type' => 'image/webp',
                    'crop' => $source['crop'] ?? 'default',
                    'sources' => $this->imageSources($source, ['fm' => 'webp']),
                ];
            }
        }

        // jpeg
        foreach ($this->profile['sources'] ?? [] as $source) {
            $sources[] = [
                'mediaQuery' => $source['media_query'] ?? 'default',
                'type' => 'image/jpeg',
                'crop' => $source['crop'] ?? 'default',
                'sources' => $this->imageSources($source, ['fm' => 'jpg']),
            ];
        }

        return $sources;
    }

    private function imageSources($mediaQueryConfig, $sourceParams = [])
    {
        $sourcesList = [];

        foreach ($mediaQueryConfig['widths'] as $width) {
            $params = ['w' => $width];

            $sourcesList[] = [
                    'src' => $this->model->image(
                        $this->role,
                        $mediaQueryConfig['crop'] ?? 'default',
                        $params + $sourceParams
                    ),
                    'crop' => $source['crop'] ?? 'default',
                    'width' => $width,
                    'lqip' => $this->model->lowQualityImagePlaceholder(
                        $source['role'] ?? $this->role,
                        $mediaQueryConfig['crop'] ?? 'default'
                    )
                ];
        }

        return $sourcesList;
    }

    private function crops()
    {
        // build crops list
        $crops = [$this->crop];

        foreach ($this->profile['sources'] ?? [] as $source) {
            if (isset($source['crop'])) {
                $crops[] = $source['crop'];
            }
        }

        return $crops;
    }
}
