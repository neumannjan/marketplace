@extends('layouts.base')

@push('stylesheets')
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
@endpush

@push('javascripts')
    <script src="{{ mix('js/app.js') }}"></script>
@endpush

@section('body')
    <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
        <a class="navbar-brand" href="#">Nav</a>
        @component('components.nav', [
            'class' => 'navbar-nav',
            'links' => [
                [
                    'icon' => "fa fa-home",
                    'name' => "Home",
                    'route' => "home"
                ],
                [
                    'icon' => "fa fa-sign-in",
                    'name' => "Login",
                    'route' => "login"
                ],
            ]
        ])
        @endcomponent
    </div>

    <div class="main-content">
        <main role="main" class="main container">
            @section('content')
            @show
        </main>

        <footer class="footer">
            <div class="footer-inner">
                @section('footer')
                    <span class="ml-auto">&copy; Company 2017</span>
                @show
            </div>
        </footer>
    </div>
@endsection