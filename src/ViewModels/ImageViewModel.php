<?php

namespace A17\Twill\Image\ViewModels;

use Spatie\ViewModels\ViewModel;
use A17\Twill\Image\Services\StyleService;
use Illuminate\Contracts\Support\Arrayable;

class ImageViewModel extends ViewModel implements Arrayable
{
    /**
     * @var null|string $backgroundColor Image background color
     */
    protected $backgroundColor;

    /**
     * @var string $layout One of the available layout "fullWidth", "constrained" or "fixed"
     */
    protected $layout;

    /**
     * @var string $loading <img> loading attribute "lazy" (default) or "eager"
     */
    protected $loading;

    /**
     * @var bool $lqip Display a low-quality placeholder
     */
    protected $lqip;

    /**
     * @var string $sizes Sizes attributes
     */
    protected $sizes;

    /**
     * @var int $height Height of the image
     */
    protected $height;

    /**
     * @var int $width Width of the image
     */
    protected $width;

    /**
     * @var int $wrapperClass CSS class added to the wrapper element
     */
    protected $wrapperClass;

    /**
     * @var string $alt Description of the image
     */
    protected $alt;

    protected $sources = null;
    protected $lqipSrc = null;
    protected $lqipSources = null;

    public function __construct(array $data, $args = [])
    {
        $this->data = $data;

        $this->setAttributes($args);
        $this->setImageAttributes();
        $this->setSourcesAttributes();
        $this->setLqipAttributes();

        $this->styleService = new StyleService();
        $this->styleService->setup(
            $this->layout,
            $this->backgroundColor,
            $this->width,
            $this->height
        );
    }

    /**
     * Process arguments and apply default values.
     *
     * @param array $args
     * @return void
     */
    protected function setAttributes($args)
    {
        $this->backgroundColor
            = $args['backgroundColor']
            ?? config('twill-image.background_color')
            ?? 'transparent';

        $this->layout
            = $args['layout']
            ?? $this->data['layout']
            ?? 'fullWidth';

        $this->loading = $args['loading'] ?? 'lazy';

        $this->lqip
            = $args['lqip']
            ?? config('twill-image.lqip')
            ?? true;

        $this->sizes
            = $args['sizes']
            ?? $this->data['sizes']
            ?? $this->defaultSizesAttribute();

        $this->wrapperClass = $args['class'] ?? null;

        $this->width = $args['width'] ?? null;

        $this->height = $args['height'] ?? null;
    }

    protected function setImageAttributes()
    {
        $image = $this->data['image'];

        $this->src = $image['src'];
        $this->alt = $image['alt'];
        $this->width = $this->width ?? $image['width'];
        $this->height = $this->height ?? $image['height'];
    }

    protected function setSourcesAttributes()
    {
        if (!isset($this->data['sources'])) {
            $this->sources = null;
            return;
        }

        $sources = [];

        foreach ($this->data['sources'] as $source) {
            $mediaQuery = $source['mediaQuery'];
            $image = $source['image'];

            $sources[] = $this->buildSourceObject(
                $image['srcSet'],
                $image['aspectRatio'],
                $this->mimeType($image['extension']),
                $mediaQuery
            );

            if (config('twill-image.webp_support')) {
                $sources[] = $this->buildSourceObject(
                    $image['srcSetWebp'],
                    $image['aspectRatio'],
                    $this->mimeType("webp"),
                    $mediaQuery
                );
            }
        }

        $image = $this->data['image'];

        $sources[] = $this->buildSourceObject(
            $image['srcSet'],
            $image['aspectRatio'],
            $this->mimeType($image['extension'])
        );

        if (config('twill-image.webp_support')) {
            $sources[] = $this->buildSourceObject(
                $image['srcSet'],
                $image['aspectRatio'],
                $this->mimeType("webp"),
            );
        }

        $this->sources = $sources;
    }

    protected function buildSourceObject($srcSet, $aspectRatio, $type = null, $mediaQuery = null)
    {
        return array_filter([
            'srcset' => $srcSet,
            'aspectRatio' => $aspectRatio * 100,
            'type' => $type,
            'mediaQuery' => $mediaQuery,
        ]);
    }

    protected function mimeType($extension)
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

    protected function defaultSizesAttribute()
    {
        switch ($this->layout) {
            // If screen is wider than the max size, image width is the max size,
            // otherwise it's the width of the screen
            case 'constrained':
                return '(min-width:' .
                    $this->width .
                    'px) ' .
                    $this->width .
                    'px, 100vw';

            // Image is always the same width, whatever the size of the screen
            case 'fixed':
                return $this->width . 'px';

            // Image is always the width of the screen
            case 'fullWidth':
                return '100vw';

            default:
                return null;
        }
    }

    protected function wrapperClasses()
    {
        $layout = $this->layout;
        $classes = 'twill-image-wrapper';

        if ($layout === 'constrained') {
            $classes = 'twill-image-wrapper twill-image-wrapper-constrained';
        }

        if (isset($this->wrapperClass)) {
            $classes = join(' ', [$classes, $this->wrapperClass]);
        }

        return $classes;
    }

    protected function setLqipAttributes()
    {
        if (!$this->lqip) {
            $this->lqipSrc = null;
            return;
        }

        $image = $this->data['image'];

        $this->lqipSrc = $image['lqipBase64'];

        if (!isset($this->data['sources'])) {
            $this->lqipSources = null;
            return;
        }

        $sources = [];

        foreach ($this->data['sources'] as $source) {
            $mediaQuery = $source['mediaQuery'];
            $image = $source['image'];

            $sources[] = $this->buildSourceObject(
                sprintf('%s 1x', $image['lqipBase64']),
                $image['aspectRatio'],
                $this->mimeType("gif"),
                $mediaQuery
            );
        }

        $this->lqipSources = $sources;
    }

    public function toArray(): array
    {
        return array_filter([
            'layout' => $this->layout,
            'wrapper' => [
                'classes' => $this->wrapperClasses(),
                'style' => $this->styleService->wrapper(),
            ],
            'placeholder' => [
                'src' => $this->lqipSrc,
                'sources' => $this->lqipSources,
                'style' => $this->styleService->placeholder(),
            ],
            'main' => [
                'loading' => $this->loading,
                'shouldLoad' => $this->loading === 'eager',
                'style' => $this->styleService->main($this->loading),
                'src' => $this->src,
                'sources' => $this->sources,
                'alt' => $this->alt,
            ],
            'alt' => $this->alt,
            'width' => $this->width,
            'height' => $this->height,
            'sizes' => $this->sizes,
            'aspectRatio' => $this->data['image']['aspectRatio'],
        ]);
    }
}
