<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomParticipant extends Model
{
    protected $table = 'classroom_participants';
    protected $fillable = [
        'school_id', 'classroom_session_id', 'user_id',
        'role_in_session', 'joined_at', 'left_at', 'is_active', 'permissions',
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

    public function session(): BelongsTo { return $this->belongsTo(ClassroomSession::class, 'classroom_session_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}