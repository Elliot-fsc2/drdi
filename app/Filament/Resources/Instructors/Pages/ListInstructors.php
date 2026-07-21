<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Imports\InstructorImporter;
use App\Filament\Resources\Instructors\InstructorResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListInstructors extends ListRecords
{
    protected static string $resource = InstructorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(InstructorImporter::class),
            CreateAction::make(),
        ];
    }
}
