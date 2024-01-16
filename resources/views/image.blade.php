@php
$shouldLoad = $shouldLoad ?? true;
@endphp
<img
    decoding="async"
    @isset($loading) loading="{{$loading}}" @endisset
    @if($shouldLoad) src="{{$src}}" @endif
    @if(!$shouldLoad) data-src="{{$src}}" @endif
    @if($shouldLoad && isset($srcSet)) srcset="{{$srcSet}}" @endif
    @if(!$shouldLoad && isset($srcSet)) data-srcset="{{$srcSet}}" @endif
    @if(isset($srcSet) && isset($sizes)) sizes="{{$sizes}}" @endif
    @isset($alt) alt="{{$alt}}" @endisset
    @isset($style) style="{{$style}}" @endisset
    @isset($class) class="{{$class}}" @endisset
    @isset($attributes) {!! $attributes !!} @endisset
/>
