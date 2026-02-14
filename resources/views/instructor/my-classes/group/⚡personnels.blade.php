<?php

use App\Enums\PersonnelRole;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Personnel;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas {
  use InteractsWithActions;
  use InteractsWithSchemas;

  public Group $group;
  public Section $section;

  #[Computed]
  public function personnels()
  {
    return Personnel::where('group_id', $this->group->id)
      ->with(['instructor.department'])
      ->get()
      ->map(function ($person) {
        return [
          'id' => $person->id,
          'name' => "{$person->instructor->first_name} {$person->instructor->last_name}",
          'department' => $person->instructor->department->name ?? 'N/A',
          'role' => $person->role->name,
        ];
      });
  }

  public function assignPersonnelAction(): Action
  {
    return Action::make('assignPersonnel')
      ->label('Assign Personnel')
      ->icon(Heroicon::UserPlus)
      ->color('primary')
      ->modalWidth('xl')
      ->modalCloseButton(false)
      ->modalHeading('Assign Personnel to Group')
      ->modalDescription("Select an instructor and assign them a role for this group.")
      ->form([
        Select::make('instructor_id')
          ->label('Instructor')
          ->options(function () {
            return Instructor::query()
              ->with('department')
              ->whereDoesntHave('personnel', function ($query) {
                $query->where('group_id', $this->group->id);
              })
              ->get()
              ->mapWithKeys(function ($instructor) {
                $dept = $instructor->department->name ?? 'N/A';
                return [$instructor->id => "{$instructor->first_name} {$instructor->last_name} ({$dept})"];
              })
              ->toArray();
          })
          ->required()
          ->searchable()
          ->native(false),
        Select::make('role')
          ->label('Role')
          ->options(PersonnelRole::class)
          ->required()
          ->native(false),
      ])
      ->successNotificationTitle('Personnel assigned successfully')
      ->action(function (array $data): void {
        Personnel::create([
          'instructor_id' => $data['instructor_id'],
          'group_id' => $this->group->id,
          'role' => $data['role'],
        ]);

        unset($this->personnels);
      });
  }

  public function removePersonnelAction(): Action
  {
    return Action::make('removePersonnel')
      ->requiresConfirmation()
      ->modalCloseButton(false)
      ->modalHeading('Remove Personnel')
      ->modalDescription('Are you sure you want to remove this personnel from the group?')
      ->modalSubmitActionLabel('Yes, Remove')
      ->color('danger')
      ->icon(Heroicon::Trash)
      ->successNotificationTitle('Personnel removed from group')
      ->action(function (array $arguments): void {
        $personnelId = $arguments['personnelId'];
        Personnel::destroy($personnelId);
        unset($this->personnels);
      });
  }
};
?>

<div class="p-4">
  <div class="mb-4 flex items-center justify-between">
    <h3 class="font-semibold text-slate-900">Assigned Personnel</h3>
    <x-filament::button wire:click="mountAction('assignPersonnel')" class="bg-blue-600 text-white">
      Assign Personnel
    </x-filament::button>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    @foreach($this->personnels as $person)
      <div class="bg-white border border-slate-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold">
              {{ substr($person['name'], 0, 1) }}
            </div>
            <div>
              <h4 class="font-semibold text-slate-900">{{ $person['name'] }}</h4>
              <p class="text-xs text-slate-500">{{ $person['department'] }}</p>
            </div>
          </div>

          <x-filament::dropdown placement="bottom-end">
            <x-slot name="trigger">
              <button class="p-1 hover:bg-slate-100 rounded transition-colors">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </x-slot>

            <x-filament::dropdown.list>
              <x-filament::dropdown.list.item icon="heroicon-o-trash" color="danger"
                wire:click="mountAction('removePersonnel', { personnelId: {{ $person['id'] }} })">
                Remove Personnel
              </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
          </x-filament::dropdown>
        </div>
        <div class="pt-3 border-t border-slate-100">
          <span class="text-sm text-slate-700 font-medium">{{ $person['role'] }}</span>
        </div>
      </div>
    @endforeach
  </div>

  <x-filament-actions::modals />
</div>