<ul class="{{ $class }}">
    @foreach($links as $link)
        @php
            $isRoute = isset($link['route']);
            $active = $isRoute && $link['route'] == Route::current()->getName();
        @endphp
        <li class="nav-item{{ $active ? " active" : "" }}">
            <a class="nav-link" href="{{ $isRoute ? route($link['route']) : $link['url'] }}"
               aria-label="{{ $link['name'] }}{{ $active ?  " (current)" : "" }}"{{ isset($link['onclick']) ? " onclick={$link['onclick']}" : "" }}> {{-- TODO translate the "current" word --}}
                @if(isset($link['icon']))
                    <i class="{{ $link['icon'] }}" aria-hidden="true"></i>
                @else
                    {{ $link['name'] }}
                    @if($active)
                        <span class="sr-only">&nbsp;(current)</span> {{-- TODO translate the "current" word --}}
                    @endif
                @endif
            </a>
        </li>
    @endforeach
</ul>