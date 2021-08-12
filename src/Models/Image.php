<?php

namespace A17\Twill\Image\Models;

use Illuminate\Contracts\Support\Arrayable;

class Image implements Arrayable
{
    /**
     * @var array $source Array output of Source model
     */
    protected $source;

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
     * @var array $imgStyle Inline CSS styles that are applied to both placeholder and main image
     */
    protected $imgStyle;

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

    /**
     * @param array $source
     * @param array $args
     */
    public function __construct(array $source, array $args = [])
    {
        if (is_array($source)) {
            $this->source = $source;
        }

        $this->setAttributes($args);
    }

    /**
     * Process arguments and apply default values.
     *
     * @param array $args
     * @return void
     */
    protected function setAttributes($args)
    {
        $this->backgroundColor = $args['backgroundColor'] ??
        config('twill-image.background_color') ??
        'transparent';

        $this->layout = $args['layout'] ?? 'fullWidth';

        $this->loading = $args['loading'] ?? 'lazy';

        $this->lqip
          = $args['lqip'] ??
          config('twill-image.lqip') ??
          true;

        $this->sizes = $args['sizes'] ?? $this->source['sizes'] ?? $this->defaultSizesAttribute();

        $this->alt = $args['alt'] ?? $this->source['alt'];

        $this->width = $args['width'] ?? $this->source['width'];

        $this->height = $args['height'] ?? (isset($args['width']) ? $this->width / $this->source['width'] * $this->source['height'] : $this->source['height']);

        $this->wrapperClass = $args['class'] ?? null;

        $this->imgStyle = array_merge(
            [
                'bottom' => 0,
                'height' => '100%',
                'left' => 0,
                'margin' => 0,
                'max-width' => 'none',
                'padding' => 0,
                'position' => 'absolute',
                'right' => 0,
                'top' => 0,
                'width' => '100%',
                'object-fit' => 'cover',
                'object-position' => 'center center',
            ],
            $args['imgStyle'] ?? []
        );
    }

    protected function defaultSizesAttribute()
    {
        switch ($this->layout) {
            // If screen is wider than the max size, image width is the max size,
            // otherwise it's the width of the screen
            case 'constrained':
                return '(min-width:'.$this->width.'px) '.$this->width.'px, 100vw';

            // Image is always the same width, whatever the size of the screen
            case 'fixed':
                return $this->width.'px';

            // Image is always the width of the screen
            case 'fullWidth':
                return '100vw';

            default:
                return null;
        }
    }

    protected function getViewWrapperProps()
    {
        $layout = $this->layout;

        $style = [
            "position" => "relative",
            "overflow" => "hidden",
        ];

        $classes = "twill-image-wrapper";

        if ($layout === "fixed") {
            $style['width'] = $this->width."px";
            $style['height'] = $this->height."px";
        } elseif ($layout === "constrained") {
            $style['display'] = 'inline-block';
            $classes = "twill-image-wrapper twill-image-wrapper-constrained";
        }

        if ($this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
        }

        if (isset($this->wrapperClass)) {
            $classes = join(" ", [$classes, $this->wrapperClass]);
        }

        return [
            'classes' => $classes,
            'style' => $this->implodeStyles($style),
        ];
    }

    protected function getViewPlaceholderProps()
    {
        $layout = $this->layout;

        $style = array_merge(
            $this->imgStyle,
            [
                'height' => '100%',
                'left' => 0,
                'position' => 'absolute',
                'top' => 0,
                'width' => '100%',
            ],
        );

        if ($this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;

            if ($layout === 'fixed') {
                $style['width'] = $this->width.'px';
                $style['height'] = $this->height.'px';
                $style['background-color'] = $this->backgroundColor;
                $style['position'] = 'relative';
            } elseif ($layout === 'constrained') {
                $style['position'] = 'absolute';
                $style['top'] = 0;
                $style['left'] = 0;
                $style['bottom'] = 0;
                $style['right'] = 0;
            } elseif ($layout === 'fullWidth') {
                $style['position'] = 'absolute';
                $style['top'] = 0;
                $style['left'] = 0;
                $style['bottom'] = 0;
                $style['right'] = 0;
            }
        }

        $style['opacity'] = 1;
        $style['transition'] =  "opacity 500ms linear";

        return [
            'style' => $this->implodeStyles($style),
        ];
    }

    protected function getViewMainProps($isLoading)
    {
        $style = array_merge(
            [
                'transition' => 'opacity 500ms ease 0s',
                'transform' => 'translateZ(0px)',
                'transition' => 'opacity 250ms linear',
                'will-change' => 'opacity',
            ],
            $this->imgStyle
        );

        if ($this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
        }

        $style['opacity'] = $this->loading === 'lazy' ? 0 : 1;

        return [
            'loading' => $this->loading,
            'shouldLoad' => $isLoading,
            'style' => $this->implodeStyles($style),
        ];
    }

    protected function implodeStyles($style)
    {
        return implode(
            ';',
            array_map(
                function ($property, $value) {
                    return "$property:$value";
                },
                array_keys($style),
                $style
            )
        );
    }

    public function toArray()
    {
        $wrapper = $this->getViewWrapperProps();

        $placeholder = array_merge(
            $this->lqip ? $this->source['lqip'] : [],
            $this->getViewPlaceholderProps()
        );

        $main = array_merge(
            [
                'src' => $this->source['src'],
                'sources' => $this->source['sources'],
                'alt' => $this->alt,
            ],
            $this->getViewMainProps($this->loading === "eager")
        );

        return [
          'layout' => $this->layout,
          'wrapper' => $wrapper,
          'placeholder' => $placeholder,
          'main' => $main,
          'alt' => $this->alt,
          'width' => $this->width,
          'height' => $this->height,
          'sizes' => $this->sizes,
        ];
    }

    public function view()
    {
        return view('image::wrapper', $this->toArray());
    }
}
