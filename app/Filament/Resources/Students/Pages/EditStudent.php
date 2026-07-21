<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = User::where('profileable_type', $this->record::class)
            ->where('profileable_id', $this->record->id)
            ->first();

        if ($user) {
            $data['email'] = $user->email;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $student = $this->record;
        $user = User::where('profileable_type', $student::class)
            ->where('profileable_id', $student->id)
            ->first();

        if (! $user) {
            return;
        }

        if (filled($this->data['email'] ?? null)) {
            $user->update(['email' => $this->data['email']]);
        }
    }
}
