<?php

namespace App\Services;

use App\Models\Group;
use App\Models\ResearchLibrary;
use App\Notifications\ResearchPublished;
use App\Notifications\ResearchSubmitted;
use App\Notifications\ResearchUnpublished;

class LibraryService
{
    public function __construct(protected NotificationService $notificationService) {}

    public function create(Group $group, array $data): ResearchLibrary
    {
        if ($group->isEligibleForLibrary()) {
            $library = $group->researchLibrary()->create([
                'title' => $group->finalTitle->title,
                'abstract' => $data['abstract'],
                'is_published' => $data['is_published'] ?? false,
                'file_path' => $data['file_path'] ?? null,
                'published_at' => $data['published_at'] ?? null,
            ]);

            $this->notificationService->sendToRdo(new ResearchSubmitted($library));

            return $library;
        }
        throw new \Exception('Group is not eligible for library');
    }

    public function update(ResearchLibrary $library, array $data)
    {
        $library->update([
            'title' => $data['title'],
            'abstract' => $data['abstract'],
            'is_published' => $data['is_published'],
            'file_path' => $data['file_path'],
            'published_at' => $data['published_at'],
        ]);
    }

    public function delete(ResearchLibrary $library)
    {
        $library->delete();
    }

    public function publish(ResearchLibrary $library, string $publishedAt)
    {
        $library->update([
            'is_published' => true,
            'published_at' => $publishedAt ?? now(),
        ]);

        activity('Research has been published')
            ->performedOn($library)
            ->causedBy(auth()->user())
            ->withProperties(['library_id' => $library->id])
            ->log(sprintf('Research library "%s" has been published by %s', $library->title, auth()->user()->name));

        if ($library->group !== null) {
            $this->notificationService->sendToGroupMembersAndAdviser($library->group, new ResearchPublished($library));
        }
    }

    public function unpublish(ResearchLibrary $library)
    {
        $library->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        activity('Research has been unpublished')
            ->performedOn($library)
            ->causedBy(auth()->user())
            ->withProperties(['library_id' => $library->id])
            ->log(sprintf('Research library "%s" has been unpublished by %s', $library->title, auth()->user()->name));

        if ($library->group !== null) {
            $this->notificationService->sendToGroupMembersAndAdviser($library->group, new ResearchUnpublished($library));
        }
    }
}
