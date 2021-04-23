<?php

return [

    'background_color' => '#e3e3e3',

    'lqip' => true,

    'webp_support' => true,

    'profiles' => [
        'generic_image' => [
            'default_width' => 989,
            'sizes' => '100vw',
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
