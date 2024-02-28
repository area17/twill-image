@include('twill-image::picture', [
    'fallback' => $mainSrc,
    'attributes' => 'data-twill-image-main',
    'sources' => $mainSources ?? [],
    'style' => $mainStyle ?? null,
    'class' => $mainClasses ?? null,
])
