<?php

namespace A17\Twill\Image\Models\Interfaces;

interface ImageSource
{
    public function width();

    public function height();

    public function caption();

    public function alt();

    public function srcSets();

    public function defaultSrc();

    public function sizesAttr();

    public function lqip();
}
