@php
$shouldLazyloadJS = $shouldLazyloadJS ?? false;
@endphp
<img
    decoding="async"
    @isset($loading) loading="{{$loading}}" @endisset
    @if(!$shouldLazyloadJS) src="{{$src}}" @endif
    @if($shouldLazyloadJS) data-src="{{$src}}" @endif
    @if(!$shouldLazyloadJS && isset($srcSet)) srcset="{{$srcSet}}" @endif
    @if($shouldLazyloadJS && isset($srcSet)) data-srcset="{{$srcSet}}" @endif
    @if(isset($srcSet) && isset($sizes)) sizes="{{$sizes}}" @endif
    @isset($alt) alt="{{$alt}}" @endisset
    @isset($style) style="{{$style}}" @endisset
    @isset($class) class="{{$class}}" @endisset
    @isset($attributes) {!! $attributes !!} @endisset
/>
