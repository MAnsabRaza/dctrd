<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarLog extends Model
{
    use HasFactory;
    protected $table = 'calendar_logs';
     protected $fillable = [
        'user_id', 'provider', 'action',
        'status', 'booking_order_id',
        'request_data', 'response_data',
        'error_message'
    ];

    protected $casts = [
        'request_data'  => 'array',
        'response_data' => 'array',
    ];

    public function getDetailsAttribute(): string
    {
        if (!empty($this->error_message)) {
            return $this->error_message;
        }

        return data_get($this->response_data, 'message', data_get($this->response_data, 'id', '-'));
    }
}
