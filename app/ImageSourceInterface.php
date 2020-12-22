<?php

namespace Croustille\Image;

interface ImageSourceInterface
{
    public function width();

    public function height();

    public function caption();

    public function alt();

    public function srcSet();

    public function defaultSrc();

    public function sizesAttr();

    public function lqip();
}
