<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallRatingObjectionFields extends Model
{
    use HasFactory;

    protected $table = 'call_rating_objection_fields';

    protected $fillable = [
        'call_rating_id',
        'objection_field_id',
        'google_column',
        'objection_rate',
        'google_column_rate',
        'value'
    ];

    public function getKeyName()
    {
        return 'call_rating_id';
    }

    public function objection(): BelongsTo
    {
        return $this->belongsTo(ProjectObjectionField::class, 'objection_field_id');
    }
}
