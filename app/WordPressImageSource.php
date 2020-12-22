<?php

namespace Croustille\Image;

use Exception;
use Croustille\Image\ImageSourceInterface;
use function App\config;

class WordPressImageSource implements ImageSourceInterface
{
    protected $id;
    protected $size;
    protected $args;
    protected $image;

    public function __construct($id, $size, $args = [])
    {
        $defaults = [
            'sizesAttr' => '100vw',
        ];

        $this->id = $id;
        $this->size = $size;
        $this->args = (object) wp_parse_args($args, $defaults);
        $this->image = wp_get_attachment_image_src($id, $size, false);
    }

    public function width()
    {
        return $this->image[1];
    }

    public function height()
    {
        return $this->image[2];
    }

    public function alt()
    {
        return $this->args->alt ?? trim(strip_tags(get_post_meta($this->id, '_wp_attachment_image_alt', true)));
    }

    public function caption()
    {
        return wp_get_attachment_caption($this->id);
    }

    public function srcSet()
    {
        return wp_get_attachment_image_srcset($this->id, $this->size);
    }

    public function defaultSrc()
    {
        return $this->image[0];
    }

    public function sizesAttr()
    {
        return $this->args->sizesAttr;
    }

    public function lqip()
    {
        $lqip = config('images.lqip');

        return $lqip ? wp_get_attachment_image_src($this->id, $lqip, false)[0] : false;
    }
}
