<?php

namespace App\Models;

use App\Enums\PersonnelRole;
use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $fillable = [
        'instructor_id',
        'group_id',
        'role',
    ];

    protected $casts = [
        'role' => PersonnelRole::class,
    ];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
