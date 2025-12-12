<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdditionalCriteria extends Model
{
    use HasFactory;

    protected $table = 'additional_criteria';

    protected $fillable = [
        'name',
        'legend',
        'project_id',
        'index_number',
    ];

    public function ratings(): HasMany
    {
        return $this->hasMany(CallRatingAdditionalCriteria::class, 'additional_criteria_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(AdditionalCriteriaOption::class, 'additional_criteria_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (AdditionalCriteria $model) {
            $model->ratings()->delete();
            $model->options()->delete();
        });
    }
}
