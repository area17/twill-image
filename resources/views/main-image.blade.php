@include('twill-image::picture', [
    'fallback' => $mainSrc,
    'attributes' => 'data-main-image',
    'sources' => $mainSources ?? [],
    'style' => $mainStyle,
])
<noscript>
    @include('twill-image::picture', [
        'fallback' => $mainSrc,
        'attributes' => 'data-main-image',
        'shouldLoad' => true,
        'sources' => $mainSources ?? [],
        'style' => $mainStyle,
    ])
</noscript>
