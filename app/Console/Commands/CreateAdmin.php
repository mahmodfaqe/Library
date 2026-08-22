<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create an administrator account for the library panel';

    public function handle(): int
    {
        $data = [
            'name' => $this->ask('Full name'),
            'email' => $this->ask('Email'),
            'password' => $this->secret('Password (at least 12 characters)'),
            'role' => User::ROLE_ADMIN,
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create($data);
        Activity::record('user.created', $user->email);

        $this->info("Administrator {$user->email} created.");

        return self::SUCCESS;
    }
}
