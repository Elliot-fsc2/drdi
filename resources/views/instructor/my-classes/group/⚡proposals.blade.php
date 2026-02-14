<?php

use App\Models\Group;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Proposal;

new class extends Component implements HasActions, HasSchemas {
  use InteractsWithActions;
  use InteractsWithSchemas;

  public Group $group;
  public Section $section;

  public function mount()
  {
    abort_if($this->group->section->instructor_id !== auth()->user()->profileable->id, 403);
    abort_if($this->group->section_id !== $this->section->id, 403);
  }

  // Temporary mock data - will be replaced with actual database models
  #[Computed]
  public function proposal()
  {
    return Proposal::where('group_id', $this->group->id)
      ->get()
      ->map(function ($proposal) {
        return [
          'title' => $proposal->title,
          'description' => $proposal->description,
          'status' => $proposal->status,
          'submitted_date' => $proposal->created_at->format('M d, Y'),
        ];
      });
  }

  public function editTitleAction(): Action
  {
    return Action::make('editTitle')
      ->modalWidth('2xl')
      ->modalCloseButton(false)
      ->label('Edit Title')
      ->icon(Heroicon::PencilSquare)
      ->modalHeading('Edit Research Title')
      ->form([
        TextInput::make('title')
          ->label('Research Title')
          ->required()
          ->maxLength(255)
          ->default($this->proposal['title']),
        Textarea::make('description')
          ->label('Description')
          ->required()
          ->rows(4)
          ->maxLength(500)
          ->default($this->proposal['description']),
      ])
      ->successNotificationTitle('Title updated successfully')
      ->action(function (array $data): void {
        // TODO: Update the actual proposal record in database
        \Filament\Notifications\Notification::make()
          ->title('Title updated successfully')
          ->success()
          ->send();
      });
  }

  public function approveTitleAction(): Action
  {
    return Action::make('approveTitle')
      ->requiresConfirmation()
      ->modalCloseButton(false)
      ->modalHeading('Approve Research Title')
      ->modalDescription('Are you sure you want to approve this research title? This action will notify the group.')
      ->modalSubmitActionLabel('Yes, Approve')
      ->color('success')
      ->icon(Heroicon::CheckCircle)
      ->successNotificationTitle('Title approved successfully')
      ->action(function (): void {
        // TODO: Update proposal status in database
        \Filament\Notifications\Notification::make()
          ->title('Title approved successfully')
          ->body('The group has been notified.')
          ->success()
          ->send();
      });
  }
};
?>

<div class="p-4">

  @if($this->proposal->isEmpty())
    <div class="bg-slate-50 border border-slate-200 rounded-lg p-8 text-center">
      <div class="text-slate-400 mb-2">
        <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <h4 class="text-lg font-semibold text-slate-900 mb-1">No Proposals Yet</h4>
      <p class="text-slate-600 text-sm">This group hasn't submitted any research title proposals.</p>
    </div>
  @else
    <div class="space-y-4">
      @foreach($this->proposal as $proposalItem)
        <div class="bg-white border border-slate-200 rounded-lg p-6">
          <div class="mb-4">
            <div class="flex items-start justify-between mb-2 gap-4">
              <h4 class="text-lg font-bold text-slate-900 flex-1">{{ $proposalItem['title'] }}</h4>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span class="px-3 py-1 text-xs font-medium rounded
                          {{ $proposalItem['status'] === 'Approved' ? 'bg-green-100 text-green-700' : '' }}
                          {{ $proposalItem['status'] === 'Under Review' ? 'bg-orange-100 text-orange-700' : '' }}
                          {{ $proposalItem['status'] === 'Rejected' ? 'bg-red-100 text-red-700' : '' }}">
                  {{ $proposalItem['status'] }}
                </span>
              </div>
            </div>
            <p class="text-sm text-slate-500">Submitted: {{ $proposalItem['submitted_date'] }}</p>
          </div>

          <div class="mb-4">
            <h5 class="text-sm font-semibold text-slate-700 mb-2">Description</h5>
            <p class="text-slate-600 leading-relaxed">{{ $proposalItem['description'] }}</p>
          </div>

          <div class="flex gap-2 pt-4 border-t border-slate-100">
            <x-filament::button wire:click="mountAction('editTitle')" color="gray" outlined size="sm">
              Edit Title
            </x-filament::button>
            @if($proposalItem['status'] !== 'Approved')
              <x-filament::button wire:click="mountAction('approveTitle')" color="success" size="sm">
                Approve
              </x-filament::button>
            @endif
            @if($proposalItem['status'] === 'Under Review')
              <x-filament::button color="danger" outlined size="sm">
                Reject
              </x-filament::button>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>