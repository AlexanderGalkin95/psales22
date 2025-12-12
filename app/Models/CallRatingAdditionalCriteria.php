<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class CallRatingAdditionalCriteria extends Model
{
    use HasFactory;

    protected $table = 'call_rating_additional_criteria';

    protected $fillable = [
        'call_rating_id',
        'additional_criteria_id',
        'additional_criteria_option_id',
        'value'
    ];

    public function getKeyName(): string
    {
        return 'call_rating_id';
    }

    public function additionalCriteria(): BelongsTo
    {
        return $this->belongsTo(AdditionalCriteria::class, 'additional_criteria_id')
            ->with(['options' => function(Relation $relation) {
                return $relation->where('id', '=', $this->additional_criteria_option_id);
            }]);
    }
}
