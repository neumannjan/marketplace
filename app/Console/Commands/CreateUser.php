<?php

namespace App\Console\Commands;


use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Console command to create a new user
 */
class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {username} {email} '
    . '{password} '
    . '{--d|display-name= : The user\'s display name} '
    . '{--s|status=active : active, banned, inactive} '
    . '{--a|admin : Give the user admin privileges}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user';

    /**
     * @throws ValidationException
     */
    public function handle()
    {
        $data = [
            'username' => $this->argument('username'),
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
            'display_name' => $this->option('display-name'),
            'status' => $this->option('status'),
        ];

        $is_admin = $this->option('admin');

        // set validation rules
        $rules = User::getValidationRules();
        $rules['status'] = [
            Rule::in(['active', 'banned', 'inactive'])
        ];

        // validate input
        $validator = \Validator::make($data, $rules);
        try {
            $validator->validate();
        } catch (ValidationException $e) {
            $this->error(json_encode($validator->messages()->getMessages(), JSON_PRETTY_PRINT));
            throw new ValidationException($validator);
        }

        // modify input
        $data['password'] = Hash::make($data['password']);
        $data['status'] = $this->getStatusValue($data['status']);

        // create the user
        $u = new User($data);
        $u->is_admin = $is_admin;
        $u->save();
    }

    /**
     * Convert a string status to its integer counterpart
     * @param string $stringValue
     * @return int
     */
    protected function getStatusValue($stringValue)
    {
        switch ($stringValue) {
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