<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RegulatoryFormSubmission extends Model
{
    protected $table = 'regulatory_form_submissions';

    protected $fillable = [
        'user_id', 'role_catalog_id', 'template_id', 'form_id', 'form_submission_id', 'previous_submission_id',
        'level', 'data', 'status', 'rejection_reason', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'data'        => 'array', // ab sirf legacy/purane records ke liye — naye records ka data form_submissions me hai
    'reviewed_at' => 'datetime',
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

    // ✅ ab asal source Form hai
    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function roleCatalog()
    {
        return $this->belongsTo(RoleCatalog::class, 'role_catalog_id');
    }

    // ✅ NAYA: actual submitted answers ab yahan se aayenge (Form Builder ka apna table)
    public function formSubmission()
    {
        return $this->belongsTo(FormSubmission::class, 'form_submission_id');
    }

    public function previousSubmission()
    {
        return $this->belongsTo(self::class, 'previous_submission_id');
    }

    // ✅ NAYA: kis admin ne review kiya
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
