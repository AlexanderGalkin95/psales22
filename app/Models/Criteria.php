<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criteria extends Model
{
    use HasFactory;

    protected $table = 'criteria';

    protected $fillable = [
        'name',
        'legend',
        'project_id',
        'google_column',
        'index_number',
    ];


    public function settings(): HasMany
    {
        return $this->hasMany(ProjectSettings::class, 'criteria_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(CallRatingCriteria::class);
    }


    public static function boot()
    {
        parent::boot();

        static::deleting(function (Criteria $model) {
            $model->settings()->each(function ($setting) {
                $setting->delete();
            });
        });
    }
}
