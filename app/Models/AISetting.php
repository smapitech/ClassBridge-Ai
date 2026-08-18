<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AISetting extends Model
{
    protected $table = 'ai_settings';

    protected $fillable = [
        'school_id', 'default_provider_id', 'ai_enabled',
        'allow_teacher_ai', 'allow_school_override',
        'monthly_generation_limit', 'monthly_token_limit',
        'monthly_cost_limit', 'settings',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
            'allow_teacher_ai' => 'boolean',
            'allow_school_override' => 'boolean',
            'settings' => 'array',
            'monthly_cost_limit' => 'decimal:4',
        ];
    }

    public function school(): BelongsTo { return $this->belongsTo(School::class); }
    public function defaultProvider(): BelongsTo { return $this->belongsTo(AIProvider::class, 'default_provider_id'); }

    /** Get global settings (school_id = null) */
    public static function global(): ?self
    {
        return static::whereNull('school_id')->first();
    }

    /** Get or create settings for a school */
    public static function forSchool(int $schoolId): self
    {
        return static::firstOrCreate(
            ['school_id' => $schoolId],
            ['ai_enabled' => true, 'allow_teacher_ai' => true]
        );
    }
}