<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingImport extends Model
{
    use HasFactory;

    protected $table = 'booking_imports';

    protected $fillable = [
        'user_id',
        'file_path',
        'file_name',
        'type',
        'total_rows',
        'processed_rows',
        'success_rows',
        'failed_rows',
        'errors',
        'status',
    ];

    protected $casts = [
        'errors'         => 'array',
        'total_rows'     => 'integer',
        'processed_rows' => 'integer',
        'success_rows'   => 'integer',
        'failed_rows'    => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'    => '<span class="badge badge-warning">Pending</span>',
            'processing' => '<span class="badge badge-info">Processing</span>',
            'completed'  => '<span class="badge badge-success">Completed</span>',
            'failed'     => '<span class="badge badge-danger">Failed</span>',
            default      => '<span class="badge badge-secondary">' . $this->status . '</span>',
        };
    }
}