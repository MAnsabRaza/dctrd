<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckoutModule extends Model
{
    use HasFactory;
    protected $table = 'checkout_modules';
    protected $fillable = [
        'name',
        'input_type',
        'config',
        'price_rule',
        'order_index',
        'is_active',
        'is_required',
    ];

    protected $casts = [
        'config' => 'array',
        'price_rule' => 'array',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
    ];

    // ─────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────

    public function translations(): HasMany
    {
        return $this->hasMany(CheckoutModuleTranslation::class, 'module_id');
    }

    public function orgModules(): HasMany
    {
        return $this->hasMany(OrgCheckoutModule::class, 'module_id');
    }

    public function entityModules(): HasMany
    {
        return $this->hasMany(EntityCheckoutModule::class, 'module_id');
    }

    // ─────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────

    /**
     * Get translated label for current locale
     */
    public function getTranslatedLabelAttribute()
    {
        $locale = app()->getLocale();
        
        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            return $translation->label;
        }

        // Fallback to English
        $englishTranslation = $this->translations()
            ->where('locale', 'en')
            ->first();

        return $englishTranslation ? $englishTranslation->label : $this->name;
    }

    /**
     * Get translated help text for current locale
     */
    public function getTranslatedHelpTextAttribute()
    {
        $locale = app()->getLocale();
        
        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        if ($translation) {
            return $translation->help_text;
        }

        // Fallback to English
        $englishTranslation = $this->translations()
            ->where('locale', 'en')
            ->first();

        return $englishTranslation ? $englishTranslation->help_text : null;
    }

    // ─────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index', 'asc');
    }
}
