<ul class="{{ $class }}">
    @foreach($links as $link)
        <li class="nav-item{{ ($active = (($isRoute = isset($link['route'])) && $link['route'] == Route::current()->getName())) ? " active" : "" }}">
            <a class="nav-link" href="{{ $isRoute ? route($link['route']) : $link['url'] }}"
               aria-label="{{ $link['name'] }}{{ $active ?  " (current)" : "" }}"> {{-- TODO translate the "current" word --}}
                @isset($link['icon'])
                    <i class="{{ $link['icon'] }}" aria-hidden="true"></i>
                @else
                    {{ link.name }}
                    @if($active)
                        <span class="sr-only">&nbsp;(current)</span> {{-- TODO translate the "current" word --}}
                    @endif
                @endisset
            </a>
        </li>
    @endforeach
</ul>