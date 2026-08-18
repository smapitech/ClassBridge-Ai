<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointerEvent extends Model
{
    protected $table = 'pointer_events';
    public $timestamps = false; // we only store created_at
    protected $fillable = ['school_id', 'classroom_session_id', 'user_id', 'x_position', 'y_position', 'target_area', 'metadata', 'created_at'];

    protected function casts(): array
    {
        return ['x_position' => 'decimal:2', 'y_position' => 'decimal:2', 'metadata' => 'array', 'created_at' => 'datetime'];
    }

    public function session(): BelongsTo { return $this->belongsTo(ClassroomSession::class, 'classroom_session_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}