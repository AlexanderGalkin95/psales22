<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory;

    const STATUS_CREATED = 'created';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_DONE = 'done';

    const TASK_STATUSES = [
        self::STATUS_CREATED => 'К выполнению',
        self::STATUS_IN_PROGRESS => 'Взято в работу',
        self::STATUS_ON_HOLD => 'Поставлено на паузу',
        self::STATUS_DONE => 'Выполнено',
    ];

    protected $fillable = [
        'project_id',
        'assessor_id',
        'total_duration',
        'processed',
        'status',
    ];

    protected $casts = [
        'processed' => 'double',
        'total_duration' => 'double'
    ];

    public function getStatusAttribute($value)
    {
        if ($value) {
            return [
                'label' => self::TASK_STATUSES[$value],
                'value' => $value
            ];
        } else {
            return [
                'label' => 'К выполнению',
                'value' => null
            ];
        }
    }

    /**
     * Get all of the comments for the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls(): BelongsToMany
    {
        return $this->belongsToMany(ProjectCall::class, 'task_calls', 'task_id', 'project_call_id')
            ->withPivot('id');
    }

    /**
     * Get the project that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that owns the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function (Task $model) {
            $model->history()->create([
                'author_id' => Auth::id(),
                'description' => 'Задача была создана'
            ]);
        });
    }
}
