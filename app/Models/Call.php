<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'integration_id',
        'record_id',
        'record_created_at',
        'record_event_type',
        'record_responsible_id',
        'record_responsible_name',
        'record_element_id',
        'record_element_name',
        'record_element_type',
        'record_element_link',
        'record_duration',
        'record_link',
        'schedule_id'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(TaskCall::class, 'project_calls', 'call_id', 'id', 'id', 'project_call_id')
            ->withPivot('id');
    }
}
