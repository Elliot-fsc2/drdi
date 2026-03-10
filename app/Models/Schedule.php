<?php

namespace App\Models;

use App\Enums\PresentationStatus;
use App\Enums\PresentationType;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
  protected $fillable = [
    'section_id',
    'group_id',
    'venue',
    'date',
    'start_time',
    'end_time',
    'presentation_type',
    'status',
    'panelists',
  ];

  public function casts()
  {
    return [
      'panelists' => 'array',
      'status' => PresentationStatus::class,
      'presentation_type' => PresentationType::class,
    ];
  }

  public function section()
  {
    return $this->belongsTo(Section::class);
  }

  public function group()
  {
    return $this->belongsTo(Group::class);
  }
}
