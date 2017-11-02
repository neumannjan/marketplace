<?php

namespace App\Console\Commands;


use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/** TODO make sure this class is actually useful */
class CreateUser extends Command
{
    protected $signature = 'user:create {username} {email} {password} {--d|display-name=} {--s|status=active}';

    public function handle()
    {
        $data = [
            'username' => $this->argument('username'),
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
            'display_name' => $this->option('display-name'),
            'status' => $this->getStatusValue($this->option('status')),
        ];

        $rules = User::getValidationRules();

        $validator = \Validator::make($data, $rules);
        try {
            $validator->validate();
        } catch (ValidationException $e) {
            echo json_encode($validator->messages()->getMessages(), JSON_PRETTY_PRINT);
            throw new ValidationException($validator);
        }

        $data['password'] = Hash::make($data['password']);

        $u = new User($data);
        $u->save();
    }

    protected function getStatusValue($string)
    {
        switch ($string) {
            case 'active':
                return User::STATUS_ACTIVE;
            case 'banned':
                return User::STATUS_BANNED;
            case 'inactive':
            default:
                return User::STATUS_INACTIVE;
        }
    }
}