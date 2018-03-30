@extends('layouts.base')

@push('stylesheets')
    <link rel="stylesheet" href="{{ hashedAsset('app.css') }}">
@endpush

@push('javascripts')
    <script src="{{ hashedAsset('app.js') }}"></script>
@endpush

@push('meta')
    <script type="text/javascript">
                @php
                    $data = \App\Api\Request\InitialDataRequest::get(app('request'));

                    /**
                     * Base64 is used here to obfuscate the result, so that noobs don't think they are
                     * world-class hackers just because they noticed a random "is_admin" boolean
                     * in the HTML of the frontend. :)
                     *
                     * No, they would NOT gain unrestricted access to anything just by modifying these values. The
                     * JavaScript frontend would just become a bit confused.
                     */
                    $data = base64_encode(json_encode($data));
                @endphp
        window.data = "{{ $data }}";
    </script>
@endpush

@section('body')
    <div id="app">
        Loading...
    </div>
@endsection