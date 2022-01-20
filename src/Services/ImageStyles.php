<?php

namespace A17\Twill\Image\Services;

use A17\Twill\Image\ViewModels\ImageViewModel;

class ImageStyles
{
    protected $backgroundColor;

    protected $baseStyle;

    protected $height;

    protected $layout;

    protected $width;

    /**
     * Set up the service to generate view inline styles for the wrapper, main image and placeholder elements
     *
     * @param string $layout
     * @param string $backgroundColor
     * @param int $width
     * @param int|null $height
     * @param array $imgStyle
     * @return void
     */
    public function setup($layout, $backgroundColor, $width, $height, $imgStyle = [])
    {
        $this->layout = $layout;

        $this->backgroundColor = $backgroundColor;

        $this->width = $width;

        $this->height = $height;

        $this->baseStyle = array_merge(
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
            $imgStyle,
        );
    }

    /**
     * Return inline styles for the wrapper element
     *
     * @return string
     */
    public function wrapper()
    {
        $layout = $this->layout;

        $style = [
            'position' => 'relative',
            'overflow' => 'hidden',
        ];

        if ($layout === ImageViewModel::LAYOUT_FIXED) {
            $style['width'] = $this->width . 'px';
            $style['height'] = $this->height . 'px';
        } elseif ($layout === ImageViewModel::LAYOUT_CONSTRAINED) {
            $style['display'] = 'inline-block';
        }

        if (!! $this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
        }

        return $this->implodeStyles($style);
    }

    /**
     * Return inline styles for the placeholder element
     *
     * @return string
     */
    public function placeholder()
    {
        $layout = $this->layout;

        $style = array_merge($this->baseStyle, [
            'height' => '100%',
            'left' => 0,
            'position' => 'absolute',
            'top' => 0,
            'width' => '100%',
        ]);

        if (!!$this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;

            if ($layout === ImageViewModel::LAYOUT_FIXED) {
                $style['width'] = $this->width . 'px';
                $style['height'] = $this->height . 'px';
                $style['background-color'] = $this->backgroundColor;
                $style['position'] = 'relative';
            } elseif ($layout === ImageViewModel::LAYOUT_CONSTRAINED) {
                $style['position'] = 'absolute';
                $style['top'] = 0;
                $style['left'] = 0;
                $style['bottom'] = 0;
                $style['right'] = 0;
            } elseif ($layout === ImageViewModel::LAYOUT_FULL_WIDTH) {
                $style['position'] = 'absolute';
                $style['top'] = 0;
                $style['left'] = 0;
                $style['bottom'] = 0;
                $style['right'] = 0;
            }
        }

        $style['opacity'] = 1;
        $style['transition'] = 'opacity 500ms linear';

        return $this->implodeStyles($style);
    }

    /**
     * Return inline styles for the main image element
     *
     * @return string
     */
    public function main($loading = 'eager')
    {
        $style = array_merge(
            [
                'transform' => 'translateZ(0px)',
                'will-change' => 'opacity',
            ],
            $this->baseStyle,
        );

        $style['opacity'] = (config('twill-image.js') && $loading === 'lazy') ? 0 : 1;

        return $this->implodeStyles($style);
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
                $style,
            ),
        );
    }
}
