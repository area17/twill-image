# Twill Image

Image module for Twill.

- `<picture>` with `<source>`
- Twill's LQIP and background color image placeholder
- Art direction
- WebP and JPEG support
- Lazyload (fade-in) image with IntersectionOserver
- Support native lazyloading with `loading='lazy'

## Installation

```
composer require croustille/twill-image
```

### Config file

Publish `config/images.php`.

```bash
php artisan vendor:publish --provider="Croustille\Image\ImageServiceProvider" --tag=config
```

### JavaScript module

You can publish a script `twill-image.js` to your public folder and add a `<script>` tag to your project.

```bash
php artisan vendor:publish --provider="Croustille\Image\ImageServiceProvider" --tag=js
```

In a Blade file.

```php
<script src="{{ asset('/twill-image.js') }}"></script>
```

Or you can import the JavaScript module and init the lazyloading class in you own js to be bundled with you application.

```js
import { TwillImage } from '../../vendor/croustille/twill-image'

document.addEventListener('DOMContentLoaded', function () {
  const lazyloading = new TwillImage()
})
```

## Config


In `config/images.php`, you can define frontend image profiles. A profile informs the `TwillImage::source()` method which crops to output along other options. By default, a profile share the same name as the image `role`.

```php
<?php

return [

    'background_color' => '#e3e3e3',

    'lqip' => true,

    'webp_support' => true,

    'profiles' => [
        'preview_image' => [
            'crop' => 'default',
            'width' => 500,
            'sizes' => '50vw',
        ],
        'listing' => [
            'crop' => 'card',
            'width' => 500,
            'sizes' => '50vw',
        ],
    ],
];
```

## Usage

```php
@php
$heroImage = TwillImage::source($item, [
  'role' => 'preview_image',
]);

$listingImage = TwillImage::source($item, [
  'role' => 'preview_image',
  'profile' => 'listing',
]);
@endphp

{!! TwillImage::render($heroImage) !!}

{!! TwillImage::render($listingImage) !!}

{!! TwillImage::render($listingImage, [
    'layout' => 'constrained',
    'width' => 400
]) !!}

{!! TwillImage::render($listingImage, [
    'layout' => 'fixed',
    'width' => 100,
    'height' => 150
]) !!}
```

## Methods

```
TwillImage::source($model, $args): array
```

|Argument|Type|Default|Description|
|---|---|---|---|
|`role` (Required)|`string`|   |   |
|`profile`|`string`|`role` value|   |


```
TwillImage::render($imageSource, $args): string
```

|Argument|Type|Default|Description|
|---|---|---|---|
|`layout`|`"fullWidth" \| "constrained" \| "fixed"`|`fullWidth`|By default, the image will spread the full width of its container element, `constrained` will apply a `max-width` and `fixed` will apply hard width and height value|
|`class`|`string`|   |   |
|`sizes`|`string`|   |   |
|`width`|`int`|`1000`|Used with `layout` `constrained` and `fixed`|
|`height`|`int`|   |   |

## Art direction

To use different images (and/or crops) with media queries, you need to set size and ratio for the sources other than default. An example on how to do this by adding some styles.

Let's say this is your profile sources config:

```php
    // ...
    'art_directed' => [
        'crop' => 'desktop',
        'width' => 700,
        'sizes' => '(max-width: 767px) 100vw, 50vw',
        'sources' => [
            [
                'crop' => 'mobile',
                'media_query' => '(max-width: 767px)',
            ],
            [
                'crop' => 'desktop',
                'media_query' => '(min-width: 768px)',
            ]
        ],
    ],
    // ...
```

```blade
@php
$imageSource = TwillImage::source('art_directed');
@endphp

{!! TwillImage::render($imageSource, [
    'class' => 'art-directed'
]) !!}
```

Will output:

```html
<div class="twill-image-wrapper art-directed">...</div>
```

You can define styles for each breakpoints.

```css
.art-directed {
  aspect-ratio: 16 / 9;
}

@media screen and (max-width: 767px) {
  .art-directed {
    aspect-ratio: unset;
  }
}
```
