@extends('layouts.base')

@push('stylesheets')
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
@endpush

@push('javascripts')
    <script src="{{ mix('js/app.js') }}"></script>
@endpush

@section('body')
    <div id="app" is="app">
        Loading...
    </div>
@endsection