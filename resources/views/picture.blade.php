@php
$shouldLoad = $shouldLoad ?? true;
@endphp
@isset($sources)
    <picture>
        @isset($sources)
            @foreach($sources as $source)
                <source
                    type="{{ $source['type'] }}"
                    @if($source['mediaQuery'] !== 'default')
                        media="{{ $source['mediaQuery'] }}"
                    @endif
                    @if($shouldLoad)
                        srcset="{{ $source['srcset'] }}"
                    @else
                        data-srcset="{{ $source['srcset'] }}"
                    @endif
                    sizes="{{ $sizes }}"
                />
            @endforeach
        @endisset

        @include('image::image', [
            'src' => $fallback,
        ])
    </picture>
@else
    @include('image::image', [
        'src' => $fallback,
    ])
@endisset
