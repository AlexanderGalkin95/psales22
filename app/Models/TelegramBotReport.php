<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramBotReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'last_report_sent_at',
        'project_id',
        'report_status',
        'error_text'
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saved(function(TelegramBotReport $model) {
            if($model->report_status === 'queued') {
                $project = $model->project;
                $project->override_report_sent_at = $model->last_report_sent_at;
                $project->save();
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(GoogleProject::class, 'project_id');
    }
}
