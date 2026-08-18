<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteboardSnapshot extends Model
{
    protected $fillable = [
        'school_id',
        'whiteboard_id',
        'whiteboard_page_id',
        'snapshot_data',
        'created_by',
        'name',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_data' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function whiteboard(): BelongsTo
    {
        return $this->belongsTo(Whiteboard::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(WhiteboardPage::class, 'whiteboard_page_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
