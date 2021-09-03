<?php

namespace A17\Twill\Image\Services;

class StyleService
{
    protected $layout;
    protected $backgroundColor;
    protected $width;
    protected $height;

    public function setup($layout, $backgroundColor, $width, $height, $imgStyle = null)
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
            $imgStyle ?? [],
        );
    }

    public function wrapper()
    {
        $layout = $this->layout;

        $style = [
            'position' => 'relative',
            'overflow' => 'hidden',
        ];

        if ($layout === 'fixed') {
            $style['width'] = $this->width . 'px';
            $style['height'] = $this->height . 'px';
        } elseif ($layout === 'constrained') {
            $style['display'] = 'inline-block';
        }

        if (!! $this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
        }

        return $this->implodeStyles($style);
    }

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

            if ($layout === 'fixed') {
                $style['width'] = $this->width . 'px';
                $style['height'] = $this->height . 'px';
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
        $style['transition'] = 'opacity 500ms linear';

        return $this->implodeStyles($style);
    }

    public function main($loading)
    {
        $style = array_merge(
            [
                'transition' => 'opacity 500ms ease 0s',
                'transform' => 'translateZ(0px)',
                'transition' => 'opacity 250ms linear',
                'will-change' => 'opacity',
            ],
            $this->baseStyle,
        );

        if (!!$this->backgroundColor) {
            $style['background-color'] = $this->backgroundColor;
        }

        $style['opacity'] = $loading === 'lazy' ? 0 : 1;

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
