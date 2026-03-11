<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResearchLibrary extends Model
{
  protected $fillable = [
    'group_id',
    'title',
    'academic_year',
    'abstract',
    'file_path',
    'is_published',
    'published_at',
  ];

  public function group()
  {
    return $this->belongsTo(Group::class);
  }
}
