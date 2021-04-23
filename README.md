# Croustille Image

Image module for Twill.

- `<picture>` with `<source>`
- Twill's LQIP and background color image placeholder
- Art direction
- WebP and JPEG support
- Lazyload (fade-in) image and video with IntersectionOserver
- Support native lazyloading with `loading='lazy'

## Installation

```
composer require croustille/image
```

Publish `config/images.php`.

```bash
php artisan vendor:publish --provider="Croustille\Image\ImageServiceProvider" --tag=config
```

Publish JavaScript assets for inclusion in frontend bundler.

```bash
php artisan vendor:publish --provider="Croustille\Image\ImageServiceProvider" --tag=js
```

Init lazyloading

```js
import { CroustilleImage } from '../../packages/croustille/image/src/js'

document.addEventListener('DOMContentLoaded', function () {
  const lazyloading = new CroustilleImage()
})
```

## Config

Example with a Twill crop `preview_image` defined here:

```php
    // ...
    'crops' => [
        'preview_image' => [
            'default' => [
                [
                    'name' => 'default',
                    'ratio' => 10 / 16,
                ],
            ],
            'mobile' => [
                [
                    'name' => 'landscape',
                    'ratio' => 10 / 16,
                ],
                [
                    'name' => 'portrait',
                    'ratio' => 16 / 9,
                ],
            ],
        ],
    ],
    // ...
```

In `config/images.php`, defines image profiles and assocates Twill image roles to an image profile.

```php
<?php

return [

    'background_color' => '#e3e3e3',

    'lqip' => true,

    'webp_support' => true,

    'profiles' => [
        'generic_image' => [
            'default_width' => 989,
            'sizes' => '(max-width: 767px) 100vw, 50vw', // default '100vw'
            'sources' => [
                [
                    'crop' => 'mobile',
                    'media_query' => '(max-width: 767px)',
                    'widths' => [413, 826, 649, 989, 1299, 1519, 1919],
                ],
                [
                    // 'crop' => 'default',
                    'media_query' => '(min-width: 768px)',
                    'widths' => [989, 1299, 1519, 1919, 2599, 3038],
                ]
            ]
        ],
    ],

    'roles' => [
        'preview_image' => 'generic_image',
    ],

];
```

## Usage

```php
{!! CroustilleImage::fullWidth($block, 'preview_image') !!}
{!! CroustilleImage::constrained($block, 'preview_image', ['width' => 1000]) !!}
{!! CroustilleImage::fixed($block, 'preview_image', ['width' => 400]) !!}
```

## TODO

- Art direction placeholder (with picture/source/img)
