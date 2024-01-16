@php
$shouldLazyloadJS = $shouldLazyloadJS ?? false;
@endphp
@include('twill-image::picture', [
    'fallback' => $mainSrc,
    'attributes' => 'data-twill-image-main',
    'sources' => $mainSources ?? [],
    'style' => $mainStyle ?? null,
    'class' => $mainClasses ?? null,
])
@if($shouldLazyloadJS)
<noscript>
    @include('twill-image::picture', [
        'fallback' => $mainSrc,
        'attributes' => 'data-main-image',
        'shouldLazyloadJS' => false,
        'sources' => $mainSources ?? [],
        'style' => $mainNoscriptStyle ?? null,
        'class' => $mainNoscriptClasses ?? null,
    ])
</noscript>
@endif
