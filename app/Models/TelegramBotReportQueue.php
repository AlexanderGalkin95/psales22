<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramBotReportQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'report_status',
        'error_text'
    ];
}
