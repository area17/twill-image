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
- [Usage](#usage)
  - [The `Image` model](#the-image-,model)
    - [Available methods](#available-methods)
      - [`crop`](#crop)
      - [`width`](#width)
      - [`height`](#height)
      - [`sources`](#sources)
      - [`sizes`](#sizes)
      - [`columns`](#columns)
      - [`srcSetWidths`](#srcSetWidths)
      - [`preset`](#preset)
      - [`render`](#render)
      - [`toArray`](#toArray)
  - [The facade `render` method](#the-facade-render-method)
    - [List of arguments](#list-of-arguments)
    - [Examples](#examples)
- [Configuration](#configuration)
  - [Presets](#presets)
  - [List of options](#list-of-options)
- [Frontend breakpoints and grid structure](#frontend-breakpoints-and-grid-structure)
  - [`columns` example](#columns-example)
    - [`columns` preset](#columns-preset)
    - [`columns` output](#columns-output)
  - [`columns` custom class](#columns-custom-class)
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

The JavaScript module is not required. If you prefer to rely only on the browser's native `loading` attribute, set the [`js` config option](#list-of-options) to `false`.


## Usage

### The `Image` model

The `Image` model allows you to interact fluently with a media object.

```php
$image = new A17\Twill\Image\Models\Image($object, $role, $media);

// or using the Facade
$image = TwillImage::make($object, $role, $media);
```

|Argument|Type|Default|Description|
|---|---|---|---|
|`$object` (Required)|`A17\Twill\Models\Media` `A17\Twill\Models\Block` `object`|   |Your Twill module or block object|
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

Note: the width is applied to the "fallback" image (`<img src="{{ $image }}">`) and to determine the number of image URLs to add to the `srcset` attribute.

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
        'srcSetWidths' => [100, 200, 400], // optional
    ],
    [
        'mediaQuery' => '(min-width: 401px) and (max-width: 700px)',
        'crop' => 'tablet',
    ],
]);
```

Media queries can also be generated from a [frontend breakpoints and grid structure](#frontend-breakpoints-and-grid-structure) file by passing a `columns` key instead of `mediaQuery`. You can see the format below.


```php
$image->crop('desktop')->sources([
    [
        'columns' => [
            'md' => 'max',
        ],
        'crop' => 'mobile',
    ],
    [
        'columns' => [
            'md' => 'min',
            'lg' => 'max',
        ],
        'crop' => 'tablet',
    ],
]);
```

##### `sizes`

Use this method to pass a `sizes` attribute to the model.

```php
$image->sizes('(max-width: 400px) 100vw, 50vw');
```

##### `columns`

As an alternative to the `sizes` method, Twill Image provides a way to generate the `sizes` attribute based on a [frontend breakpoints and grid structure](#frontend-breakpoints-and-grid-structure) file. When placing this JSON file at the base folder of your app, the `sizes` attribute can be generated from passing a series of breakpoints and columns number to this method.

```php
$image->columns([
    'xxl' => 6,
    'xl' => 6,
    'lg' => 8,
    'md' => 8,
    'sm' => 8,
    'xs' => 12,
]);
```

This would tell how many columns the image will take at each breakpoint in order to generate to proper `sizes` attribute.

Note: this method will have an effect only when `frontend.config.json` exists at in base folder of your app.

##### `srcSetWidths`

Use this method to give a list a widths to generate the `srcset` attribute. Without this method, Twill Image will auto generate a series of widths based on the image width.

```php
$image->srcSetWidths([100, 150, 300, 600, 1200, 2000, 2400, 3600, 5000]);
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
            'sizes' => '(max-width: 767px) 25vw, (min-width: 767px) and (max-width: 1023px) 50vw, 33vw',
            'srcSetWidths' => [350, 700, 1400],
            'sources' => [
                [
                    'crop' => 'mobile',
                    'media_query' => '(max-width: 767px)',
                    'srcSetWidths' => [100, 200, 400],
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
$image = TwillImage::make($page, 'preview')->preset('art_directed');
@endphp

{!! $image->render() !!}

{{-- with arguments --}}
{!! $image->render([
    'loading' => 'eager',
    'layout' => 'constrained',
]) !!}
```

##### `toArray`

If you need to split the image generation from the render (exposing the `Image` model data through a REST API for example), use this method to get all attributes as an array.

```php
$previewImage = TwillImage::make($page, 'preview')->preset('art_directed')->toArray();
```

And use the `render` method from the facade to render the view.

```blade
{{-- resources/views/page.blade.php --}}

<div>{!! TwillImage::render($previewImage) !!}</div>
```

### The facade `render` method

As seen in the previous section, the image element rendering can be separated from the image attributes generation. You can use the `Image` model to set up your image and pass the resulting object (or its `array` format to the `render` method to output the view).

```php
$previewImage = TwillImage::make($page, 'preview')->toArray();
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
|`imageSizer`|`boolean`|True|Render the image sizer markup. If set as `false`, CSS classes need to be setup into `class` to size properly the wrapper element|
|`layout`|`"fullWidth" \| "constrained" \| "fixed"`|`fullWidth`|By default, the image will spread the full width of its container element, `constrained` will apply a `max-width` and `fixed` will apply hard width and height value|
|`loading`|`"lazy" \| "eager"`|`lazy`|Set native lazy loading attribute|
|`lqip`|`boolean`|See config|Use LQIP|
|`sizes`|`string`|   |The image sizes attributes|
|`width`|`int`|`1000`|Used with `layout` `constrained` and `fixed`|
|`imageStyles`|`array`|`[]`|Apply styles to placeholder and main `img` tags (ex.: `[['object-fit' => 'contain']]`|

#### Examples

```blade
{!! TwillImage::make($item, 'preview_image')->sizes('(max-width: 767px) 50vw, 100vw')->render(); !!}

@php
$heroImage = TwillImage::make($item, 'preview_image');
$listingImage = TwillImage::make($item, 'preview_image')->crop('listing');
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

## Configuration


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

### Presets

See [above section](#preset) about the `preset` method.
### List of options

|Argument|Type|Default|Description|
|---|---|---|---|
|`background_color`|`string`|`#e3e3e3`|   |
|`lqip`|`boolean`|`true`|Uses Twill LQIP method to generate responsive placeholder|
|`webp_support`|`boolean`|`true`|If set to `false`, the `type` attribute is omitted from `<source>` elements|
|`js`|`boolean`|`false`|Default is set to `false`, lazy-loading will simply rely on the image's `loading` attribute. If set to `true`, you will need to add the JS behvaior so image are properly lazy loaded|
|`presets`|`object`|   |   |

## Art directed images

To use different crops with media queries, you need to list the other sources in a `Image::preset` or by passing them to the `Image::sources` method. The rendered image element with have only the ratio of the main crop and other ratio need to be added with CSS.

Let's say this is your preset `art_directed` in your config:

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
$image = TwillImage::make($page, 'preview')->preset('art_directed');
@endphp

<div>
    {!! TwillImage::render($image, [
        'class' => 'art-directed'
    ]) !!}
</div>
```

It will output the image element with the class applied to the container.

```html
<div class="twill-image-wrapper art-directed">...</div>
```

Define styles for each breakpoint.

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

## Frontend breakpoints and grid structure

We provide a way to generate `sizes` and `media` attributes by describing the structure of your page in a JSON file `frontend.config.json` placed at the base of your app. An example is provided [`frontend.config.json.example`](frontend.config.json.example).

This file describes the breakpoints, main container widths, number of columns per breakpoints and inner/outer gutters. When this file exists in your project, you can use the `columns` method on the `Image` model or the `columns` key in your preset and sources objects in order to generate dynamically the `sizes` and `media` attributes.

### `columns` example

This example assumes that you have the provided `frontend.config.json` in your app's `base_path`.

#### `columns` preset

```php
// config/twill-image.php

return [
    // ...
    'presets' => [
        'art_directed' => [
            'crop' => 'desktop',
            'columns' => [
                'xxl' => 6,
                'xl' => 6,
                'lg' => 8,
                'md' => 8,
                'sm' => 8,
                'xs' => 12,
            ],
            'sources' => [
                [
                    'crop' => 'mobile',
                    'columns' => [
                        'md' => 'max',
                    ],
                ],
                [
                    'crop' => 'tablet',
                    'columns' => [
                        'md' => 'min',
                        'lg' => 'max',
                    ],
                ],
            ],
        ],
    ],
];
```

#### `columns` output

```blade
{{-- to use this preset from the config file and render the image --}}
{!! $image->preset('art_directed')->render() !!}
```

The image source and fallback would have these `sizes` and `media` attributes (the elements have been simplified for clarity):

```html
<picture>
    <source media="(max-width: 767px)" sizes="..." srcset="...">
    <source media="(min-width: 768px) and (max-width: 1023px)" sizes="..." srcset="...">
    <img sizes="
        (max-width: 543px) calc((((100vw - 260px) / 12) * 12) + 220px),
        (min-width: 544px) and (max-width: 767px) calc((((100vw - 312px) / 12) * 8) + 168px),
        (min-width: 768px) and (max-width: 1023px) calc((((100vw - 416px) / 12) * 8) + 224px),
        (min-width: 1024px) and (max-width: 1279px) calc((((100vw - 624px) / 12) * 8) + 336px),
        (min-width: 1280px) and (max-width: 1680px) calc((((100vw - 832px) / 12) * 6) + 320px),
        761px
    " src="...">
</picture>
```

### `columns` custom class

You can provide your own custom class to be used instead of the one provided. You can create your own service and provide the class name in the config file:

```php
// config/twill-image.php

    // ...
    // default to: A17\Twill\Image\Services\ImageColumns::class
   'columns_class' => MyApp\Services\MyOwnImageColumnsService::class,

];
```

The service must implement the interface `A17\Twill\Image\Services\Interfaces\ImageColumns`.


This can also be useful if you simply need to override some of the proprties that are defined in the provided services.

## Multiple medias

This is an example when you have multiple medias attached to a single `role`.

```blade
@php
$galleryImages = $item->imageObjects('gallery_image', 'desktop')->map(function ($media) use ($item) {
    return TwillImage::make($item, 'gallery_image', $media);
})->toArray();
@endphp

@if($galleryImages)
    @foreach($galleryImages as $image)
        {!! TwillImage::render($image) !!}
    @endforeach
@endif
```
