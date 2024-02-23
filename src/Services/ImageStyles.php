<?php

namespace A17\Twill\Image\Services;

use A17\Twill\Image\ViewModels\ImageViewModel;

class ImageStyles
{
    /**
     * @var string $backgroundColor CSS color value to backgound-color directive
     */
    protected $backgroundColor;

    /**
     * @var array $baseStyle
     */
    protected $baseStyle = [];

    /**
     * @var array $baseClass
     */
    protected $baseClass = [];

    /**
     * Set up the service to generate view inline styles for the wrapper, main image and placeholder elements
     *
     * @param bool $needPlaceholder
     * @param string $backgroundColor
     * @param array $imgStyle
     * @return void
     */
    public function setup($needPlaceholder = false, $backgroundColor, $imgStyle = [], $imgClass = '')
    {

        $this->backgroundColor = $backgroundColor;

        if (config('twill-image.inline_styles') !== false) {
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
                $imgStyle,
            );
        } else {
            $this->baseClass = array_merge(
                $this->baseClass,
                $needPlaceholder ? config('twill-image.custom_classes.main') ?? [] : []
            );
        }

        if ($imgClass) {
            $this->baseClass = array_merge($this->baseClass, explode(" ", $imgClass));
        }
    }

    /**
     * Return inline styles for the wrapper element
     *
     * @return string
     */
    public function wrapper()
    {
        $style = [];
        $class = [];

        if (config('twill-image.inline_styles') !== false) {
            $style = [
                'position' => 'relative',
                'overflow' => 'hidden',
            ];
        } else {
            $class = array_merge(config('twill-image.custom_classes.wrapper') ?? [], $class);
        }

        if (!! $this->backgroundColor && $this->backgroundColor !== 'transparent') {
            $style['background-color'] = $this->backgroundColor;
        }

        return [
            'style' => $this->implodeStyles($style),
            'class' => implode(' ', $class),
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
        $class = $this->baseClass;

        if (!!$this->backgroundColor && $this->backgroundColor !== 'transparent') {
             if (config('twill-image.inline_styles') !== false) {
                $style['background-color'] = $this->backgroundColor;
                $style['bottom'] = 0;
                $style['right'] = 0;
            } else {
                $class = array_merge(config('twill-image.custom_classes.placeholder') ?? [], $class);
            }
        }

        return [
            'style' => $this->implodeStyles($style),
            'class' => implode(' ', $class),
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
            'style' => $this->implodeStyles($this->baseStyle),
            'class' => implode(' ', $this->baseClass),
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
