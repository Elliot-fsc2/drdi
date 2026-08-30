<?php

namespace App\Services;

use App\Enums\PostType;
use App\Models\Instructor;
use App\Models\Post;
use App\Models\Student;
use App\Models\User;
use App\Notifications\NewAnnouncement;

class PostService
{
    public function __construct(protected NotificationService $notificationService) {}

    public function createForInstructors(array $data)
    {
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'images_path' => $data['images_path'] ?? null,
            'author_id' => auth()->id(),
            'target_type' => PostType::INSTRUCTORS,
        ]);

        activity('Post Creation by '.(auth()->user()?->name ?? 'System'))
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);

        $instructorIds = Instructor::where('role', 'Instructor')->pluck('id');

        $users = User::query()
            ->where('profileable_type', Instructor::class)
            ->whereIn('profileable_id', $instructorIds)
            ->get();

        $this->notificationService->sendMany($users, new NewAnnouncement($post));
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

        activity('Post Creation by '.(auth()->user()?->name ?? 'System'))
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);

        $users = User::query()
            ->where('profileable_type', Student::class)
            ->get();

        $this->notificationService->sendMany($users, new NewAnnouncement($post));
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

        activity('Post Creation by '.(auth()->user()?->name ?? 'System'))
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Created a new post: '.$data['title']);

        $users = User::query()
            ->where('profileable_type', Student::class)
            ->whereHasMorph('profileable', [Student::class], fn ($q) => $q->whereHas('sections', fn ($q2) => $q2->whereIn('sections.id', $sectionIds)))
            ->get();

        $this->notificationService->sendMany($users, new NewAnnouncement($post));
    }

    public function updatePost(Post $post, array $data): void
    {
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

        activity('Post Update by '.(auth()->user()?->name ?? 'System'))
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Updated post: '.$data['title']);
    }

    public function deletePost(Post $post)
    {
        $post->delete();

        activity('Post Deletion by '.(auth()->user()?->name ?? 'System'))
            ->performedOn($post)
            ->withProperties(['post_id' => $post->id])
            ->log('Deleted post: '.$post->title);
    }
}
