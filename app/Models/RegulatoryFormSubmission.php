<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RegulatoryFormSubmission extends Model
{
    protected $table = 'regulatory_form_submissions';

    protected $fillable = [
        'user_id', 'role_catalog_id', 'template_id', 'form_id', 'level', 'data', 'status', 'rejection_reason',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Purana relation — backward compatibility ke liye rakha hai
    public function template()
    {
        return $this->belongsTo(RegulatoryFormTemplate::class, 'template_id');
    }

    // ✅ NAYA: ab asal source Form hai
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function roleCatalog()
    {
        return $this->belongsTo(RoleCatalog::class, 'role_catalog_id');
    }
}