<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'foreign_manager_id',
        'integration_id',
        'name',
        'email',
        'is_admin',
        'is_active',
        'group_id',
        'role_id',
        'role_name',
        'group_name',
    ];
}
