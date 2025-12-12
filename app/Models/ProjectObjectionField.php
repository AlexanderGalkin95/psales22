<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectObjectionField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'google_column',
        'google_column_rate',
        'project_id',
    ];

    public static function getGoogleColumnKeys($projectId): array
    {
        $columns = self::where('project_id', $projectId)->first(['google_column', 'google_column_rate']);
        return $columns ? $columns->toArray() : [];
    }

    public function ratings(): HasOne
    {
        return $this->hasOne(CallRatingObjectionFields::class, 'objection_field_id');
    }
}
