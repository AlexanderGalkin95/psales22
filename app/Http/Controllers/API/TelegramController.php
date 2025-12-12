<?php

namespace App\Http\Controllers\API;

use App\Events\TelegramBotEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function handler(Request $request): JsonResponse
    {
        Log::channel('telegram-hooks')->info('telegram webhook', $request->all());

        $webhook = Telegram::getWebhookUpdates();
        $message = $webhook->getMessage();

        if (count($message)) {
            event(new TelegramBotEvent($message));
        }

        return response()->json($message, 200);
    }
}
