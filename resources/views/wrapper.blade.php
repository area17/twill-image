<div
    class="{{$wrapperClasses}}"
    style="{{$wrapperStyle ?? null}}"
    data-twill-image-wrapper
>
    @include('twill-image::sizer')
    @include('twill-image::placeholder')
    @include('twill-image::main-image')
</div>
