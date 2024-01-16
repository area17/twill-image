@php
$shouldLoad = $shouldLoad ?? true;
@endphp
@include('twill-image::picture', [
    'fallback' => $mainSrc,
    'attributes' => 'data-twill-image-main',
    'sources' => $mainSources ?? [],
    'style' => $mainStyle ?? null,
    'class' => $mainClasses ?? null,
])
@if($shouldLoad === false)
<noscript>
    @include('twill-image::picture', [
        'fallback' => $mainSrc,
        'attributes' => 'data-main-image',
        'shouldLoad' => true,
        'sources' => $mainSources ?? [],
        'style' => $mainNoscriptStyle ?? null,
        'class' => $mainNoscriptClasses ?? null,
    ])
</noscript>
@endif
