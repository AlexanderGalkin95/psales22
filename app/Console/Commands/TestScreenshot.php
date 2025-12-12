<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Spatie\Browsershot\Browsershot;


class TestScreenshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'do:screen';

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
        Browsershot::url('https://google.com')
            ->noSandbox()
            ->save('example.pdf');

        return 0;
    }
}
