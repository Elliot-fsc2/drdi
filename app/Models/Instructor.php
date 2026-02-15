<?php

namespace App\Models;

use App\Enums\InstructorRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function personnel()
    {
        return $this->hasMany(Personnel::class);
    }

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
