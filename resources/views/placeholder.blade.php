@php
$attributes = 'aria-hidden="true" data-placeholder-image';
@endphp
@if(isset($placeholderSrc) && !!$placeholderSrc)
  @include('twill-image::picture', [
      'fallback' => $placeholderSrc,
      'alt' => "",
      'attributes' => $attributes,
      'sizes' => $sizes,
      'sources' => $placeholderSources ?? [],
      'style' => $placeholderStyle,
  ])
@else
    <div style="{{$placeholderStyle}}" {!! $attributes !!}></div>
@endif
