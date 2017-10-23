<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\Slug;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'username' => ['required', 'string', 'min:5', 'max:255', 'unique:users', new Slug()],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed|letters|numbers',
            'display_name' => 'nullable|string|max:128'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => \Hash::make($data['password']),
            'display_name' => $data['display_name'],
            'status' => User::STATUS_INACTIVE
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // do not login

        return $this->registered($request, $user);
    }

    /**
     * @param User $user
     * @inheritDoc
     */
    protected function registered(Request $request, $user)
    {
        //TODO send email
        //TODO only if set
        $user->sendRegistrationActivateNotification();

        return redirect()->route('index')->with('success.register', __('flash.success.register', [
            'email' => $user->email
        ]));
    }


}
