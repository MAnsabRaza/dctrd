<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class CalendarIntegration extends Model
{
    use HasFactory;
    protected $table = 'calendar_integrations';
     protected $fillable = [
        'user_id', 'provider', 'client_id',
        'client_secret', 'access_token',
        'refresh_token', 'token_expires_at',
        'calendar_id', 'sync_token', 'status',
        'last_sync_at', 'last_error'
    ];

     protected $casts = [
        'token_expires_at' => 'datetime',
        'last_sync_at'     => 'datetime',
        'client_secret'    => 'encrypted',
        'access_token'     => 'encrypted',
        'refresh_token'    => 'encrypted',
    ];
    protected $hidden = [
        'access_token',
        'refresh_token'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at
            && now()->isAfter($this->token_expires_at);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected'
            && !empty($this->access_token);
    }
}
