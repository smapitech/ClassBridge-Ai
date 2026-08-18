<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPagePricingItem extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_text',
        'features',
        'button_text',
        'button_url',
        'is_popular',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_popular' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
