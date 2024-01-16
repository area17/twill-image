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

        $style = $this->baseStyle;

        if (!!$this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;

            if ($layout === ImageViewModel::LAYOUT_FIXED) {
                $style['width'] = $this->width . 'px';
                $style['height'] = $this->height . 'px';
                $style['position'] = 'relative';
            } elseif ($layout === ImageViewModel::LAYOUT_CONSTRAINED) {
                $style['bottom'] = 0;
                $style['right'] = 0;
            } elseif ($layout === ImageViewModel::LAYOUT_FULL_WIDTH) {
                $style['bottom'] = 0;
                $style['right'] = 0;
            }
        }

        if($loading === 'lazy') {
            $style['opacity'] = 1;
            $style['transition'] = 'opacity 500ms linear';
        }

        return $this->implodeStyles($style);
    }

    /**
     * Return inline styles for the main image element
     *
     * @return string
     */
    public function main($loading = 'eager')
    {
        $style = $this->baseStyle;

        // Only set CSS to animate IMG if JS is enabled
        if(config('twill-image.js') && $loading === 'lazy') {
            $style['opacity'] = 0;
            $style['transform'] = 'translateZ(0px)';
            $style['will-change'] = 'opacity';
        }

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
