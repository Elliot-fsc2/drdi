<?php

use App\Enums\InstructorRole;
use App\Models\Group;
use App\Models\Instructor;
use App\Services\GroupService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts::rdo.app')] class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    private const ACTIONS_TRAIT = InteractsWithActions::class;

    private const SCHEMAS_TRAIT = InteractsWithSchemas::class;

    public Group $group;

    #[Validate('required|string|max:255')]
    public string $name = '';

    private GroupService $groupService;

    public function boot(GroupService $groupService): void
    {
        $this->groupService = $groupService;
    }

    public function mount(): void
    {
        $this->name = $this->group->name;

        $this->authorizeAccess();
    }

    public function updateGroup(): void
    {
        $this->validate();

        $this->groupService->update($this->group, [
            'name' => $this->name,
        ]);

        $this->group->refresh();

        Notification::make()
            ->title('Group updated successfully')
            ->success()
            ->send();
    }

    public function deleteGroupAction(): Action
    {
        return Action::make('deleteGroup')
            ->modalCloseButton(false)
            ->requiresConfirmation()
            ->databaseTransaction()
            ->modalHeading('Delete Group')
            ->modalDescription('Are you sure you want to delete this group? This action cannot be undone.')
            ->modalSubmitActionLabel('Yes, Delete')
            ->color('danger')
            ->icon(Heroicon::Trash)
            ->successNotificationTitle('Group deleted successfully')
            ->action(function (): void {
                $sectionId = $this->group->section_id;

                $this->groupService->delete($this->group);

                $this->redirectRoute('rdo.classes.view', ['section' => $sectionId, 'tab' => 'groups'], navigate: true);
            });
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();
        $isRDO = $user->profileable_type === Instructor::class && $user->profileable?->role === InstructorRole::RDO;

        if (! $isRDO) {
            abort_if($this->group->section->instructor_id !== $user->profileable->id, 403);
        }
    }
};
?>

@assets
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">
  {{ $this->group->name }} Settings
</x-slot>

<div class="relative min-h-screen" style="background: #F8FAFC">
  <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
    <div class="absolute -right-32 -top-32 h-125 w-125 rounded-full"
      style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
    <div class="absolute bottom-1/3 -left-24 h-100 w-100 rounded-full"
      style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
    </div>
  </div>

  <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
    <div class="mb-8">
      <div class="mb-5 flex items-center gap-2 text-sm" style="color: #94A3B8">
        <a href="{{ route('rdo.classes.view', ['section' => $this->group->section_id, 'tab' => 'groups']) }}"
          wire:navigate class="font-medium transition-colors hover:text-blue-500" style="color: #64748B">Back to
          Groups</a>
        <span style="color: #CBD5E1">/</span>
        <span style="color: #0F172A; font-weight: 600">Settings</span>
      </div>

      <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
            style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
            <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
            <span
              style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
              Group Settings
            </span>
          </div>
          <h1 class="leading-tight"
            style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
            {{ $this->group->name }}
          </h1>
          <p class="mt-2 text-sm" style="color: #64748B">
            Update the group name or delete the group from this settings page.
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3 lg:gap-6">
      <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-2xl border bg-white"
          style="border-color: #E2E8F0; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
          <div class="border-b px-5 py-4" style="border-color: #F1F5F9">
            <h2 class="text-sm font-semibold" style="color: #0F172A">Rename Group</h2>
            <p class="mt-0.5 text-xs" style="color: #94A3B8">Change the group name for the current section.</p>
          </div>

          <form wire:submit="updateGroup" class="space-y-5 p-5">
            <div>
              <label for="group-name" class="mb-2 block text-sm font-medium" style="color: #334155">Group Name</label>
              <input id="group-name" type="text" wire:model.live="name"
                class="w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                style="border-color: #D7E0EB; background: #FAFBFD; color: #0F172A"
                placeholder="Enter a new group name">

              @error('name')
                <p class="mt-2 text-sm" style="color: #DC2626">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all hover:-translate-y-px hover:shadow-md"
                style="background: linear-gradient(to right, #0052FF, #4D7CFF); color: white; box-shadow: 0 2px 8px rgba(0,82,255,0.28)">
                <x-heroicon-o-check class="h-4 w-4" />
                Save Changes
              </button>

              <a href="{{ route('rdo.classes.view', ['section' => $this->group->section_id, 'tab' => 'groups']) }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all hover:-translate-y-px hover:shadow-md"
                style="border-color: #E2E8F0; background: white; color: #475569; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                Cancel
              </a>
            </div>
          </form>
        </div>
      </div>

      <div class="lg:col-span-1">
        <div class="overflow-hidden rounded-2xl border bg-white"
          style="border-color: #FECACA; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
          <div class="border-b px-5 py-4" style="border-color: #FEE2E2; background: #FFF7F7">
            <h2 class="text-sm font-semibold" style="color: #991B1B">Danger Zone</h2>
            <p class="mt-0.5 text-xs" style="color: #B91C1C">Deleting the group removes the group and its related data.</p>
          </div>

          <div class="space-y-4 p-5">
            <p class="text-sm leading-6" style="color: #64748B">
              This action cannot be undone. Make sure you want to permanently remove this group before continuing.
            </p>

            <button type="button" wire:click="mountAction('deleteGroupAction')"
              class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all hover:-translate-y-px hover:shadow-md"
              style="background: linear-gradient(to right, #DC2626, #EF4444); color: white; box-shadow: 0 2px 8px rgba(220,38,38,0.28)">
              <x-heroicon-o-trash class="h-4 w-4" />
              Delete Group
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <x-filament-actions::modals />
</div>

