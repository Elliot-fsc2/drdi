<?php

use App\Enums\PersonnelRole;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Personnel;
use App\Models\Section;
use App\Services\FeeService;
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
            ->databaseTransaction()
            ->icon(Heroicon::UserPlus)
            ->color('primary')
            ->modalWidth('xl')
            ->modalCloseButton(false)
            ->modalHeading('Assign Personnel to Group')
            ->modalDescription('Select an instructor and assign them a role for this group.')
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
                Select::make('role')->label('Role')->options(PersonnelRole::class)->required()->native(false),
            ])
            ->successNotificationTitle('Personnel assigned successfully')
            ->action(function (array $data): void {
                Personnel::create([
                    'instructor_id' => $data['instructor_id'],
                    'group_id' => $this->group->id,
                    'role' => $data['role'],
                ]);

                // Recalculate honorarium based on updated personnel count
                $feeService = app(FeeService::class);
                $feeService->syncHonorarium($this->group);

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

                // Recalculate honorarium based on updated personnel count
                $feeService = app(FeeService::class);
                $feeService->syncHonorarium($this->group);

                unset($this->personnels);
            });
    }
};
?>

<div class="p-4 md:p-5 space-y-4">
    <div class="flex items-center justify-between gap-4">
        <p class="text-[0.8125rem] font-medium text-slate-500">Assigned Personnel</p>
        <x-filament::button wire:click="mountAction('assignPersonnel')" color="info">
            Assign Personnel
        </x-filament::button>
    </div>

    @if ($this->personnels->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-10 text-center">
            <p class="mb-1 font-semibold text-slate-700">No personnel assigned</p>
            <p class="text-sm text-slate-400">Assign instructors to serve as adviser or panelist for this group.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-2.5 md:grid-cols-2">
            @foreach ($this->personnels as $person)
                <div
                    class="relative overflow-hidden rounded-xl border border-slate-200 bg-white transition-all duration-200 hover:border-blue-200 hover:shadow-md">
                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl"
                        style="background: linear-gradient(to bottom, #0052FF, #4D7CFF);"></div>
                    <div class="py-4 pl-5 pr-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $person['name'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $person['department'] }}</p>
                                </div>
                            </div>
                            <x-filament::dropdown placement="bottom-end">
                                <x-slot name="trigger">
                                    <button class="shrink-0 rounded-lg p-1.5 transition-colors hover:bg-slate-100">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
                        <div class="mt-2.5 border-t border-slate-100 pt-2.5">
                            <span
                                class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                {{ $person['role'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-filament-actions::modals />
</div>
