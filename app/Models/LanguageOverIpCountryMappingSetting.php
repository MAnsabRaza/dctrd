<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageOverIpCountryMappingSetting extends Model
{
    use HasFactory;
    protected $table = 'language_over_ip_country_mapping_settings';
    protected $fillable = ['country_id', 'language','language_code'];

    public function country()
    {
        return $this->belongsTo(Region::class, 'country_id');
    }
}
