# Twill Image

Twill Image is a package designed to work with [Twill](https://twill.io) to display images easily on your site. It leverages Twill's image processing capabilities and adds modern lazy-loading techniques. It supports responsive images and fixed width and height images.

- `<picture>` with `<source>`
- Twill's LQIP and background color image placeholder
- Art direction (multiple crops)
- WebP and JPEG support
- Lazyload (fade-in) image with IntersectionOserver
- Support native lazyloading with `loading='lazy'

## Contents

- [Installation](#installation)
  - [Configuration file](#configuration-file)
  - [JavaScript module](#javascript-module)
- [Using `twill-image`](#using-twill-image)
  - [Available methods](#available-methods)
  - [Examples](#examples)
- [Configuration options and image presets](#configuration-options-and-image-presets)
  - [Options](#options)
  - [Preset options](#presets-options)
  - [Presets' `sources` array options](#presets-sources-array-options)
- [Art directed images](#art-directed-images)
- [Multiple medias](#multiple-medias)

## Installation

```
composer require area17/twill-image
```

### Configuration file

Publish `config/images.php`.

```bash
php artisan vendor:publish --provider="A17\Twill\Image\ImageServiceProvider" --tag=config
```

### JavaScript module

You can publish a script `twill-image.js` to your public folder and add a `<script>` tag to your project.

```bash
php artisan vendor:publish --provider="A17\Twill\Image\ImageServiceProvider" --tag=js
```

In a Blade file.

```php
<script src="{{ asset('/twill-image.js') }}"></script>
```

Or you can import the JavaScript module and init the lazyloading class in you own js to be bundled with you application.

```js
import { TwillImage } from '../../vendor/area17/twill-image'

document.addEventListener('DOMContentLoaded', function () {
  const lazyloading = new TwillImage()
})
```

## Using `twill-image`

### Available methods

```
TwillImage::source($object, $role, $args, $preset, $media): array
```

|Argument|Type|Default|Description|
|---|---|---|---|
|`object` (Required)|`string`|   |Twill Media, Block, module, etc.|
|`role` (Required)|`string`|   |Twill Media role|
|`args`|`array`|`[]`|   |
|`preset`|`string`|`role` value|Preset name|
|`media`|`A17\Twill\Models\Media`|`null`|   |

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

```
TwillImage::image($object, $role, $args, $preset, $media)
```

|Argument|Type|Default|Description|
|---|---|---|---|
|`object` (Required)|`string`|   |Twill Media, Block, module, etc.|
|`role` (Required)|`string`|   |Twill Media role|
|`args`|`array`|`[]`|   |
|`preset`|`string`|`role` value|Preset name|
|`media`|`A17\Twill\Models\Media`|`null`|   |

### Examples

```php
@php
$heroImage = TwillImage::source($item, 'preview_image');
$listingImage = TwillImage::source($item, 'preview_image', [], 'listing');
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

## Configuration options and image presets


In `config/twill-image.php`, you can define image presets. A preset informs the `TwillImage::source()` method which crop to output along other options like responsive sources. By default, a profile share the same name as the image `role`, but you can override this by passing a preset name to the `source()` method. This is useful for cases where you might need to re-use the same present with multiple media roles.

```php
<?php

return [

    'background_color' => '#e3e3e3',

    'lqip' => true,

    'webp_support' => true,

    'presets' => [
        'preview_image' => [
            'crop' => 'desktop',
            'width' => 1500,
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
        'listing' => [
            'crop' => 'card',
            'width' => 500,
            'sizes' => '25vw',
        ],
    ],
];
```
### Options

|Argument|Type|Default|Description|
|---|---|---|---|
|`background_color`|`string`|`#e3e3e3`|   |
|`lqip`|`boolean`|`true`|Uses Twill LQIP method to generate responsive placeholder|
|`webp_support`|`boolean`|`true`|   |
|`presets`|`object`|   |   |

### Presets options

If the key name (see above: `preview_image`) match the role name, it will be used as the base preset.

|Argument|Type|Default|Description|
|---|---|---|---|
|`crop`|`string`|   |If omitted, will look for `default` or, if not present and only one crop is available, it will use that one. If more than one crop is available, it will throw an error|
|`width`|`int`|1000|   |
|`sizes`|`string`|`100vw`|   |
|`sources`|`array`|   |   |

### Presets' `sources` array options

|Argument|Type|Default|Description|
|---|---|---|---|
|`crop`|`string`|   |   |
|`media_query`|`string`|   |   |
|`widths`|`[int]`|   |By default, a series of sources will be generated up to 5000px wide|

## Art directed images

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

## Multiple medias

```blade
@php
$galleryImages = $item->imageObjects('gallery_image', 'desktop')->map(function ($media) use ($item) {
    return TwillImage::source($item, 'gallery_image', [], null, $media);
})->toArray();
@endphp

@if($galleryImages)
    @foreach($galleryImages as $image)
        {!! TwillImage::render($image) !!}
    @endforeach
@endif
```
