<?php

namespace App\Models;

use App\Enums\InstructorRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;

class Instructor extends Model
{
    /** @use HasFactory<\Database\Factories\InstructorFactory> */
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'department_id',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'role' => InstructorRole::class,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Instructor $instructor) {
            DB::transaction(function () use ($instructor) {
                $instructor->user?->delete();
                $instructor->consultations()->delete();
                $instructor->personnel()->delete();
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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function classes()
    {
        return $this->hasMany(Section::class);
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'profileable');
    }
}
