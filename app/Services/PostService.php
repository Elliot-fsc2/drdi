<?php

namespace App\Services;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;

class PostService
{
    public function createForInstructors(array $data)
    {
        Gate::authorize('create_announcements');

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
        Gate::authorize('create_announcements');

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
        Gate::authorize('create_announcements');

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

    public function updatePost(Post $post, array $data): void
    {
        Gate::authorize('update_announcements');

        $sectionIds = $data['section_ids'] ?? [];

        $post->update([
            'title' => $data['title'],
            'content' => $data['content'],
            'images_path' => $data['images_path'] ?? null,
            'target_type' => $data['target_type'] ?? $post->target_type,
        ]);

        if (! empty($sectionIds)) {
            $post->sections()->sync($sectionIds);
        } else {
            $post->sections()->detach();
        }

        activity('Post Update by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Updated post: '.$data['title']);
    }

    public function deletePost(Post $post)
    {
        Gate::authorize('delete_announcements');

        $post->delete();

        activity('Post Deletion by :causer.name')
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Deleted post: '.$post->title);
    }
}
