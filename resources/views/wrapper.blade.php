@php
$needSizer = $needSizer ?? false;
@endphp
<div
    class="{{$wrapperClasses}}"
    style="{{$wrapperStyle ?? null}}"
    data-twill-image-wrapper
>
    @if($needSizer)
        @include('twill-image::sizer')
    @endif
    @include('twill-image::placeholder')
    @include('twill-image::main-image')
</div>
