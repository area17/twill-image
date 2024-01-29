@php
$attributes = 'aria-hidden="true" data-twill-image-placeholder';
@endphp
@if(isset($placeholderSrc) && !!$placeholderSrc)
  @include('twill-image::picture', [
      'fallback' => $placeholderSrc,
      'alt' => "",
      'attributes' => $attributes,
      'sizes' => $sizes,
      'sources' => $placeholderSources ?? [],
      'style' => $placeholderStyle,
      'class' => $placeholderClasses ?? null,
  ])
@else
    <div class='{{$placeholderClasses ?? null}}' style="{{$placeholderStyle ?? null}}" {!! $attributes !!}></div>
@endif
