<?php

namespace App\Models;

use App\Enums\PresentationStatus;
use App\Enums\PresentationType;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
  protected $fillable = [
    'name',
    'section_id',
    'leader_id',
    'final_title_id',
    'status',
    'final_grade',
  ];

  public function section()
  {
    return $this->belongsTo(Section::class);
  }

  public function leader()
  {
    return $this->belongsTo(Student::class, 'leader_id');
  }

  public function members()
  {
    return $this->belongsToMany(Student::class, 'members', 'group_id', 'student_id');
  }

  public function proposals()
  {
    return $this->hasMany(Proposal::class);
  }

  public function finalTitle()
  {
    return $this->belongsTo(Proposal::class, 'final_title_id');
  }

  public function consultations()
  {
    return $this->hasMany(Consultation::class);
  }

  public function personnel()
  {
    return $this->hasMany(Personnel::class);
  }

  public function fee()
  {
    return $this->hasOne(GroupFee::class);
  }

  public function schedules()
  {
    return $this->hasMany(Schedule::class);
  }

  public function researchLibrary()
  {
    return $this->hasOne(ResearchLibrary::class);
  }

  public function scopePassed($query)
  {
    return $query->where('status', 'passed');
  }

  public function isEligibleForLibrary(): bool
  {
    return $this->schedules()
      ->where('presentation_type', PresentationType::THESIS_B_FINAL)
      ->where('status', PresentationStatus::PASSED)
      ->exists();
  }
}
