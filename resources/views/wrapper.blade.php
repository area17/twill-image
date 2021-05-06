<div
    class="{{$wrapper['classes']}}"
    style="{{$wrapper['style']}}"
    data-twill-image-wrapper
>
    @include('image::sizer')
    @include('image::placeholder', $placeholder)
    @include('image::main-image', $main)
</div>
