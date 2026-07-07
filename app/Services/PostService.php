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
            'images_path' => $data['images_path'] ?? null,
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
            'images_path' => $data['images_path'] ?? null,
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
        $sectionIds = $data['section_ids'] ?? [];

        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'images_path' => $data['images_path'] ?? null,
            'author_id' => auth()->id(),
            'target_type' => PostType::SECTIONS,
        ]);

        $post->sections()->attach($sectionIds);

        activity('Post Creation by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);
    }

    public function updateForSection(Post $post, array $data): void
    {
        $sectionIds = $data['section_ids'] ?? [];

        $post->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'images_path' => $data['images_path'] ?? null,
        ]);

        $post->sections()->sync($sectionIds);

        activity('Post Update by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Updated post: '.$data['title']);
    }

    public function deletePost(Post $post)
    {
        $post->delete();

        activity('Post Deletion by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Deleted post: '.$post->title);
    }
}
