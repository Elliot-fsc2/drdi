<?php

namespace App\Models;

use App\Enums\PostType;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'author_id',
        'target_type',
        'section_id',
        'images_path',
    ];

    protected function casts(): array
    {
        return [
            'target_type' => PostType::class,
            'images_path' => 'array',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
