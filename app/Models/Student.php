<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'student_number',
        'program_id',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'members', 'student_id', 'group_id');
    }

    public function leads()
    {
        return $this->hasMany(Group::class, 'leader_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }
}
