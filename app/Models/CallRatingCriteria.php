<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallRatingCriteria extends Model
{
    use HasFactory;

    protected $table = 'call_rating_criteria';

    protected $fillable = [
        'call_rating_id',
        'criteria_id',
        'value'
    ];

    public function getKeyName(): string
    {
        return 'call_rating_id';
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }
}
