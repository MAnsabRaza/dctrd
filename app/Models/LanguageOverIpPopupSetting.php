<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageOverIpPopupSetting extends Model
{
    use HasFactory;
    protected $table = 'language_over_ip_popup_settings';
    protected $fillable = [
        'language',
        'notification_title',
        'notification_text',
        'confirm_button_text',
        'cancel_button_text',
        'action_type',
    ];
}
