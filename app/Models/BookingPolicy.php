<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPolicy extends Model
{
    use HasFactory;

    protected $table = 'booking_policies';

    protected $fillable = [
        'booking_id',
        'cancellation_type',
        'free_cancel_hours',
        'cancellation_fee_percent',
        'reschedule_allowed',
        'reschedule_before_hours',
        'max_reschedules',
        'noshow_fee_percent',
        'deposit_required',
        'deposit_percent',
        'deposit_due_days',
        'balance_due_days_before',
        'policy_text'
    ];

    // ✅ CASTS (VERY IMPORTANT)
    protected $casts = [
        'free_cancel_hours' => 'integer',
        'cancellation_fee_percent' => 'decimal:2',
        'reschedule_allowed' => 'boolean',
        'reschedule_before_hours' => 'integer',
        'max_reschedules' => 'integer',
        'noshow_fee_percent' => 'decimal:2',
        'deposit_required' => 'boolean',
        'deposit_percent' => 'decimal:2',
        'deposit_due_days' => 'integer',
        'balance_due_days_before' => 'integer',
    ];

    // ✅ RELATIONSHIP
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}