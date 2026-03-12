<?php

namespace App\Models;

use App\Collections\InstructorCollection;
use App\Enums\InstructorRole;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[CollectedBy(InstructorCollection::class)]
class Instructor extends Model
{
  /** @use HasFactory<\Database\Factories\InstructorFactory> */
  use HasFactory;

  protected $fillable = [
    'first_name',
    'last_name',
    'department_id',
    'role',
  ];

  protected function casts(): array
  {
    return [
      'role' => InstructorRole::class,
    ];
  }

  public function getFullNameAttribute(): string
  {
    return "{$this->first_name} {$this->last_name}";
  }

  public function department()
  {
    return $this->belongsTo(Department::class);
  }

  public function classes()
  {
    return $this->hasMany(Section::class);
  }

  public function personnel()
  {
    return $this->hasMany(Personnel::class);
  }

  public function consultations()
  {
    return $this->hasMany(Consultation::class);
  }
}
