<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'status',
        'user_id',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
