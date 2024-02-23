<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Placeholder Background Color
    |--------------------------------------------------------------------------
    |
    | The color which should be used to fill the image space on a page.
    | Examples: 'gray', 'transparent', 'rgba(0, 0, 0, 0.25)'
    |
    */
    'background_color' => '#e3e3e3',

    /*
    |--------------------------------------------------------------------------
    | Enable Low Quality Placeholder
    |--------------------------------------------------------------------------
    |
    | Tells if LQIP should be used if it is available.
    |
    */
    'lqip' => false,

    /*
    |--------------------------------------------------------------------------
    | Use image sizer element
    |--------------------------------------------------------------------------
    |
    | If sets to auto, the sizer will be used if LQIP is enabled.
    | It can be set to `true` or `false` to enable or disable.
    |
    */
    'image_sizer' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Enable WebP Support
    |--------------------------------------------------------------------------
    |
    | Add sources support for WepP images.
    |
    */
    'webp_support' => true,

    /*
    |--------------------------------------------------------------------------
    | Inline styles
    |--------------------------------------------------------------------------
    |
    | Use inline styles for default styling or disable and use custom classes
    | instead.
    |
    | Custom classes can be used for applying Tailwind CSS classes if
    | inline styles are set to false.
    |
    */
    'inline_styles' => true,

    'custom_classes' => [
        'main' => [
            'bottom-0',
            'h-full',
            'left-0',
            'm-0',
            'max-w-none',
            'p-0',
            'absolute',
            'right-0',
            'top-0',
            'w-full',
            'object-cover',
            'object-center'
        ],
        'wrapper' => [
            'relative',
            'overflow-hidden',
        ],
        'placeholder' => [
            'bottom-0',
            'right-0'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Presets
    |--------------------------------------------------------------------------
    |
    | Define image presets here.
    |
    */
    'presets' => [
        // Preset example
        // 'preview_image' => [
        //     'crop' => 'default',
        //     'sizes' => '25vw',
        // ],

        // Preset example with multiple crops
        // 'art_directed' => [
        //     'crop' => 'desktop',
        //     'width' => 700,
        //     'sizes' => '(max-width: 767px) 100vw, (min-width: 767px) and (max-width: 1023px) 50vw, 33vw',
        //     'sources' => [
        //         [
        //             'crop' => 'mobile',
        //             'media_query' => '(max-width: 767px)',
        //         ],
        //         [
        //             'crop' => 'tablet',
        //             'media_query' => '(min-width: 767px) and (max-width: 1023px)',
        //         ],
        //         [
        //             'crop' => 'desktop',
        //             'media_query' => '(min-width: 1024px)',
        //         ]
        //         // ...
        //     ],
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Columns - Frontend breakpoints and grid structure
    |--------------------------------------------------------------------------
    |
    | Define the columns class that is used to dynamically generates
    | `sizes` and `media`.
    |
    */
    'columns_class' => A17\Twill\Image\Services\ImageColumns::class,

    /*
    |--------------------------------------------------------------------------
    | Static Images Local Path
    |--------------------------------------------------------------------------
    |
    | Define the local path where the static images
    | are located. This should correcponds to the Twill `ImageService`
    | source folder and be publicly available.
    |
    */
    'static_local_path' => public_path(),

    'static_image_support' => false,

    // Glide config overrides
    'glide' => [
        'source' => public_path(),
        'base_path' => 'static',
    ],

];
