<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property integer id
 * @property integer audio_id
 * @property integer project_id
 * @property integer user_id
 *
 * @property string audio_link
 *
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property-read Project project
 * @property-read User assessor
 * @property-read Call call
 */
class CallRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'audio_id',
        'comments',
        'call_type_id',
        'call_type_value',
        'heat',
        'type',
        'created_date',
        'created_time',
        'duration',
        'audio_link',
        'link_to_lead',
        'manager',
    ];


    public function criteria(): HasMany
    {
        return $this->hasMany(CallRatingCriteria::class);
    }

    public function crm(): HasMany
    {
        return $this->hasMany(CallRatingCrmFields::class);
    }

    public function objection(): HasOne
    {
        return $this->hasOne(CallRatingObjectionFields::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ProjectSettings::class, 'project_id', 'project_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class, 'audio_id', 'record_id');
    }

    public function additionalCriteria(): HasMany
    {
        return $this->hasMany(CallRatingAdditionalCriteria::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (CallRating $model) {
            foreach ($model->getRelations() as $relation) {
                if ($relation instanceof Collection) {
                    $relation->each(function ($item) {
                        $item->delete();
                    });
                } else {
                    if ($relation) $relation->delete();
                }
            }
        });


    }
}
