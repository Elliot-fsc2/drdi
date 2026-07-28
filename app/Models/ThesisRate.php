<?php

namespace App\Models;

use App\Enums\PanelistRole;
use App\Enums\PersonnelRole;
use App\Enums\ThesisRatesType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ThesisRate extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'type',
        'personnel_role',
        'panelist_role',
    ];

    protected function casts(): array
    {
        return [
            'type' => ThesisRatesType::class,
            'personnel_role' => PersonnelRole::class,
            'panelist_role' => PanelistRole::class,
        ];
    }

    public function semesters()
    {
        return $this->belongsToMany(Semester::class, 'semester_rates');
    }

    public function scopeForPersonnelRole(Builder $query, ?PersonnelRole $role): Builder
    {
        return $query->where(function (Builder $q) use ($role) {
            $q->where('personnel_role', $role?->value)
                ->orWhereNull('personnel_role');
        });
    }

    public function scopeForPanelistRole(Builder $query, ?PanelistRole $role): Builder
    {
        return $query->where(function (Builder $q) use ($role) {
            $q->where('panelist_role', $role?->value)
                ->orWhereNull('panelist_role');
        });
    }
}
