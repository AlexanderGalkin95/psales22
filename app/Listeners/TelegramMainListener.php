<?php

namespace App\Listeners;

use App\Events\ProjectCreatedEvent;
use App\Events\ProjectEditedEvent;
use App\Events\TelegramBotEvent;
use App\Models\GoogleProject;
use App\Models\TelegramBot;
use App\Models\User;
use App\Notifications\TelegramNotification;
use App\Repositories\ProjectRepository;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramMainListener
{
    public ProjectRepository $pr;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ProjectRepository $pr)
    {
        $this->pr = $pr;
    }

    /**
     * Handle the event.
     *
     * @param TelegramBotEvent|ProjectEditedEvent|ProjectCreatedEvent $event
     * @return void
     */
    public function handle($event)
    {
        Log::debug('Telegram Event');

        try {
            if ($event->text === '/start') {
                $this->send($event, 'Добро пожаловать на канал "' . config('telegram.bots.mybot.username') . '"');
            }

            if ($event->type === 'user') {
                $telegram = TelegramBot::where('username', $event->username)->first();
                $userId = User::where('telegram', '=', $event->username)->value('id');
                if ($telegram === null) {
                    TelegramBot::create([
                        'chat_id' => $event->chat_id,
                        'username' => $event->username,
                        'user_id' => $userId,
                    ]);
                } else {
                    $telegram->update([
                        'chat_id' => $event->chat_id,
                        'user_id' => $userId,
                    ]);
                }
                return;
            }

            if (in_array($event->type, ['group', 'supergroup', 'megagroup', 'gigagroup'])) {
                $telegram = TelegramBot::where('username', $event->username)->first();
                if ($telegram === null && $event->context->left_chat_participant) return;
                if ($telegram) {
                    $status = '';
                    if ($event->context->left_chat_participant
                        && $event->context->left_chat_participant->is_bot
                        && $event->context->left_chat_participant->username === config('telegram.bots.mybot.username')) {
                        $status = 'disabled';
                    } else if ($event->context->new_chat_participant
                        && $event->context->new_chat_participant->is_bot
                        && $event->context->new_chat_participant->username === config('telegram.bots.mybot.username')) {
                        $status = 'active';
                    }
                    if ($status !== '') {
                        $telegram->update([
                            'chat_id' => $event->chat_id,
                            'type' => $event->type,
                            'status' => $status,
                        ]);
                    }
                } else {
                    $telegram = new TelegramBot();
                    $telegram->chat_id = $event->chat_id;
                    $telegram->username = $event->username;
                    $telegram->type = $event->type;
                    $telegram->save();
                }

                $telegram->refresh();
                GoogleProject::where('telegram', $event->username)->update(['telegram_bot_id' => $telegram->id]);

                return;
            }

            // disable send info to admins
//            if (in_array($event->type, ['project-edited', 'project-created'])) {
//                $context = $this->pr->getProject($event->project->id);
//                $title = '';
//                switch($event->type) {
//                    case 'project-created':
//                        $title = "Проект '{$context->name}' был создан.";
//                        break;
//                    case 'project-edited':
//                        $title = "Проект '{$context->name}' был обновлен.";
//                        break;
//                }
//
//                $this->sendToAdmins($this->mapToText($context), $title);
//                return;
//            }

        } catch (\Exception $exception) {
            report($exception);
        }
    }

    private function sendToAdmins($context, $title)
    {
        $admins = User::admins();
        foreach ($admins as $admin) {
            $admin->notify(new TelegramNotification($context, $title));
        }
    }

    protected function send($event, $append): void
    {
        Telegram::sendMessage([
            'chat_id' => $event->chat_id,
            'text' => $append,
        ]);
    }

    private function mapToText($context): string
    {
        $criteria = implode(', ', array_map(function($item) {
            return $item['label'];
        }, $context->criteria->toArray()));

        $pm = $context->pm ? $context->pm->label : '';
        $senior = $context->senior ? $context->senior->label : '';
        $assessors = $context->assessors->pluck('label')->join(', ');

        $integrableType = Str::studly($context->reference->system_name);
        return <<<HTML
            <b>Название проекта :</b> $context->name\n
            <b>Домен $integrableType :</b> $context->integration_domain\n
            <b>Google Таблица :</b> $context->googleSpreadsheet\n
            <b>Google Лист :</b> $context->googleConnection\n
            <b>Проектный менеджер :</b> $pm \n
            <b>Старший асессор :</b> $senior \n
            <b>Асессоры :</b> $assessors \n
            <b>Критерии :</b> $criteria\n
        HTML;

    }
}
