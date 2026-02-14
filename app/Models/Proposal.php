<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = ['title', 'description', 'group_id', 'submitted_by', 'status', 'feedback'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(Student::class, 'submitted_by');
    }
}
