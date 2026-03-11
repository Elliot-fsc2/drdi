<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use function Symfony\Component\String\s;

class Program extends Model
{
  protected $fillable = [
    'name',
    'department_id',
  ];

  public function department()
  {
    return $this->belongsTo(Department::class);
  }

  public function sections()
  {
    return $this->hasMany(Section::class);
  }
}
