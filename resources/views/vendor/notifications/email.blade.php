@component('mail::message')
    {{-- Greeting --}}
    @if (! empty($greeting))
        # {{ $greeting }}
    @else
        @if ($level == 'error')
            # {{ __('email.base.greetings.error') }}
        @else
            # {{ __('email.base.greetings.default') }}
        @endif
    @endif

    {{-- Intro Lines --}}
    @foreach ($introLines as $line)
        {{ $line }}

    @endforeach

    {{-- Action Button --}}
    @isset($actionText)
        @php
            switch ($level) {
                case 'success':
                    $color = 'green';
                    break;
                case 'error':
                    $color = 'red';
                    break;
                default:
                    $color = 'blue';
            }
        @endphp
        @component('mail::button', ['url' => $actionUrl, 'color' => $color])
            {{ $actionText }}
        @endcomponent
    @endisset

    {{-- Outro Lines --}}
    @foreach ($outroLines as $line)
        {{ $line }}

    @endforeach

    {{-- Salutation --}}
    @if (! empty($salutation))
        {{ $salutation }}
    @else
        {{ __('email.base.regards', ['site' => config('app.name')]) }}
    @endif

    {{-- Subcopy --}}
    @isset($actionText)
        @component('mail::subcopy')
            {{ __('email.base.help', ['text' => $actionText, 'url' => $actionUrl]) }}
        @endcomponent
    @endisset
@endcomponent
