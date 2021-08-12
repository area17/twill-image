<?php

namespace A17\Twill\Image\Models;

use ImageService;
use A17\Twill\Models\Media;
use A17\Twill\Models\Behaviors\HasMedias;
use Illuminate\Contracts\Support\Arrayable;
use A17\Twill\Image\Exceptions\ImageException;
use A17\Twill\Image\Models\Interfaces\ImageSource;

class Source implements Arrayable
{
    public const AUTO_WIDTHS = [
        250,
        500,
        750,
        1000,
        1500,
        2000,
        2500,
        3000,
        3500,
        4000,
        4500,
        5000,
    ];

    public const AUTO_WIDTHS_RATIO = 2.5;

    public const DEFAULT_WIDTH = 1000;

    public const TYPE_GIF = 'image/gif';

    public const TYPE_JPEG = 'image/jpeg';

    public const TYPE_WEBP = 'image/webp';

    protected $model;

    protected $role;

    protected $crop;

    protected $media;

    protected $preset;

    protected $imageArray;

    protected $sources;

    protected $sizes;

    /**
     * Build a Source to be used with A17\Twill\Image\Models\Image
     *
     * @param [A17\Twill\Models\Model] $object of type Media, Block, module, etc.
     * @param string $role Twill Media role
     * @param array $args Arguments
     * @param string $preset Preset name
     * @param Media $media Twill Media instance
     * @throws ImageException
     */
    public function __construct(
        $object,
        $role,
        $args = [],
        $preset = null,
        Media $media = null
    ) {
        $this->role = $role;
        $this->media = $media;
        $this->setModel($object);
        $this->setPreset($preset);
        $this->setCrop($args['crop'] ?? null);
        $this->setImageArray();
        $this->setSources($args['sources'] ?? null);
        $this->setSizes($args['sizes'] ?? null);
    }

    protected function setSources($sources)
    {
        $this->sources = $sources ?? $this->preset['sources'] ?? [
            [
                'crop' => $this->crop,
            ]
        ];
    }

