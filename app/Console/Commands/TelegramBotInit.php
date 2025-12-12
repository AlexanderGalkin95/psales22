<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TelegramBotInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:bot-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Инициализация бота (адрес вебхука, команды, кнопки и т.д.)';

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
        $token = config('telegram.bots.mybot.token');
        $webhook = config('telegram.bots.mybot.webhook_url');

        $this->info('Set webhook to: ' . $webhook);
        // не работает с текущей версией guzzle
        //$response = Telegram::setWebhook(['url' => $webhook]);
        $url = "https://api.telegram.org/bot". $token ."/setWebhook?" . http_build_query(["url" => $webhook]);
        $response = file_get_contents($url);
        $this->info($response);

        $this->info('Finish bot init');


        //$url = "https://api.telegram.org/bot$token/getWebhookInfo";
        //$this->info(file_get_contents($url));

        return 0;
    }
}
