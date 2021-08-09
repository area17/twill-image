<?php

return [

    'background_color' => '#e3e3e3',

    'lqip' => true,

    'webp_support' => true,

    'presets' => [
        'preview_image' => [
            'crop' => 'default',
            'sizes' => '100vw',
        ],

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
    ],

];
