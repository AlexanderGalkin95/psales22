<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCallSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'statuses',
        'filter_duration_from',
        'filter_duration_to',
    ];

    protected $casts = [
        'statuses' => 'array',
    ];
}
