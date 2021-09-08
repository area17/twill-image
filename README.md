# Twill Image

Twill Image is a package designed to work with [Twill](https://twill.io) to display responsive images easily on your site. It leverages Twill's image processing capabilities and adds modern lazy-loading techniques. It supports responsive images, art direction and fixed width images.

- `<picture>` with multiple `<source>` elements
- Twill's low-quality placeholder (LQIP)
- Background-color placeholder
- Art direction (multiple crops)
- WebP and JPEG support
- Lazy load (fade in) image with `IntersectionOserver`
- Support native lazy loading with `loading='lazy'`

## Contents

- [Installation](#installation)
  - [Configuration file](#configuration-file)
  - [JavaScript module](#javascript-module)
- [Using `twill-image`](#using-twill-image)
  - [The `Image` model](#the-image-,model)
    - [Available methods](#available-methods)
      - [`crop`](#crop)
      - [`width`](#width)
      - [`height`](#height)
      - [`sources`](#sources)
      - [`sizes`](#sizes)
      - [`preset`](#preset)
      - [`render`](#render)
      - [`toArray`](#toArray)
  - [The facade `render` method](#the-facade-render-method)
    - [List of arguments](#list-of-arguments)
    - [Examples](#examples)
- [Configuration options and image presets](#configuration-options-and-image-presets)
  - [Options](#options)
- [Art directed images](#art-directed-images)
- [Multiple medias](#multiple-medias)

## Installation

Install the package to your existing Twill project with Composer.

```
composer require area17/twill-image
```

### Configuration file

The configuration file contains a few general settings and this is where you can define preset for your images.

Publish `config/twill-image.php` to your app's config folder.

```bash
php artisan vendor:publish --provider="A17\Twill\Image\TwillImageServiceProvider" --tag=config
```

### JavaScript module

You can import the JavaScript module and initialize the lazy loading class in your application.

```js
import { TwillImage } from '../../vendor/area17/twill-image'

document.addEventListener('DOMContentLoaded', function () {
  const lazyloading = new TwillImage()
})
```

When adding or refreshing content of a page without a reload, you can trigger a recalculation of TwillImage's observers by calling the `reset()` method. This is an example:

```js
document.addEventListener('page:updated', () => lazyloading.reset());
```

If you prefer to use a pre-compiled version of the JavaScrip module, you can publish a script `twill-image.js` to your app's public folder and add a `<script>` tag to your project.

```bash
php artisan vendor:publish --provider="A17\Twill\Image\TwillImageServiceProvider" --tag=js
```

In a Blade file.

```php
<script src="{{ asset('/twill-image.js') }}"></script>
```


## Using `twill-image`

### The `Image` model

The `Image` model allows you to interact fluently with a media object.

```php
$image = new A17\Twill\Image\Models\Image($object, $role, $media);

// or using the Facade
$image = TwillImage::image($object, $role, $media);
```

|Argument|Type|Default|Description|
|---|---|---|---|
|`$object` (Required)|`object`|   |`A17\Twill\Models\Media`, `A17\Twill\Models\Block`, module, etc.|
|`$role` (Required)|`string`|   |`Media` role|
|`$media`|`A17\Twill\Models\Media`|`null`|`Media` instance|

#### Available methods

Once you have created an instance of the `Image` model, you can interact by using one or chaining many of these methods.
##### `crop`

You can specify the crop name by passing it as an argument. By default, the `Image` model will look for a crop name `default` and if it isn't availble, it will look for a single crop and select it. If it can't determine the crop, it will result in an error.

```php
$image->crop('listing_card');
```

##### `width`

To set the width of the image, you can use this method. The default is an image of 1000 pixels wide. This is useful if you need to display an image with a fixed width or if you know in advance that you will a larger image than the default.

Note: the width is applied to the "fallback" image (`<img src="##">`) and to determine the number of image URL to add to the `srcset` attribute.

```php
$image->width(1500);
```

##### `height`

You can set the height of the image with this method. Similar to the `width` method above, it is most useful for fixed-size image. When not used, the height is determined by the aspect ratio of the image and inferred from the width.

```php
$image->height(900);

$image->crop('listing')->width(600)->height(400);
```

##### `sources`

To use mutliples `<source>` elements, you can pass a array to this method by listing the sources other than the main crop. Each item in the array must have a `mediaQuery` and a `crop` key in order to generate the proper `srcset`. You can pass an optional width and height. This is useful when used with the `crop` method to set the main image crop. See also [Art directed images](#art-directed-images).

```php
$image->crop('desktop')->sources([
    [
        'mediaQuery' => '(max-width: 400px)', // required
        'crop' => 'mobile', // required
        'width' => 200, // optional
        'height' => 200, // optional
    ],
    [
        'mediaQuery' => '(min-width: 401px) and (max-width: 700px)',
        'crop' => 'tablet',
    ],
]);
```

##### `sizes`

Use this method to pass a `sizes` attribute to the model.

```php
$image->sizes('(max-width: 400px) 100vw, 50vw');
```

##### `preset`

With this method you can use an object to pass a value to any of the above methods. You can also add a preset key to the config `config/twill-image.php` and pass the name to this method.

```php
// config/twill-image.php

return [
    // ...
    'presets' => [
        'art_directed' => [
            'crop' => 'desktop',
            'width' => 700,
            'sizes' => '(max-width: 767px) 100vw, (min-width: 767px) and (max-width: 1023px) 50vw, 33vw',
            'sources' => [
                [
                    'crop' => 'mobile',
                    'media_query' => '(max-width: 767px)',
                ],
                [
                    'crop' => 'tablet',
                    'media_query' => '(min-width: 767px) and (max-width: 1023px)',
                ],
            ],
        ],
    ],
];
```

```php
// to use this preset from the config file
$image->preset('art_directed');
```

You can directly pass the full object if you prefer.

```php
$image->preset([
    'crop' => 'desktop',
    'width' => 700,
    'sizes' => '(max-width: 767px) 100vw, (min-width: 767px) and (max-width: 1023px) 50vw, 33vw',
    'sources' => [
        [
            'crop' => 'mobile',
            'media_query' => '(max-width: 767px)',
        ],
        [
            'crop' => 'tablet',
            'media_query' => '(min-width: 767px) and (max-width: 1023px)',
        ],
    ],
]);
```

##### `render`

This method will return the rendered view.

```blade
{{-- resources/views/home.blade.php --}}
@php
$image = new Image($page, 'preview');
@endphp

{!! $image->preset('art_directed')->render() !!}

{{-- with arguments --}}
{!! $image->preset('art_directed')->render([
    'loading' => 'eager',
    'layout' => 'constrained',
]) !!}
```

##### `toArray`

If you need to split the image generation from the render (exposing the `Image` model data through a REST API for example), use this method to get all attributes as an array.

```php
    $previewImage = TwillImage::image($page, 'preview')->preset('art_directed')->toArray();
```

And use the `render` method from the facade to render the view.

```blade
{{-- resources/views/page.blade.php --}}

<div>{!! TwillImage::render($previewImage) !!}</div>
```

### The facade `render` method

As seen in the previous section, the image element rendering can be separated from the image attributes generation. You can use the `Image` model to set up your image and pass the resulting object (or its `array` format to the `render` method to output the view).

```php
$previewImage = TwillImage::image($page, 'preview')->toArray();
```

```blade
{!! TwillImage::render($previewImage) !!}
```

or

```php
<div class="w-1/4">
    {!! TwillImage::render($previewImage, [
        'layout' => 'constrained',
        'width' => 700,
    ]) !!}
</div>
```

#### List of arguments

|Argument|Type|Default|Description|
|---|---|---|---|
|`backgroundColor`|`hex`  `string`|See config|Set placeholder background color|
|`class`|`string`|   |Add class(es) to the wrapper element|
|`height`|`int`|   |   |
|`layout`|`"fullWidth" \| "constrained" \| "fixed"`|`fullWidth`|By default, the image will spread the full width of its container element, `constrained` will apply a `max-width` and `fixed` will apply hard width and height value|
|`loading`|`"lazy" \| "eager"`|`lazy`|Set native lazy loading attribute|
|`lqip`|`boolean`|See config|Use LQIP|
|`sizes`|`string`|   |The image sizes attributes|
|`width`|`int`|`1000`|Used with `layout` `constrained` and `fixed`|

#### Examples

```blade
{!! TwillImage::image($item, 'preview_image', [
    'sizes' => '(max-width: 767px) 50vw, 100vw',
])->render(); !!}

@php
$heroImage = TwillImage::image($item, 'preview_image');
$listingImage = TwillImage::image($item, 'preview_image')->crop('listing');
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


In `config/twill-image.php`, you can define general options and image presets. A preset informs the `Image::preset` method which crop to output along other options like responsive sources.

```php
<?php

return [

    'background_color' => '#e3e3e3',

    'lqip' => true,

    'webp_support' => true,

    'presets' => [
        'listing' => [
            'crop' => 'card',
            'width' => 500,
            'sizes' => '25vw',
        ],
        // ...
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

## Art directed images

To use different crops with media queries, you need to list the other sources in a `Image::preset` or by passing them to the `Image::sources` method. The rendered image element with have only the ratio of the main crop and other ratio need to be added with CSS.

Let's say this is your preset `sources` config:

```php
    // ...
    'art_directed' => [
        'crop' => 'desktop',
        'sizes' => '(max-width: 767px) 100vw, 50vw',
        'sources' => [
            [
                'crop' => 'mobile',
                'media_query' => '(max-width: 767px)',
            ]
        ],
    ],
    // ...
```

```blade
@php
$image = TwillImage::image($page, 'preview')->preset([
        'crop' => 'desktop',
        'sizes' => '(max-width: 767px) 100vw, 50vw',
        'sources' => [
            [
                'crop' => 'mobile',
                'media_query' => '(max-width: 767px)',
            ]
        ],
    ]);
@endphp

<div>
    {!! TwillImage::render($image, [
        'class' => 'art-directed'
    ]) !!}
</div>
```

Will output:

```html
<div class="twill-image-wrapper art-directed">...</div>
```

You can define styles for each breakpoint.

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
    return TwillImage::image($item, 'gallery_image', $media);
})->toArray();
@endphp

@if($galleryImages)
    @foreach($galleryImages as $image)
        {!! TwillImage::render($image) !!}
    @endforeach
@endif
```
