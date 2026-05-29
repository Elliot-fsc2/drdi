<?php

namespace App\Services;

use App\Enums\PostType;
use App\Models\Post;

class PostService
{
    public function createForInstructors(array $data)
    {
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'author_id' => auth()->id(),
            'target_type' => PostType::INSTRUCTORS,
        ]);

        activity('Post Creation by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);
    }

    public function createForStudents(array $data)
    {
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'author_id' => auth()->id(),
            'target_type' => PostType::STUDENTS,
        ]);

        activity('Post Creation by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);
    }

    public function createForSection(array $data)
    {
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'author_id' => auth()->id(),
            'target_type' => PostType::SECTIONS,
        ]);

        activity('Post Creation by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);
    }
}