    protected function setSizes($sizes)
    {
        $this->sizes = $sizes ?? $this->preset['sizes'] ?? null;
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

    public function defaultSrc()
    {
        $defaultWidth = $this->defaultWidth();

        return $this->model->image(
            $this->role,
            $this->crop,
            ['w' => $defaultWidth],
            false,
            false,
            $this->media,
        );
    }


    public function lqip()
    {
        $sources = [];

        foreach ($this->sources as $source) {
            $crop = $source['crop'] ?? $this->crop;

            $sources[] = [
                'mediaQuery' => $source['media_query'] ?? null,
                'crop' => $crop,
                'type' => self::TYPE_GIF,
                'srcset' => sprintf(
                    '%s 1x',
                    $this->media
                        ? $this->media->pivot->lqip_data ??
                            ImageService::getTransparentFallbackUrl()
                        : $this->model->lowQualityImagePlaceholder(
                            $this->role,
                            $crop,
                        ),
                ),
            ];
        }

        return [
            'src' => $this->media
                ? $this->media->pivot->lqip_data ??
                    ImageService::getTransparentFallbackUrl()
                : $this->model->lowQualityImagePlaceholder(
                    $this->role,
                    $this->crop,
                ),
            'sources' => $sources,
        ];
    }

    public function srcSets()
    {
        $srcSets = [];

        foreach ($this->sources() ?? [] as $sources) {
            $srcset = [];

            foreach ($sources['sources'] as $source) {
                if (isset($source['src']) && isset($source['width'])) {
                    $srcset[] = sprintf(
                        '%s %dw',
                        $source['src'],
                        $source['width'],
                    );
                }
            }

            $srcSets[] = [
                'srcset' => join(',', $srcset),
                'type' => $sources['type'],
                'mediaQuery' => $sources['mediaQuery'],
                'crop' => $sources['crop'],
                'ratio' => $this->ratio($sources['crop']),
            ];
        }

        return $srcSets;
    }

    protected function sources()
    {
        $sources = [];

        // webp
        if (config('twill-image.webp_support')) {
            foreach ($this->sources as $source) {
                $sources[] = [
                    'mediaQuery' => $source['media_query'] ?? null,
                    'type' => self::TYPE_WEBP,
                    'crop' => $source['crop'] ?? $this->crop,
                    'sources' => $this->imageSources($source, ['fm' => 'webp']),
                ];
            }
        }

        // jpeg
        foreach ($this->sources as $source) {
            $sources[] = [
                'mediaQuery' => $source['media_query'] ?? null,
                'type' => self::TYPE_JPEG,
                'crop' => $source['crop'] ?? $this->crop,
                'sources' => $this->imageSources($source, ['fm' => 'jpg']),
            ];
        }

        return $sources;
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
                    ['w' => $width] + $sourceParams,
                    false,
                    false,
                    $this->media,
                ),
                'width' => $width,
            ];
        }

        return $sourcesList;
    }

    protected function defaultWidth()
    {
        return $this->preset['width'] ?? self::DEFAULT_WIDTH;
    }

    protected function defaultWidths()
    {
        $defaultWidth = $this->defaultWidth();

        return array_filter(self::AUTO_WIDTHS, function ($width) use (
            $defaultWidth
        ) {
            return $width <= $defaultWidth * self::AUTO_WIDTHS_RATIO;
        });
    }



    /**
     * Build crops list
     *
     * @return array
     */
    protected function getAllCrops(): array
    {
        $role = $this->role;

        $crops = array_values(
            $this->model->medias
                ->filter(function ($media) use ($role) {
                    return $media->pivot->role === $role;
                })
                ->map(function ($media) {
                    return $media->pivot->crop;
                })
                ->toArray(),
        );

        return $crops;
    }

    /**
     * Set model crop
     *
     * @param null|string $crop
     * @return void
     */
    protected function setCrop($crop)
    {
        if (isset($crop)) {
            $this->crop = $crop;
            return;
        }

        if (isset($this->preset['crop'])) {
            $this->crop = $this->preset['crop'];
            return;
        }

        $crops = $this->getAllCrops();

        if ($index = array_search('default', $crops)) {
            return $crops[$index];
        }

        if (count($crops) > 1) {
            throw new ImageException(
                "Image role has more than one crop, please add 'crop' key to the configuration preset",
                1,
            );
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

    /**
     * Set model
     *
     * @param object $model
     * @return void
     */
    protected function setModel($model)
    {
        if (!classHasTrait($model, HasMedias::class)) {
            throw new ImageException('Model must use HasMedias trait', 1);
        }

        $this->model = $model;
    }

    protected function setPreset($preset)
    {
        $preset = $preset ?? $this->role;

        if (!config()->has("twill-image.presets.$preset")) {
            $this->preset = null;
            return;
        }

        $this->preset = config("twill-image.presets.$preset");
    }

    protected function setImageArray()
    {
        $imageArray = $this->model->imageAsArray(
            $this->role,
            $this->crop,
            [],
            $this->media,
        );

        if (empty($imageArray)) {
            throw new ImageException(
                "No media was found for role '{$this->role}' and crop '{$this->crop}'",
                1,
            );
        }

        $this->imageArray = $imageArray;
    }

    protected function ratio($crop): string
    {
        $role = $this->role;

        $media = $this->model->medias
            ->filter(function ($media) use ($role, $crop) {
                return $media->pivot->role === $role &&
                    $media->pivot->crop === $crop;
            })
            ->first();

        return $media->pivot->ratio;
    }

    public function toArray()
    {
        return [
            'lqip' => $this->lqip(),
            'sources' => $this->srcSets(),
            'src' => $this->defaultSrc(),
            'width' => $this->width(),
            'height' => $this->height(),
            'sizes' => $this->sizes,
            'alt' => $this->alt(),
        ];
    }
}
