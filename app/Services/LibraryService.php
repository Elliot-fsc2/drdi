<?php

namespace App\Services;

use App\Models\Group;
use App\Models\ResearchLibrary;

class LibraryService
{
    public function create(Group $group, array $data): ResearchLibrary
    {
        if ($group->isEligibleForLibrary()) {
            $group->researchLibrary()->create([
                'title' => $group->finalTitle->title,
                'abstract' => $data['abstract'],
                'is_published' => $data['is_published'] ?? false,
                'file_path' => $data['file_path'] ?? null,
                'published_at' => $data['published_at'] ?? null,
            ]);
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
    }
}
