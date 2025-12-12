<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramBot extends Model
{
    use HasFactory;

    protected $table = 'telegram_bot';

    protected $fillable = [
        'chat_id',
        'user_id',
        'project_id',
        'username',
        'type',
        'status',
    ];

}
