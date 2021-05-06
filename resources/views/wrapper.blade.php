<div
    class="{{$wrapper['classes']}}"
    style="{{$wrapper['style']}}"
    data-croustille-image-wrapper
>
    @include('image::sizer')
    @include('image::placeholder', $placeholder)
    @include('image::main-image', $main)
</div>
