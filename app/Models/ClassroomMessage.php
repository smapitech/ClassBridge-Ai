<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomMessage extends Model
{
    protected $table = 'classroom_messages';
    protected $fillable = ['school_id', 'classroom_session_id', 'user_id', 'message', 'message_type'];

    public function session(): BelongsTo { return $this->belongsTo(ClassroomSession::class, 'classroom_session_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}