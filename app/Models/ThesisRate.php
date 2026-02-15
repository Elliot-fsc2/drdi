<?php

namespace App\Models;

use App\Enums\ThesisRatesType;
use Illuminate\Database\Eloquent\Model;

class ThesisRate extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => ThesisRatesType::class,
        ];
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class, 'semester_rates');
    }

}
