<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodingSessionParticipant extends Model
{
    protected $table = 'session_participants';

    protected $fillable = [
        'school_id',
        'coding_session_id',
        'user_id',
        'role_in_session',
        'joined_at',
        'left_at',
        'is_active',
        'typing_status',
        'active_file_key',
        'cursor_line',
        'cursor_column',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function session(): BelongsTo { return $this->belongsTo(CodingSession::class, 'coding_session_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}
