<?php

namespace App\Filament\Resources\Instructors\Pages;

use App\Filament\Resources\Instructors\InstructorResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInstructor extends EditRecord
{
    protected static string $resource = InstructorResource::class;

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

        $data['department_id'] = $this->record->department_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
            unset($data['password_confirmation']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $instructor = $this->record;
        $user = User::where('profileable_type', $instructor::class)
            ->where('profileable_id', $instructor->id)
            ->first();

        if (! $user) {
            return;
        }

        $updates = [];

        if (filled($this->data['email'] ?? null)) {
            $updates['email'] = $this->data['email'];
        }

        if (filled($this->data['password'] ?? null)) {
            $updates['password'] = $this->data['password'];
        }

        if (! empty($updates)) {
            $user->update($updates);
        }
    }
}
