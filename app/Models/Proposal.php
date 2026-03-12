<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
  protected $fillable = ['title', 'description', 'group_id', 'submitted_by', 'status', 'feedback'];

  protected $casts = [
    'status' => ProposalStatus::class,
  ];
  public function group()
  {
    return $this->belongsTo(Group::class);
  }

  public function submittedBy()
  {
    return $this->belongsTo(Student::class, 'submitted_by');
  }
}
