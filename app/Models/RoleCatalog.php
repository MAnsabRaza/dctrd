<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleCatalog extends Model
{
    protected $table = 'role_catalogs';

    protected $fillable = [
        'family', 'key', 'label', 'supersedes', 'sort_order', 'active',
    ];

    protected $casts = [
        'supersedes' => 'array',
        'active'     => 'boolean',
    ];

    const FAMILY_INSTRUCTOR   = 'instructor';
    const FAMILY_ORGANIZATION = 'organization';
    const FAMILY_CUSTOMER     = 'customer';

    public function userRoleRequests()
    {
        return $this->hasMany(UserRoleRequest::class, 'role_catalog_id');
    }

    public function regulatoryTemplates()
    {
        return $this->hasMany(RegulatoryFormTemplate::class, 'role_catalog_id');
    }

    public function scopeFamily($query, string $family)
    {
        return $query->where('family', $family);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}