<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationSchedule extends Model
{
    use HasFactory;

    const SCHEDULE_STATUS_CREATED = 'created';
    const SCHEDULE_STATUS_COMPLETED = 'completed';
    const SCHEDULE_STATUS_RUNNING = 'running';
    const SCHEDULE_STATUS_FAILED = 'failed';

    const SCHEDULE_TYPE_CALLS = 'calls';
    const SCHEDULE_TYPE_MANAGERS = 'managers';
    const SCHEDULE_TYPE_PIPELINES = 'pipelines';

    public array $statuses = [
        self::SCHEDULE_STATUS_CREATED,
        self::SCHEDULE_STATUS_COMPLETED,
        self::SCHEDULE_STATUS_RUNNING,
        self::SCHEDULE_STATUS_FAILED
    ];

    public array $types = [
        self::SCHEDULE_TYPE_CALLS,
        self::SCHEDULE_TYPE_MANAGERS,
        self::SCHEDULE_TYPE_PIPELINES
    ];

    protected $fillable = [
        'type',
        'runtime',
        'status',
    ];

    public function calls()
    {
        return $this->hasMany(Call::class, 'schedule_id');
    }
}
