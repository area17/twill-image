@if($layout === "fullWidth")
    <div
        aria-hidden="true"
        style="padding-top:{{number_format((float) ($height / $width) * 100, 2, '.', '')}}%;"
    ></div>
@endif
@if($layout === "constrained")
    <div style="max-width:{{$width}}px;display:block;">
        <img
            alt=""
            role="presentation"
            aria-hidden="true"
            src="data:image/svg+xml;charset=utf-8,%3Csvg height='{{$height}}px' width='{{$width}}px' xmlns='http://www.w3.org/2000/svg' version='1.1'%3E%3C/svg%3E"
            style="bottom:0;height:100%;left:0;margin:0;padding:0;right:0;top:0;width:100%;object-fit:cover;max-width:100%;display:block;position:static;"
        />
    </div>
@endif
