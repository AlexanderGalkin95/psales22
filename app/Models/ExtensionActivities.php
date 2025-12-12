<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtensionActivities extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_id',
        'user_id',
        'enabled',
        'online',
        'online_date',
        'offline_date',
    ];

    public $timestamps = false;
}
