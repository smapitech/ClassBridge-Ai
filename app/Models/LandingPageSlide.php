<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageSlide extends Model
{
    protected $fillable = [
        'label',
        'headline',
        'subtitle',
        'primary_button_text',
        'primary_button_url',
        'secondary_button_text',
        'secondary_button_url',
        'image',
        'background_style',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
