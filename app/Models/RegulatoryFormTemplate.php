<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegulatoryFormTemplate extends Model
{
    protected $table = 'regulatory_form_templates';

    protected $fillable = [
        'role_catalog_id', 'level', 'label', 'fields', 'countries', 'active',
    ];

    protected $casts = [
        'fields'    => 'array',
        'countries' => 'array',
        'active'    => 'boolean',
    ];

    public function roleCatalog()
    {
        return $this->belongsTo(RoleCatalog::class, 'role_catalog_id');
    }

    public function submissions()
    {
        return $this->hasMany(RegulatoryFormSubmission::class, 'template_id');
    }
}