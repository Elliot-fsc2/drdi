<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'section_id', 'leader_id'];

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
}
