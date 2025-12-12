<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationPipelineStatus extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'integration_pipeline_id',
        'pipeline_id',
        'status_id',
        'name',
        'sort',
        'is_editable',
        'color',
        'type',
        'account_id',
    ];

    protected $visible = [
        'id',
        'integration_pipeline_id',
        'pipeline_id',
        'status_id',
        'name',
        'sort',
        'is_editable',
        'color',
        'type',
        'account_id',
    ];
}
