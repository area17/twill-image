<?php

namespace Croustille\Image;

use Croustille\Image\ImageSourceInterface;
use function App\view;

class Image
{
    protected $source;
    protected $padding_bottom;
    protected $background_color;
    protected $lqip;

    public function __construct(ImageSourceInterface $source, array $args = [])
    {
        $this->source = $source;
        $this->padding_bottom = $this->ratio();
        $this->background_color = $args['background_color'] ?? '#e3e3e3';
        $this->lqip = $args['lqip'] ?? false;
    }

    public function ratio()
    {
        return number_format((float) (100 / ($this->source->width() / $this->source->height())), 2, '.', '');
    }

    public function view()
    {
        return view(
            'components.croustille-image',
            [
                'padding_bottom' => $this->padding_bottom,
                'alt' => $this->source->alt(),
                'background_color' => $this->background_color,
                'srcset_attr' => $this->source->srcSet(),
                'default_src' => $this->source->defaultSrc(),
                'sizes_attr' => $this->source->sizesAttr(),
                'lqip' => $this->lqip ? $this->source->lqip() : false,
            ]
        );
    }
}
