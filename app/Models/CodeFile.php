<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CodeFile extends Model
{
    protected $fillable = ['school_id', 'code_project_id', 'filename', 'language', 'content', 'sort_order'];

    public function project(): BelongsTo { return $this->belongsTo(CodeProject::class, 'code_project_id'); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }
}