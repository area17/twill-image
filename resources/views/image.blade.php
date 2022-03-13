<img
    decoding="async"
    @isset($loading) loading="{{$loading}}" @endisset
    @if($shouldLoad = $shouldLoad ?? true) src="{{$src}}" @endif
    @if(!$shouldLoad) data-src="{{$src}}" @endif
    @if($shouldLoad && isset($srcSet))
        @isset($sizes) sizes="{{$sizes}}" @endisset
        srcset="{{$srcSet}}"
    @endif
    @if(!$shouldLoad && isset($srcSet)) data-srcset="{{$srcSet}}" @endif
    @isset($alt) alt="{{$alt}}" @endisset
    @isset($style) style="{{$style}}" @endisset
    @isset($attributes) {!! $attributes !!} @endisset
/>
