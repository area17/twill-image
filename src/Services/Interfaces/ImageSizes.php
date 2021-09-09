<?php

namespace A17\Twill\Image\Services\Interfaces;

interface ImageSizes
{
    public static function shouldInstantiateService(): bool;

    public function sizes($sizes): string;

    public function mediaQuery($args): string;
}
