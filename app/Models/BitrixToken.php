<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitrixToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_token',
        "expires",
        'expires_in',
        'application_token',
        'refresh_token',
        'token_type',
        'bitrix_code_id',
        'status',
    ];

    public function bitrixCode(): BelongsTo
    {
        return $this->belongsTo(BitrixCode::class, 'bitrix_code_id');
    }

    public function hasExpired(): bool
    {
        $expires = strtotime($this->expires_in . '+ 30 minutes');
        return $expires < time();
    }

    public static function boot()
    {
        parent::boot();

        static::created(function (BitrixToken $model) {
            $model->updateProjectStatus();
        });

        static::updated(function (BitrixToken $model) {
            $model->updateProjectStatus();
        });
    }

    private function updateProjectStatus()
    {
        /*INFO:: После установки интеграции (BitrixCode), автоматически заносится запись в таблицу integrations.
         |       Это просто привязка к типу интеграции.
         |       Поэтому интеграция не может отсутствовать, если BitrixCode существует.
        */
        $projects = $this->bitrixCode->integration->projects;
        if ($projects->count()) {
            $projectIds = $projects->pluck('id')->toArray();
            $this->bitrixCode->integration->projects()
                ->whereIn('id', $projectIds)
                ->update(['integration_status' => true]);
        }
    }
}
