<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user and issue a token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var User $user */
        $user = User::create([
            'name' => $this->ask('Username'),
            'email' => $this->ask('Email'),
            'password' => Hash::make($this->secret('Password')),
        ]);
        $token = $user->createToken($user->email);
        $this->warn('External API token is: ' . $token->plainTextToken);
        $this->info('User created.');
        return 0;
    }
}
