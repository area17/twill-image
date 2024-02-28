<img
    decoding="async"
    src="{{$src}}"
    @isset($loading) loading="{{$loading}}" @endisset
    @if(isset($srcSet)) srcset="{{$srcSet}}" @endif
    @if(isset($srcSet) && isset($sizes)) sizes="{{$sizes}}" @endif
    @isset($alt) alt="{{$alt}}" @endisset
    @isset($style) style="{{$style}}" @endisset
    @isset($class) class="{{$class}}" @endisset
    @isset($attributes) {!! $attributes !!} @endisset
/>
