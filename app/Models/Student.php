<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

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

    protected static function booted(): void
    {
        static::deleting(function (Student $student) {
            DB::transaction(function () use ($student) {
                $student->user?->delete();

                Group::where('leader_id', $student->id)->update(['leader_id' => null]);

                $student->groups()->detach();
                $student->sections()->detach();
            });
        });
    }

    public function setFirstNameAttribute(string $value): void
    {
        $this->attributes['first_name'] = ucfirst(trim($value));
    }

    public function setLastNameAttribute(string $value): void
    {
        $this->attributes['last_name'] = ucfirst(trim($value));
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'profileable');
    }

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

    public function scopeActiveSection($query)
    {
        return $query->whereHas('sections', function ($q) {
            $q->whereHas('semester', function ($q2) {
                $q2->active();
            });
        });
    }
}
