@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Register</div> {{-- TODO translate --}}

                    <div class="panel-body">
                        <form id="form-register" class="form-horizontal" method="POST" action="{{ route('register') }}">
                            {{ csrf_field() }}

                            @component('components.input', ['id' => 'username', 'type' => 'text', 'required' => true, 'autofocus' => true])
                            @endcomponent

                            @component('components.input', ['id' => 'email', 'type' => 'email', 'required' => true])
                            @endcomponent

                            @component('components.input', ['id' => 'display_name', 'type' => 'text'])
                            @endcomponent

                            @component('components.input', ['id' => 'password', 'type' => 'password', 'required' => true])
                            @endcomponent

                            @component('components.input', ['id' => 'password_confirmation', 'type' => 'password', 'required' => true])
                            @endcomponent

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Register {{-- TODO translate --}}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
