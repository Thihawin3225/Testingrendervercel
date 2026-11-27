<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'academic_year_id',
    'year_level_id',
    'major_id',
    'semester_id',
    'subject_id',
])]
class SubjectOffering extends Model
{
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function studyMaterials(): HasMany
    {
        return $this->hasMany(StudyMaterial::class);
    }

    public function peerLearningRequests(): HasMany
    {
        return $this->hasMany(PeerLearningRequest::class);
    }

    public function mentorProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            MentorProfile::class,
            'mentor_profile_subjects',
            'subject_offering_id',
            'mentor_profile_id'
        )->withPivot('proficiency_level')->withTimestamps();
    }
}
