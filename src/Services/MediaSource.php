<?php

namespace A17\Twill\Image\Services;

use A17\Twill\Models\Block;
use A17\Twill\Models\Media;
use A17\Twill\Models\Model;
use Illuminate\Contracts\Support\Arrayable;
use A17\Twill\Image\Exceptions\ImageException;
use A17\Twill\Services\MediaLibrary\ImageService;

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

    protected $params;

    /**
     * Create an instance of the service
     *
     * @param Model|Block|object $object
     * @param string $role
     * @param array $args
     * @param Media|object|null $media
     * @param array $params
     */
    public function __construct($object, $role, $media = null, $params = [])
    {
        $this->setModel($object);

        $this->role = $role;
        $this->media = $media;
        $this->params = $params;
    }

    public function generate($crop = null, $width = null, $height = null, $srcSetWidths = [], $params=[])
    {
        $this->setCrop($crop);
        $this->setWidth($width);
        $this->setHeight($height);
        $this->setParams($params);
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

    public function setParams($params)
    {
        $this->params = $params ?? [];
    }

    protected function setImageArray()
    {
        $this->imageArray = $this->model->imageAsArray(
            $this->role,
            $this->crop,
            $this->params,
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

        return array_merge($this->params, $args);
    }

    protected function calcHeightFromWidth($width)
    {
        $height
            = isset($this->height)
            ? $width * $this->height / $this->width
            : $width * $this->imageArray['height'] / $this->imageArray['width'];

        return (int) $height;
    }

    public function src()
    {
        return $this->getSrc($this->width, $this->height);
    }

    public function srcWebp()
    {
        return $this->getSrc($this->width, $this->height, self::FORMAT_WEBP);
    }

    protected function getSrc($width, $height, $format = null)
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

    public function srcSet()
    {
        return $this->getSrcset();
    }

    public function srcSetWebp()
    {
        return $this->getSrcSet(self::FORMAT_WEBP);
    }

    protected function getSrcSet($format = null)
    {
        $range = !empty($this->srcSetWidths) ? collect($this->srcSetWidths) : collect($this->widthRange());

        return $range
            ->map(function ($width) use ($format) {
                return sprintf(
                    "%s %sw",
                    $this->getSrc(
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

    public function width()
    {
        return $this->width;
    }

    public function height()
    {
        return isset($this->height) ? $this->height : $this->calcHeightFromWidth($this->width);
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
        return array_merge([
            "alt" => $this->alt(),
            "aspectRatio" => $this->aspectRatio(),
            "caption" => $this->caption(),
            "crop" => $this->crop,
            "extension" => $this->extension(),
            "height" => $this->height(),
            "lqipBase64" => $this->lqipBase64(),
            "ratio" => $this->ratio(),
            "src" => $this->src(),
            "srcSet" => $this->srcSet(),
            "width" => $this->width(),
        ], (config('twill-image.webp_support') ? [
            "srcWebp" => $this->srcWebp(),
            "srcSetWebp" => $this->srcSetWebp(),
        ] : []));
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
