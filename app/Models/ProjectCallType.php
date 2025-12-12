<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectCallType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'project_id',
        'rate_crm',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function ratings(): HasOne
    {
        return $this->hasOne(CallRating::class, 'call_type_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ProjectSettings::class, 'call_type_id');
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (ProjectCallType $model) {
            $model->settings()->each(function ($setting) {
                $setting->delete();
            });
        });
    }
}
