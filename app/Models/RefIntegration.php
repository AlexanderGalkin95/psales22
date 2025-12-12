<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefIntegration extends Model
{
    use HasFactory;

    protected $table = 'ref_integrations';

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }
}
