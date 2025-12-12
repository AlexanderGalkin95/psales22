<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallRatingCrmFields extends Model
{
    use HasFactory;

    protected $table = 'call_rating_crm_fields';

    protected $fillable = [
        'call_rating_id',
        'crm_field_id',
        'value'
    ];

    public function getKeyName(): string
    {
        return 'call_rating_id';
    }

    public function crm(): BelongsTo
    {
        return $this->belongsTo(ProjectCrmField::class, 'crm_field_id');
    }
}
