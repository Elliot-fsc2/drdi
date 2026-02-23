<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'section_id',
        'group_id',
        'venue',
        'date',
        'start_time',
        'end_time',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
