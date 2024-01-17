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
                'object-position' => 'center',
            ],
            $imgStyle,
        );

        $this->baseTailwindCSS = array_merge(
            [
                'bottom-0',
                'h-full',
                'left-0',
                'm-0',
                'max-w-none',
                'p-0',
                'absolute',
                'right-0',
                'top-0',
                'w-full' => '100%',
                'object-cover',
                'object-center'
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

        // Regular CSS
        $style = [
            'position' => 'relative',
            'overflow' => 'hidden',
        ];

        // Tailwind Classes
        $tailwindCSS = [
            'relative',
            'overflow-hidden',
        ];

        // Extra CSS for arbitrary values
        $tailwindStyle = [];

        if ($layout === ImageViewModel::LAYOUT_FIXED) {
            $style['width'] = $this->width . 'px';
            $style['height'] = $this->height . 'px';
            $tailwindStyle['width'] = $style['width'];
            $tailwindStyle['height'] = $style['height'];
        } elseif ($layout === ImageViewModel::LAYOUT_CONSTRAINED) {
            $style['display'] = 'inline-block';
            $tailwindCSS[] = 'inline-block';
        }

        if (!! $this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
            $tailwindStyle['background-color'] = $style['background-color'];
        }

        return [
            'inline' => $this->implodeStyles($style),
            'tailwind' => implode(' ', $tailwindCSS),
            'tailwind-inline' => $this->implodeStyles($tailwindStyle),
        ];
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
        $tailwindCSS = $this->baseTailwindCSS;
        $tailwindStyle = [];

        if (!!$this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
            $tailwindStyle['background-color'] = $style['background-color'];

            if ($layout === ImageViewModel::LAYOUT_FIXED) {
                $style['width'] = $this->width . 'px';
                $style['height'] = $this->height . 'px';
                $style['position'] = 'relative';
                $tailwindStyle['width'] = $style['width'];
                $tailwindStyle['height'] = $style['height'];
                $tailwindCSS[] = 'relative';
            } elseif (
                $layout === ImageViewModel::LAYOUT_CONSTRAINED ||
                $layout === ImageViewModel::LAYOUT_FULL_WIDTH
            ) {
                $style['bottom'] = 0;
                $style['right'] = 0;
                $tailwindCSS[] = 'bottom-0';
                $tailwindCSS[] = 'right-0';
            }
        }

        return [
            'inline' => $this->implodeStyles($style),
            'tailwind' => implode(' ', $tailwindCSS),
            'tailwind-inline' => $this->implodeStyles($tailwindStyle),
        ];
    }

    /**
     * Return inline styles for the main image element
     *
     * @return string
     */
    public function main($loading = 'eager')
    {
        $style = $this->baseStyle;
        $tailwindCSS = $this->baseTailwindCSS;

        // Only set CSS to animate IMG if JS is enabled
        if(config('twill-image.js') && $loading === 'lazy') {
            $style['opacity'] = 0;
            $style['transform'] = 'translateZ(0px)';
            $tailwindCSS[] = 'opacity-0';
        }

        return [
            'inline' => $this->implodeStyles($style),
            'tailwind' => implode(' ', $this->baseTailwindCSS),
            'tailwind-inline' => '',
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
                $style,
            ),
        );
    }
}
