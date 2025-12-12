<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TelegramBotEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $chat_id;

    public string $username;

    public string $text;

    public Collection $context;

    public string $type = 'bot';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Collection $context)
    {
        //Log::debug(print_r($context, true));

        $this->context = $context;
        $this->chat_id = $context['chat']['id'];

        if (isset($context['chat']['username'])) {
            $this->username = $context['chat']['username'];
        } elseif (isset($context['chat']['title'])) {
            $this->username = $context['chat']['title'];
        } elseif (isset($context['chat']['first_name']) || isset($context['chat']['last_name'])) {
            $firstName = $context['chat']['first_name'] ?? '';
            $lastName = $context['chat']['last_name'] ?? '';
            $this->username = trim($firstName . ' ' . $lastName);
        } else {
            $this->username = 'Unknown';
        }

        $this->text = $context['text'] ?? '';
        $this->type = $context['chat']['type'];
    }
}
