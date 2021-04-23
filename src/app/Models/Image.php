<?php

namespace Croustille\Image\Models;

use Croustille\Image\Models\Interfaces\ImageSource;

class Image
{
    protected $backgroundColor;
    protected $layout;
    protected $loading;
    protected $lqip;
    protected $imgStyle;
    protected $sizes;
    protected $source;
    protected $height;
    protected $width;

    public function __construct(ImageSource $source, array $args = [])
    {
        $this->source = $source;

        $this->backgroundColor
            = $args['backgroundColor'] ??
            config('images.background_color') ??
            'transparent';

        $this->lqip
            = $args['lqip'] ??
            config('images.lqip') ??
            false;

        $this->layout = $args['layout'] ?? 'fullWidth';

        $this->loading = $args['loading'] ?? 'lazy';

        $this->sizes = $args['sizes'] ?? $this->getSizes();

        $this->width = $args['width'] ?? $this->source->width();
        $this->height = $args['height'] ?? (isset($args['width']) ? $this->width / $this->source->width() * $this->source->height() : $this->source->height());

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

    public function getSizes()
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

    public function getWrapperProps($width = null, $height = null, $layout = "fullWidth")
    {
        $style = [
            "position" => "relative",
            "overflow" => "hidden",
        ];

        $classes = "croustille-image-wrapper";

        if ($layout === "fixed") {
            $style['width'] = $width."px";
            $style['height'] = $height."px";
        } elseif ($layout === "constrained") {
            $style['display'] = 'inline-block';
            $classes = "croustille-image-wrapper croustille-image-wrapper-constrained";
        }

        $style['background-color'] = $this->backgroundColor;

        return [
            'classes' => $classes,
            'style' => $this->implodeStyles($style),
        ];
    }

    public function getPlaceholderProps(
        $src,
        $layout,
        $width,
        $height,
        $backgroundColor
    ) {
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

        if ($backgroundColor) {
            $style['background-color'] = $backgroundColor;

            if ($layout === 'fixed') {
                $style['width'] = $width.'px';
                $style['height'] = $height.'px';
                $style['background-color'] = $backgroundColor;
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
            'src' => $src,
            'style' => $this->implodeStyles($style),
        ];
    }

    public function getMainProps($isLoading)
    {
        $style = array_merge(
            [
                'background-color' => $this->backgroundColor,
                'transition' => 'opacity 500ms ease 0s',
                'transform' => 'translateZ(0px)',
                'transition' => 'opacity 250ms linear',
                'will-change' => 'opacity',
            ],
            $this->imgStyle
        );

        $style['opacity'] = $this->loading === 'lazy' ? 0 : 1;

        return [
            'sources' => $this->source->srcSets(),
            'src' => $this->source->defaultSrc(),
            'loading' => $this->loading,
            'shouldLoad' => $isLoading,
            'style' => $this->implodeStyles($style),
        ];
    }

    public function implodeStyles($style)
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

    public function view()
    {
        $wrapper = $this->getWrapperProps(
            $this->width,
            $this->height,
            $this->layout
        );
        $placeholder = $this->getPlaceholderProps(
            $this->lqip ? $this->source->lqip() : false,
            $this->layout,
            $this->width,
            $this->height,
            $this->backgroundColor
        );
        $main = $this->getMainProps(
            $this->loading === "eager"
        );

        return view(
            'image::wrapper',
            [
                'layout' => $this->layout,
                'wrapper' => $wrapper,
                'placeholder' => $placeholder,
                'main' => $main,
                'width' => $this->width,
                'height' => $this->height,
                'alt' => $this->source->alt(),
                'sizes' => $this->sizes ?? $this->source->sizesAttr(),
            ]
        );
    }
}
