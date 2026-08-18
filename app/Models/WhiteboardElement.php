<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhiteboardElement extends Model
{
    protected $table = 'whiteboard_elements';
    protected $fillable = [
        'school_id',
        'classroom_session_id',
        'whiteboard_id',
        'whiteboard_page_id',
        'element_uuid',
        'user_id',
        'updated_by',
        'element_type',
        'z_index',
        'is_locked',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'z_index' => 'integer',
            'is_locked' => 'boolean',
        ];
    }

    public function session(): BelongsTo { return $this->belongsTo(ClassroomSession::class, 'classroom_session_id'); }
    public function whiteboard(): BelongsTo { return $this->belongsTo(Whiteboard::class); }
    public function page(): BelongsTo { return $this->belongsTo(WhiteboardPage::class, 'whiteboard_page_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}
