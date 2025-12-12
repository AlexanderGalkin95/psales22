<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'author_id',
        'description'
    ];

    protected $visible = [
        'id',
        'task_id',
        'author_id',
        'description',
        'created_at',
        'author'
    ];

    /**
     * Get the author that modified the Task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id')->select('name', 'id');
    }
}
