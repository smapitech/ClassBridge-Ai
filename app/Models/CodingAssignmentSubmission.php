<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodingAssignmentSubmission extends Model
{
    protected $fillable = [
        'school_id', 'coding_assignment_id', 'student_id', 'code_project_id',
        'submitted_at', 'status', 'teacher_feedback', 'score',
    ];

    protected function casts(): array { return ['submitted_at' => 'datetime', 'score' => 'decimal:2']; }

    public function assignment(): BelongsTo { return $this->belongsTo(CodingAssignment::class, 'coding_assignment_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function project(): BelongsTo { return $this->belongsTo(CodeProject::class, 'code_project_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}