<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchLibrary extends Model
{
  protected $fillable = [
    'group_id',
    'academic_year',
    'abstract',
    'file_path',
  ];

  public function group()
  {
    return $this->belongsTo(Group::class);
  }
}
