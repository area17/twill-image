<?php

namespace A17\Twill\Image\Services;

use A17\Twill\Image\ViewModels\ImageViewModel;

class ImageStyles
{
    protected $backgroundColor;

    protected $baseStyle;

    protected $height;

    protected $width;

    /**
     * Set up the service to generate view inline styles for the wrapper, main image and placeholder elements
     *
     * @param bool $needPlaceholder
     * @param string $backgroundColor
     * @param int $width
     * @param int|null $height
     * @param array $imgStyle
     * @return void
     */
    public function setup($needPlaceholder = false, $backgroundColor, $width, $height, $imgStyle = [])
    {

        $this->backgroundColor = $backgroundColor;

        $this->width = $width;

        $this->height = $height;

        $this->baseStyle = array_merge(
            $needPlaceholder ? [
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
            ] : [],
            !config('twill-image.tailwind_css') ? $imgStyle : [],
        );

        $this->baseTailwindCSS = array_merge(
            $needPlaceholder ? [
                'bottom-0',
                'h-full',
                'left-0',
                'm-0',
                'max-w-none',
                'p-0',
                'absolute',
                'right-0',
                'top-0',
                'w-full',
                'object-cover',
                'object-center'
            ] : [],
            config('twill-image.tailwind_css') ? $imgStyle : [],
        );
    }

    /**
     * Return inline styles for the wrapper element
     *
     * @return string
     */
    public function wrapper()
    {

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
        $style = $this->baseStyle;
        $tailwindCSS = $this->baseTailwindCSS;
        $tailwindStyle = [];

        if (!!$this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
            $tailwindStyle['background-color'] = $style['background-color'];
            $style['bottom'] = 0;
            $style['right'] = 0;
            $tailwindCSS[] = 'bottom-0';
            $tailwindCSS[] = 'right-0';
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
        return [
            'inline' => $this->implodeStyles($this->baseStyle),
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
