<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject_code', 'subject_name', 'description'])]
class Subject extends Model
{
    public function subjectOfferings(): HasMany
    {
        return $this->hasMany(SubjectOffering::class);
    }
}
