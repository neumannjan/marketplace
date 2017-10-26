@extends('layouts.base')

@push('stylesheets')
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
@endpush

@push('javascripts')
    <script src="{{ mix('js/app.js') }}"></script>
@endpush

@section('body')
    <div class="wrapper" id="app">
        <div class="navbar navbar-expand navbar-dark bg-dark navbar-vertical">
            <a class="navbar-brand" href="#">Nav</a>
            @component('components.nav', [
                'class' => 'navbar-nav',
                'links' => [
                    [
                        'icon' => "fa fa-home",
                        'name' => "Home",
                        'route' => "index"
                    ],
                ]
            ])
            @endcomponent

            @php
                if(auth()->guard()->guest()) {
                    $links = [
                        [
                            'icon' => "fa fa-sign-in",
                            'name' => "Log in",
                            'route' => "login"
                        ],
                        [
                            'icon' => "fa fa-user-plus",
                            'name' => "Sign up",
                            'route' => "register"
                        ],
                    ];
                } else {
                    $links = [
                        [
                            'icon' => "fa fa-sign-out",
                            'name' => "Log out",
                            'route' => "logout",
                            'onclick' => "event.preventDefault();document.getElementById('logout-form').submit();"
                        ],
                    ];
                }
            @endphp

            @component('components.nav', [
                'class' => 'navbar-nav mt-auto',
                'links' => $links
            ])
            @endcomponent

            @auth
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            @endauth
        </div>

        <div class="main-content">
            <main role="main" class="main container">
                @component('components.flash')
                @endcomponent
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
    </div>
@endsection