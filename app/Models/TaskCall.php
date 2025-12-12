<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'project_call_id',
        'duration',
    ];

    protected $visible = [
        'task_id',
        'project_call_id',
        'duration',
        'call',
    ];

    protected $casts = [
        'duration' => 'double',
    ];

    protected $with = [
        'call',
    ];

    /**
     * Get the call that owns the TaskCall
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(ProjectCall::class, 'project_call_id');
    }
}
