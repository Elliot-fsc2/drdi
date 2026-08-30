<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'program_id',
        'instructor_id',
        'semester_id',
    ];

    public function getPhotoUrlAttribute(): string
    {
        return $this->photo
            ? asset('storage/'.$this->photo)
            : asset('images/course_page.jpg');
    }

    public function scopeActive($query)
    {
        return $query->whereHas('semester', function ($q) {
            $q->active();
        });
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
