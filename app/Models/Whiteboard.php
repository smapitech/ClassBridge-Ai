<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Whiteboard extends Model
{
    protected $fillable = [
        'school_id',
        'classroom_session_id',
        'live_classroom_id',
        'title',
        'current_page_id',
        'created_by',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassroomSession::class, 'classroom_session_id');
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(LiveClassroom::class, 'live_classroom_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WhiteboardPage::class);
    }

    public function currentPage(): BelongsTo
    {
        return $this->belongsTo(WhiteboardPage::class, 'current_page_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(WhiteboardSnapshot::class);
    }
}
