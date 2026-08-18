<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodingSessionFile extends Model
{
    protected $fillable = [
        'school_id',
        'coding_session_id',
        'filename',
        'language',
        'content',
        'sort_order',
        'is_entry_point',
        'created_by',
        'updated_by',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'is_entry_point' => 'boolean',
        ];
    }

    public function session(): BelongsTo { return $this->belongsTo(CodingSession::class, 'coding_session_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
}
