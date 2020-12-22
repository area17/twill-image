<div class="croustille-image-wrapper" style="position:relative;overflow:hidden;">
  <div style="width:100%;padding-bottom:{{ $padding_bottom }}%"></div>
  <div class="croustille-image-placeholder"
    style="background-color: {{ $background_color }};background-repeat: no-repeat;background-size: cover;position: absolute;top: 0px;bottom: 0px;opacity: 1;transition-delay: 0.35s;right: 0px;left: 0px;@if($lqip) background-image: url({{ $lqip }}); @endif">
  </div>
  <picture>
    <source sizes="{{ $sizes_attr }}" data-srcset="{{ $srcset_attr }}">
    <img alt="{{ $alt }}"
      style="position: absolute;top: 0px;left: 0px;width: 100%;height: 100%;object-fit: cover;object-position: center center;opacity: 0;transition: opacity 0.5s ease 0s;"
      data-src="{{ $default_src }}">
  </picture>
  <noscript>
    <picture>
      <source srcset="{{ $srcset_attr }}" sizes="{{ $sizes_attr }}" />
      <img alt="" src="{{ $default_src }}"
        style="position:absolute;top:0;left:0;transition:opacity 0.5s;transition-delay:0.5s;opacity:1;width:100%%;height:100%%;object-fit:cover;object-position:center;" />
    </picture>
  </noscript>
</div>
