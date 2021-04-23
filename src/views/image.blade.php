@php
$shouldLoad = $shouldLoad ?? true;
@endphp
<img
    decoding="async"
    @isset($loading) loading="{{$loading}}" @endisset
    @if($shouldLoad) src="{{$src}}" @endif
    @if(!$shouldLoad) data-src="{{$src}}" @endif
    @if($shouldLoad && isset($srcSet)) srcSet="{{$srcSet}}" @endif
    @if(!$shouldLoad && isset($srcSet)) data-srcset="{{$srcSet}}" @endif
    @isset($sizes) sizes="{{$sizes}}" @endisset
    @isset($alt) alt="{{$alt}}" @endisset
    @isset($style) style="{{$style}}" @endisset
    @isset($attributes) {{$attributes}} @endisset
/>
