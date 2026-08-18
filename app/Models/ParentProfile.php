<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentProfile extends Model
{
    protected $table = 'parent_profiles';
    protected $fillable = ['user_id', 'school_id', 'relationship', 'occupation', 'emergency_contact', 'address', 'status'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function school(): BelongsTo { return $this->belongsTo(School::class); }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot('relationship')->withTimestamps();
    }

    public function scopeForSchool($q, $id) { return $q->where('school_id', $id); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
}