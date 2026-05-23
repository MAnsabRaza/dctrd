<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingImportLog extends Model
{
    protected $fillable = ['import_id', 'row_number', 'level', 'action', 'model_type', 'model_id', 'message', 'payload'];

    protected $casts = ['payload' => 'array'];

    public function import()
    {
        return $this->belongsTo(BookingImport::class, 'import_id');
    }
}
