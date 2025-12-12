<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectManagerAssessors extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'project_call_settings_sales_manager_id'
    ];

    protected $visible = [
        'id',
        'project_id',
        'user_id',
        'project_call_settings_sales_manager_id',
        'assessor',
        'project'
    ];

    /**
     * Get the user that owns the ProjectManagerAssessors
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the project that owns the ProjectManagerAssessors
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
