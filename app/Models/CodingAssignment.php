<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CodingAssignment extends Model
{
    protected $fillable = [
        'school_id', 'class_id', 'subject_id', 'teacher_id',
        'title', 'description', 'instructions',
        'starter_html', 'starter_css', 'starter_js',
        'due_at', 'status',
    ];

    protected function casts(): array { return ['due_at' => 'datetime']; }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function classe(): BelongsTo { return $this->belongsTo(Classe::class, 'class_id'); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function submissions(): HasMany { return $this->hasMany(CodingAssignmentSubmission::class); }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }

    /**
     * Combine starter HTML, CSS, and JS into a safe previewable HTML string.
     */
    public function starterHtmlPreview(): string
    {
        $html = $this->starter_html ?? '';
        $css = $this->starter_css ?? '';
        $js = $this->starter_js ?? '';

        // Escape the closing script tag in JS content to prevent iframe srcdoc breakage
        $safeJs = str_replace('</', '<\/', $js);

        return "<!DOCTYPE html>\n<html>\n<head><style>{$css}</style></head>\n<body>\n{$html}\n<script>{$safeJs}<\/script>\n</body>\n</html>";
    }
}