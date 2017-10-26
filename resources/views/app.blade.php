@extends('layouts.base')

@push('stylesheets')
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
@endpush

@push('javascripts')
    <script src="{{ mix('js/app.js') }}"></script>
@endpush

@push('meta')
    <script type="text/javascript">
                @php
                    $data = [
                        'token' => csrf_token(),
                        'is_authenticated' => Auth::check()
                    ];

                    /**
                     * Base64 is used here to obfuscate the result, so that noobs don't think they are
                     * world-class hackers just because they noticed a random "is_authenticated" boolean
                     * in the HTML of the frontend. :)
                     *
                     * No, they will NOT gain unwanted access to anything just by modifying these values. The
                     * JavaScript frontend will just be a bit confused.
                     */
                    $data = base64_encode(json_encode($data));
                @endphp
        const data = "{{ $data }}";
    </script>
@endpush

@section('body')
    <div id="app" is="app">
        Loading...
    </div>
@endsection