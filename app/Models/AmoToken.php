<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmoToken extends Model
{
    use HasFactory;

    protected $table = 'amo_tokens';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'amo_code_id',
        'token_type',
        'expires_in',
    ];

    public function amoCode(): BelongsTo
    {
        return $this->belongsTo(AmoCode::class, 'amo_code_id');
    }

    public function hasExpired(): bool
    {
        $expires = strtotime($this->expires_in . '+ 30 minutes');
        return $expires < time();
    }

    public static function boot()
    {
        parent::boot();

        static::created(function (AmoToken $model) {
            $model->updateProjectStatus();
        });

        static::updated(function (AmoToken $model) {
            $model->updateProjectStatus();
        });
    }

    private function updateProjectStatus()
    {
        /*INFO:: После установки интеграции (AmoCode), автоматически заносится запись в таблицу integrations.
         |       Это просто привязка к типу интеграции.
         |       Поэтому интеграция не может отсутствовать, если AmoCode существует.
        */
        $projects = $this->amoCode->integration->projects;
        if ($projects->count()) {
            $projectIds = $projects->pluck('id')->toArray();
            $this->amoCode->integration->projects()
                ->whereIn('id', $projectIds)
                ->update(['integration_status' => true]);
        }
    }
}
