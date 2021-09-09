<?php

namespace A17\Twill\Image\Services;

use A17\Twill\Models\Block;
use A17\Twill\Models\Media;
use A17\Twill\Models\Model;
use Illuminate\Contracts\Support\Arrayable;
use A17\Twill\Image\Exceptions\ImageException;

class MediaSource implements Arrayable
{
    public const AUTO_WIDTHS_RATIO = 2.5;

    public const DEFAULT_WIDTH = 1000;

    public const FORMAT_WEBP = 'webp';

    protected $model;

    protected $role;

    protected $media;

    protected $crop;

    protected $width;

    protected $srcSetWidths;

    protected $height;

    protected $imageArray;

    /**
     * Create an instance of the service
     *
     * @param Model|Block|object $object
     * @param string $role
     * @param array $args
     * @param Media|object|null $media
     * @param int[] $srcSetWidths
     */
    public function __construct($object, $role, $media = null)
    {
        $this->setModel($object);

        $this->role = $role;
        $this->media = $media;
    }

    public function generate($crop = null, $width = null, $height = null, $srcSetWidths = [])
    {
        $this->setCrop($crop);
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setImageArray();

        $this->srcSetWidths = $srcSetWidths;

        return $this;
    }

    protected function setModel($object)
    {
        if (!classHasTrait($object, 'A17\Twill\Models\Behaviors\HasMedias')) {
            throw new ImageException('Object must use HasMedias trait', 1);
        }

        $this->model = $object;
    }

    /**
     * Set model crop
     *
     * @param null|string $crop
     * @return void
     */
    protected function setCrop($crop)
    {
        if (isset($crop) && is_string($crop)) {
            $this->crop = $crop;
            return;
        }

        $crops = $this->getAllCrops($this->model, $this->role);


        if ($index = array_search('default', $crops)) {
            $this->crop = $crops[$index];
            return;
        }

        if (count($crops) > 1) {
            throw new ImageException(
                "Image role has more than one crop, please add a crop key to the arguments",
                1,
            );
        }

        $this->crop = $crops[0];
    }

    protected function setWidth($width)
    {
        $this->width = $width ?? self::DEFAULT_WIDTH;
    }

    protected function setHeight($height)
    {
        $this->height = $height ?? null;
    }

    protected function setImageArray()
    {
        $this->imageArray = $this->model->imageAsArray(
            $this->role,
            $this->crop,
            [],
            $this->media,
        );

        if (empty($this->imageArray)) {
            throw new ImageException(
                "No media was found for role '{$this->role}' and crop '{$this->crop}'",
                1,
            );
        }
    }

    protected function params($width, $height = null, $format = null)
    {
        $args = [
            'w' => $width,
        ];

        if (isset($height)) {
            $args['h'] = $height;
            $args['fit'] = 'crop';
        }

        if (isset($format)) {
            $args['fm'] = $format;
        }

        return $args;
    }

    protected function calcHeightFromWidth($width)
    {
        $height
            = isset($this->height)
            ? $width * $this->height / $this->width
            : $width * $this->imageArray['height'] / $this->imageArray['width'];

        return (int) $height;
    }

    public function src($width, $height, $format = null)
    {
        $params = $this->params($width, $height, $format);

        return $this->model->image(
            $this->role,
            $this->crop,
            $params,
            false,
            false,
            $this->media,
        );
    }

    public function lqipBase64()
    {
        return $this->media
            ? $this->media->pivot->lqip_data ??
                ImageService::getTransparentFallbackUrl()
            : $this->model->lowQualityImagePlaceholder(
                $this->role,
                $this->crop,
            );
    }

    public function srcset($format = null)
    {
        $range = !empty($this->srcSetWidths) ? collect($this->srcSetWidths) : collect($this->widthRange());

        return $range
            ->map(function ($width) use ($format) {
                return sprintf(
                    "%s %sw",
                    $this->src(
                        $width,
                        isset($this->height) ? $this->calcHeightFromWidth($width) : null,
                        $format
                    ),
                    $width
                );
            })
            ->join(', ');
    }

    protected function widthRange()
    {
        $baseWidth = $this->width;

        // weird science 🥸
        $range = array_merge(
            range(250, 1250, 250),
            range(1500, 10000, 500),
        );

        return array_filter($range, function ($width) use ($baseWidth) {
            return $width <= $baseWidth * self::AUTO_WIDTHS_RATIO;
        });
    }

    public function aspectRatio(): string
    {
        $width = $this->width;

        $height
            = $this->height
            ?? $width
                * $this->imageArray['height']
                / $this->imageArray['width'];

        return (float) ($height / $width);
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

    public function caption(): string
    {
        return $this->model->imageCaption($this->role, $this->media);
    }

    public function extension(): string
    {
        return pathinfo($this->media()->filename, PATHINFO_EXTENSION);
    }

    public function ratio(): string
    {
        return $this->media()->pivot->ratio;
    }

    protected function media()
    {
        return $this->media ?? $this->model->imageObject($this->role, $this->crop);
    }

    public function toArray()
    {
        return [
            "alt" => $this->alt(),
            "aspectRatio" => $this->aspectRatio(),
            "caption" => $this->caption(),
            "crop" => $this->crop,
            "extension" => $this->extension(),
            "height" => isset($this->height) ? $this->height : $this->calcHeightFromWidth($this->width),
            "lqipBase64" => $this->lqipBase64(),
            "ratio" => $this->ratio(),
            "src" => $this->src($this->width, $this->height),
            "srcSet" => $this->srcset(),
            "srcWebp" => $this->src($this->width, $this->height, self::FORMAT_WEBP),
            "srcSetWebp" => $this->srcset(self::FORMAT_WEBP),
            "width" => $this->width,
        ];
    }

    /**
     * Build crops list
     *
     * @return array
     */
    protected function getAllCrops($model, $role): array
    {
        $crops = array_values(
            $model->medias
                ->filter(function ($media) use ($role) {
                    return $media->pivot->role === $role;
                })
                ->map(function ($media) {
                    return $media->pivot->crop;
                })
                ->toArray(),
        );

        if (empty($crops)) {
            throw new ImageException("Can't find any crops for role '$this->role'");
        }

        return $crops;
    }
}
