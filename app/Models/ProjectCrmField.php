<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectCrmField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'google_column',
        'project_id',
        'index_number',
    ];

    public function ratings(): HasOne
    {
        return $this->hasOne(CallRatingCrmFields::class, 'crm_field_id');
    }
}
