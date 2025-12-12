<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, int|string|null $userId)
 */
class SmsCodeHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_email',
        'sms_code',
        'status',
        'is_current',
        'expires_in',
        'sms_send_attempts',
    ];

    public function hasExpired(): bool
    {
        return strtotime($this->expires_in) < time();
    }
}
