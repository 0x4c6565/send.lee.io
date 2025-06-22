<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {--u|username= : Username for user} {--e|email= : Email address for user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->option('username');
        if ($name === null) {
            $name = $this->ask('Enter username');
        }

        $email = $this->option('email');
        if ($email === null) {
            $email = $this->ask('Enter email address');
        }

        $password = $this->secret('Enter password');

        try {
            $user = new User();
            $user->password = Hash::make($password);
            $user->email = $email;
            $user->name = $name;
            $user->save();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->info('User created successfully!');
        $this->info('New user id: ' . $user->id);
    }
}
