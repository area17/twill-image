@include('image::picture', [
    'fallback' => $src,
    'attributes' => 'data-main-image',
])
<noscript>
    @include('image::picture', [
        'fallback' => $src,
        'attributes' => 'data-main-image',
        'shouldLoad' => true,
    ])
</noscript>
