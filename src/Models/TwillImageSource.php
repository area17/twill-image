<?php

namespace A17\Twill\Image\Models;

use A17\Twill\Models\Media;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Image\Exceptions\ImageException;
use A17\Twill\Image\Models\Interfaces\ImageSource;

class TwillImageSource implements ImageSource
{
    const AUTO_WIDTHS = [250, 500, 750, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 4500, 5000];

    const AUTO_WIDTHS_RATIO = 2.5;

    const DEFAULT_WIDTH = 1000;

    const TYPE_GIF = 'image/gif';

    const TYPE_JPEG = 'image/jpeg';

    const TYPE_WEBP = 'image/webp';

    protected $model;

    protected $role;

    protected $crop;

    protected $media;

    protected $preset;

    protected $imageArray;

    /**
     * Build an ImageSource to be used with A17\Twill\Image\Models\Image
     *
     * @param [A17\Twill\Models\Model] $object of type Media, Block, module, etc.
     * @param array $args Arguments
     * @param Media $media Twill Media instance
     * @throws ImageException
     */
    public function __construct($object, $args, Media $media = null)
    {
        $this->role = $args['role'];
        $this->media = $media;
        $this->setModel($object);
        $this->setPreset($args['preset'] ?? $args['role'] ?? null);
        $this->setCrop($this->preset['crop'] ?? null);
        $this->setImageArray();
    }

    /**
     * Provide the width of the default crop
     *
     * @return int
     */
    public function width(): int
    {
        return $this->imageArray['width'];
    }

    /**
     * Provide the height of the default crop
     *
     * @return int
     */
    public function height(): int
    {
        return $this->imageArray['height'];
    }

    /**
     * Provide the text description of the image
     *
     * @return string
     */
    public function alt(): string
    {
        return $this->model->imageAltText($this->role, $this->media);
    }

    /**
     * Provide the caption of the image
     *
     * @return string
     */
    public function caption(): string
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
                'crop' => $sources['crop'],
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
        return $this->preset['sizes'] ?? null;
    }

    public function lqip()
    {
        $sources = [];

        foreach ($this->preset['sources'] ?? [] as $source) {
            $crop = $source['crop'] ?? $this->crop;

            $sources[] = [
              'mediaQuery' => $source['media_query'] ?? null,
              'crop' => $crop,
              'type' => self::TYPE_GIF,
              'srcset' => sprintf(
                  '%s 1x',
                  $this->model->lowQualityImagePlaceholder($this->role, $crop)
              )
            ];
        }

        return [
          'src' => $this->model->lowQualityImagePlaceholder($this->role, $this->crop),
          'sources' => $sources,
        ];
    }

    protected function sources()
    {
        $sources = [];

        // webp
        if (config('twill-image.webp_support')) {
            foreach ($this->preset['sources'] ?? [] as $source) {
                $sources[] = [
                    'mediaQuery' => $source['media_query'] ?? null,
                    'type' => self::TYPE_WEBP,
                    'crop' => $source['crop'] ?? $this->crop,
                    'sources' => $this->imageSources($source, ['fm' => 'webp']),
                ];
            }
        }

        // jpeg
        foreach ($this->preset['sources'] ?? [] as $source) {
            $sources[] = [
                'mediaQuery' => $source['media_query'] ?? null,
                'type' => self::TYPE_JPEG,
                'crop' => $source['crop'] ?? $this->crop,
                'sources' => $this->imageSources($source, ['fm' => 'jpg']),
            ];
        }

        return $sources;
    }

    protected function defaultWidth()
    {
        return $this->preset['width'] ?? self::DEFAULT_WIDTH;
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
            $sourcesList[] = [
                'src' => $this->model->image(
                    $this->role,
                    $mediaQueryConfig['crop'] ?? $this->crop,
                    ['w' => $width] + $sourceParams
                ),
                'width' => $width,
            ];
        }

        return $sourcesList;
    }

    /**
     * Build crops list
     *
     * @return array
     */
    protected function crops(): array
    {
        $role = $this->role;

        $crops = array_values(
            $this->model->medias->filter(function ($media) use ($role) {
                return $media->pivot->role === $role;
            })->map(function ($media) {
                return $media->pivot->crop;
            })->toArray()
        );

        return $crops;
    }

    protected function setCrop($crop)
    {
        if (isset($crop)) {
            $this->crop = $crop;
            return;
        }

        $crops = $this->crops();

        if ($index = array_search('default', $crops)) {
            return $crops[$index];
        }

        if (count($crops) > 1) {
            throw new ImageException("Image role has more than one crop, please add 'crop' key to the configuration preset", 1);
        }

        $this->crop = $crops[0];
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

    protected function setPreset($preset)
    {
        if (! isset($preset)) {
            throw new ImageException("An image preset must be specified", 1);
        }

        if (! config()->has("twill-image.presets.$preset")) {
            throw new ImageException("The preset key '{$preset}' does not exist in configuration", 1);
        }

        $this->preset = config("twill-image.presets.$preset");
    }

    protected function setImageArray()
    {
        $imageArray = $this->model->imageAsArray($this->role, $this->crop, [], $this->media);

        if (empty($imageArray)) {
            throw new ImageException("No media was found for role '{$this->role}' and crop '{$this->crop}'", 1);
        }

        $this->imageArray = $imageArray;
    }
}
