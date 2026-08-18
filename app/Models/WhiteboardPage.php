<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhiteboardPage extends Model
{
    protected $fillable = [
        'whiteboard_id',
        'page_key',
        'title',
        'page_number',
        'background_type',
        'background_value',
        'thumbnail_path',
        'is_locked',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'page_number' => 'integer',
            'is_locked' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function whiteboard(): BelongsTo
    {
        return $this->belongsTo(Whiteboard::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(WhiteboardElement::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(WhiteboardSnapshot::class);
    }
}
