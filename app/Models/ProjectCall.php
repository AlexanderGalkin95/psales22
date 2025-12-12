<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ProjectCall extends Model
{
    use HasFactory;

    protected $table = 'project_calls';

    protected $fillable = [
        'project_id',
        'call_id',
    ];

    protected $with = [
        'call'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function task(): HasOneThrough
    {
        return $this->hasOneThrough(Task::class, TaskCall::class, 'task_id', 'id');
    }

    // TODO:: Реализовать this relationship
    // public function rating(): BelongsTo
    // {
    //     return $this->belongsTo(Rating::class, 'audio_id', 'record_id');
    // }
}
