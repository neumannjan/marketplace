@extends('layouts.main')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Reset Password</div>

                    <div class="panel-body">
                        <form class="form-horizontal" method="POST" action="{{ route('password.request') }}">
                            {{ csrf_field() }}

                            <input type="hidden" name="token" value="{{ $token }}">

                            @component('components.input', ['id' => 'email', 'type' => 'email', 'required' => true, 'autofocus' => true, 'value' => $email])
                            @endcomponent

                            @component('components.input', ['id' => 'password', 'type' => 'password', 'required' => true])
                            @endcomponent

                            @component('components.input', ['id' => 'password_confirmation', 'type' => 'password', 'required' => true])
                            @endcomponent

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">
                                        Reset Password
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
