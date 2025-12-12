<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DoSA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:sa {--id= : User_ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Команда для назначения пользователя SA';

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
        $this->info('Starting process ==>');
        $user_id = (int) $this->option('id');
        if (empty($user_id)) {
            $this->error('Wrong user id!');
            return 1;
        }

        $user = User::findOrFail($user_id);

        if ($user === null) {
            $this->error('User not found!');
            return 2;
        }

        if ($user->hasRole('sa')) {
            $this->error('User already has role SA!');
            return 3;
        }
        $user->attachRole('sa');
        $this->info('User has been attached to role SA!');

        return 0;
    }
}
