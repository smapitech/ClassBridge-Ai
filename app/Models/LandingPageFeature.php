<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageFeature extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'link_text',
        'link_url',
        'feature_group',
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
