<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodingSessionMessage extends Model
{
    protected $fillable = [
        'school_id',
        'coding_session_id',
        'user_id',
        'message',
        'message_type',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function session(): BelongsTo { return $this->belongsTo(CodingSession::class, 'coding_session_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}
