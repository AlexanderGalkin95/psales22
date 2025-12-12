<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdditionalCriteriaOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'value',
        'additional_criteria_id',
    ];

    public function ratings(): HasMany
    {
        return $this->hasMany(CallRatingAdditionalCriteria::class, 'additional_criteria_option_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (AdditionalCriteriaOption $model) {
            $model->ratings()->delete();
        });
    }
}
