<?php

namespace Croustille\Image\Models;

use A17\Twill\Models\Media;
use A17\Twill\Models\Behaviors\HasMedias;
use Croustille\Image\Exceptions\ImageException;
use Croustille\Image\Models\Interfaces\ImageSource;

class TwillImageSource implements ImageSource
{
    const AUTO_WIDTHS = [250, 500, 750, 1000, 1500, 2000, 2500, 3000, 3500, 4000];
    const AUTO_WIDTHS_RATIO = 2.5;
    const DEFAULT_WIDTH = 1000;

    protected $model;
    protected $role;
    protected $crop;
    protected $media;
    protected $profile;
    protected $imageArray;

    /**
     * Build a ImageSource to be used with Croustille\Image\Models\Image
     *
     * @param [A17\Twill\Models\Model] $object of type Media, Block, module, etc.
     * @param array $args Arguments
     * @param Media $media Twill Media instance
     * @throws ImageException
     */
    public function __construct($object, $args, Media $media = null)
    {
        $this->setModel($object);
        $this->setProfile($args['profile'] ?? $args['role'] ?? null);
        $this->role = $args['role'];
        $this->crop = $this->profile['crop'];
        $this->media = $media;
        $this->setImageArray($object, $this->role, $this->crop, $media);
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
        $defaultWidth = $this->defaultWidth();

        return $this->model->image($this->role, $this->crop, ['w' => $defaultWidth], false, false, $this->media);
    }

    public function sizesAttr()
    {
        return $this->profile['sizes'] ?? null;
    }

    public function lqip()
    {
        $sources = [];

        foreach ($this->profile['sources'] ?? [] as $source) {
            $sources[] = [
              'mediaQuery' => $source['media_query'] ?? 'default',
              'crop' => $source['crop'] ?? 'default',
              'type' => 'image/gif',
              'srcset' => sprintf('%s 1x', $this->model->lowQualityImagePlaceholder(
                  $this->role,
                  $source['crop'] ?? 'default'
              ))
            ];
        }

        return [
          'src' => $this->model->lowQualityImagePlaceholder($this->role, $this->crop),
          'sources' => $sources,
        ];
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

    protected function sources()
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
                'type' => $this->mimeType($this->extension()),
                'crop' => $source['crop'] ?? 'default',
                'sources' => $this->imageSources($source),
            ];
        }

        return $sources;
    }

    protected function defaultWidth()
    {
        return $this->profile['width'] ?? self::DEFAULT_WIDTH;
    }

    protected function defaultWidths()
    {
        $defaultWidth = $this->defaultWidth();

        return array_filter(
            self::AUTO_WIDTHS,
            function ($width) use ($defaultWidth) {
                return $width <= $defaultWidth * self::AUTO_WIDTHS_RATIO;
            }
        );
    }

    protected function imageSources($mediaQueryConfig, $sourceParams = [])
    {
        $widths = $mediaQueryConfig['widths'] ?? $this->defaultWidths();
        $sourcesList = [];

        foreach ($widths as $width) {
            $params = ['w' => $width];

            $sourcesList[] = [
                    'src' => $this->model->image(
                        $this->role,
                        $mediaQueryConfig['crop'] ?? 'default',
                        $params + $sourceParams,
                        false,
                        false,
                        $this->media
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

    /**
     * Build crops list
     *
     * @return array
     */
    protected function crops()
    {
        $crops = [$this->crop];

        foreach ($this->profile['sources'] ?? [] as $source) {
            if (isset($source['crop'])) {
                $crops[] = $source['crop'];
            }
        }

        return $crops;
    }

    private function extension(): string
    {
        return pathinfo($this->media()->filename, PATHINFO_EXTENSION);
    }

    private function media()
    {
        return $this->media ?? $this->model->imageObject($this->role, $this->crop);
    }

    private function mimeType($extension)
    {
        $types = [
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];

        if (isset($types[$extension])) {
            return $types[$extension];
        }

        return null;
    }

    protected function setModel($model)
    {
        if (! classHasTrait($model, HasMedias::class)) {
            throw new ImageException("Model must use HasMedias trait", 1);
        }

        $this->model = $model;
    }

    protected function setProfile($profile)
    {
        if (! isset($profile)) {
            throw new ImageException("An image profile must be specified", 1);
        }

        if (! config()->has("images.profiles.$profile")) {
            throw new ImageException("The profile key '{$profile}' does not exist in configuration", 1);
        }

        $this->profile = config("images.profiles.$profile");
    }

    protected function setImageArray($model, $role, $crop, $media)
    {
        $imageArray = $model->imageAsArray($role, $crop, [], $media);

        if (empty($imageArray)) {
            throw new ImageException("No media was found for role '{$role}' and crop '{$crop}'", 1);
        }

        $this->imageArray = $imageArray;
    }
}
