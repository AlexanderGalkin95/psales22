<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'niche',
        'domain',
        'active',
        'logo',
        'admin_id',
        'contact_name',
        'contact_phone',
        'contact_tariff',
        'contact_agreement',
    ];

    protected $withCount = ['projects'];

    protected $with = ['company_admin'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function links()
    {
        return $this->hasMany(CompanyLink::class);
    }

    public function company_admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function managers()
    {
        return $this->hasMany(CompanyManager::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Company $model) {
            $model->links()->delete();
            $model->managers()->delete();
        });
    }
}
