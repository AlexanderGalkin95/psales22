<?php

namespace App\Models\Logs;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;


/**
 * @property integer id
 * @property string type
 * @property string description
 *
 * @property string inintiator_type
 * @property integer inintiator_id
 *
 * @property string target_type
 * @property integer target_id
 *
 * @property Carbon created_at
 *
 * @property-read ?User initiator
 * @property-read ?Model target
 *
 */
class LogEvent extends Model
{
    const UPDATED_AT = null;
    protected $table = 'log_events';

    const TYPE_ERROR_IMPORT = 'error-import';
    const TYPE_ERROR_TELEGRAM = 'error-telegram';


    const TYPE_ERRORS = [
        self::TYPE_ERROR_IMPORT,
        self::TYPE_ERROR_TELEGRAM,
    ];


    const TYPE_NAMES = [
        self::TYPE_ERROR_IMPORT => 'Ошибка импорта',
        self::TYPE_ERROR_TELEGRAM => 'Ошибка телеграма',
    ];


    static public function create(string $type, string $description, Model $initiator = null, Model $target = null): bool
    {
        if (!key_exists($type, self::TYPE_NAMES)) {
            throw new \Exception('type of LogEvent is not valid');
        }

        $model = new self;
        $model->type = $type;
        $model->description = $description;
        if ($initiator) {
            $model->initiator()->associate($initiator);
        }
        if ($target) {
            $model->target()->associate($target);
        }
        return $model->save();
    }


    public function typeName(): ?string
    {
        return self::TYPE_NAMES[$this->type] ?? null;
    }

    public function initiator(): MorphTo
    {
        return $this->morphTo('initiator');
    }

    public function target(): MorphTo
    {
        return $this->morphTo('target');
    }


}
