@include('image::picture', [
    'fallback' => $mainSrc,
    'attributes' => 'data-main-image',
    'sources' => $mainSources ?? [],
    'style' => $mainStyle,
])
<noscript>
    @include('image::picture', [
        'fallback' => $mainSrc,
        'attributes' => 'data-main-image',
        'shouldLoad' => true,
        'sources' => $mainSources ?? [],
        'style' => $mainStyle,
    ])
</noscript>
