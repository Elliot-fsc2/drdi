<?php

use App\Enums\ProposalStatus;
use App\Models\Group;
use App\Models\Proposal;
use App\Models\Section;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Group $group;

    public Section $section;

    public function mount()
    {
        abort_if($this->group->section->instructor_id !== auth()->user()->profileable->id, 403);
        abort_if($this->group->section_id !== $this->section->id, 403);
    }

    #[Computed]
    public function proposal()
    {
        return Proposal::where('group_id', $this->group->id)
            ->with('submittedBy')
            ->get()
            ->map(function ($proposal) {
                return [
                    'id' => $proposal->id,
                    'title' => $proposal->title,
                    'description' => $proposal->description,
                    'status' => $proposal->status,
                    'submitted_date' => $proposal->created_at->format('M d, Y'),
                    'submitted_by' => $proposal->submittedBy ? trim($proposal->submittedBy->first_name.' '.$proposal->submittedBy->last_name) : 'Unknown',
                    'is_final' => $this->group->final_title_id === $proposal->id,
                ];
            });
    }

    public function viewProposalAction(): Action
    {
        return Action::make('viewProposal')
            ->modalAutofocus(false)
            ->modalWidth('2xl')
            ->modalCloseButton(true)
            ->modalHeading(fn (array $arguments) => $arguments['title'] ?? 'Research Proposal')
            ->modalSubmitAction(false)
            ->modalCloseButton(false)
            ->form(function (array $arguments) {
                $statusValue = strtolower($arguments['status'] ?? 'pending');
                $badgeColor = match ($statusValue) {
                    'approved' => 'success',
                    'pending', 'under review' => 'warning',
                    'rejected' => 'danger',
                    default => 'gray',
                };
                $badgeLabel = match ($statusValue) {
                    'approved' => 'Approved',
                    'pending' => 'Pending',
                    'rejected' => 'Rejected',
                    default => ucfirst($statusValue),
                };

                return [
                    Grid::make(3)
                        ->schema([
                            Placeholder::make('submitted_date')
                                ->label('Date Submitted')
                                ->content($arguments['submitted_date'] ?? ''),
                            Placeholder::make('submitted_by')
                                ->label('Submitted By')
                                ->content(new \Illuminate\Support\HtmlString('<span class="font-bold">'.e($arguments['submitted_by'] ?? 'Unknown').'</span>')),
                            Placeholder::make('status')
                                ->label('Status')
                                ->content(new \Illuminate\Support\HtmlString('<span class="text-xs font-semibold px-2 py-1 rounded text-'.$badgeColor.'-700 bg-'.$badgeColor.'-100">'.$badgeLabel.'</span>')),
                        ]),
                    Placeholder::make('description')
                        ->label('Description / Abstract')
                        ->content(new \Illuminate\Support\HtmlString('<div class="prose max-w-none text-slate-600">'.e($arguments['description'] ?? '').'</div>')),
                    Placeholder::make('feedback')
                        ->label('Instructor Feedback')
                        ->content(function () use ($arguments) {
                            $statusValue = strtolower($arguments['status'] ?? '');
                            $isRejected = $statusValue === 'rejected';
                            $feedback = $arguments['feedback'] ?? 'No feedback provided.';

                            $classes = $isRejected
                                ? 'p-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm italic mt-1'
                                : 'p-3 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-sm italic mt-1';

                            return new \Illuminate\Support\HtmlString('<div class="'.$classes.'">'.nl2br(e($feedback)).'</div>');
                        })
                        ->visible(fn () => filled($arguments['feedback'] ?? null)),
                ];
            })
            ->extraModalFooterActions(function (array $arguments) {
                $statusValue = $arguments['status'] ?? '';
                $statusSlug = $statusValue instanceof ProposalStatus ? $statusValue->value : strtolower($statusValue);

                $actions = [
                    Action::make('editTitleAction')
                        ->label('Edit')
                        ->icon(Heroicon::PencilSquare)
                        ->color('gray')
                        ->outlined()
                        ->action(function () use ($arguments) {
                            $this->replaceMountedAction('editTitleAction', [
                                'id' => $arguments['id'] ?? null,
                                'title' => $arguments['title'] ?? null,
                            ]);
                        }),
                ];

                if ($statusSlug !== 'approved') {
                    $actions[] = Action::make('approveTitleAction')
                        ->label('Approve')
                        ->color('success')
                        ->action(function () use ($arguments) {
                            $this->replaceMountedAction('approveTitleAction', ['id' => $arguments['id'] ?? null]);
                        });
                }

                if ($statusSlug === 'approved' && $this->group->final_title_id !== ($arguments['id'] ?? null)) {
                    $actions[] = Action::make('setFinalTitleAction')
                        ->label('Set as Final Title')
                        ->color('primary')
                        ->icon(Heroicon::Star)
                        ->action(function () use ($arguments) {
                            $this->replaceMountedAction('setFinalTitleAction', ['id' => $arguments['id'] ?? null]);
                        });
                }

                if ($statusSlug === 'pending' || $statusSlug === 'under review') {
                    $actions[] = Action::make('rejectAction')
                        ->label('Reject')
                        ->color('danger')
                        ->icon(Heroicon::XMark)
                        ->action(function () use ($arguments) {
                            $this->replaceMountedAction('rejectAction', ['id' => $arguments['id'] ?? null]);
                        });
                }

                return $actions;
            });
    }

    public function rejectAction(): Action
    {
        return Action::make('rejectAction')
            ->modalHeading('Reject Title?')
            ->modalCloseButton(false)
            ->modalDescription('Are you sure you want to reject this proposal? Please provide a reason below.')
            ->color('danger')
            ->form([
                \Filament\Forms\Components\Textarea::make('feedback')
                    ->label('Feedback / Reason for Rejection')
                    ->placeholder('Please explain why this title is being rejected to help the students.')
                    ->required()
                    ->rows(3),
            ])
            ->successNotificationTitle('Proposal Rejected')
            ->action(function ($arguments, array $data) {
                Proposal::find($arguments['id'])->update([
                    'status' => ProposalStatus::REJECTED,
                    'feedback' => $data['feedback'] ?? null,
                ]);
            });
    }

    // Keep existing actions for nested calls if needed, or inline them
    public function editTitleAction(): Action
    {
        return Action::make('editTitle')
            ->modalWidth('2xl')
            ->modalCloseButton(false)
            ->fillForm(function ($arguments) {
                return [
                    'title' => $arguments['title'] ?? null,
                ];
            })
            ->label('Edit Title')
            ->icon(Heroicon::PencilSquare)
            ->modalHeading('Edit Research Title')
            ->form([
                TextInput::make('title')
                    ->label('Research Title')
                    ->required()
                    ->maxLength(255),
            ])
            ->successNotificationTitle('Title updated successfully')
            ->action(function (array $data, array $arguments): void {
                $proposalId = $arguments['id'] ?? null;

                if ($proposalId) {
                    Proposal::query()->where('id', $proposalId)->update([
                        'title' => $data['title'],
                    ]);
                }
            });
    }

    public function approveTitleAction(): Action
    {
        return Action::make('approveTitle')
            ->modalCloseButton(false)
            ->modalHeading('Approve Research Title')
            ->modalDescription('Are you sure you want to approve this research title? This action will notify the group.')
            ->modalSubmitActionLabel('Yes, Approve')
            ->color('success')
            ->icon(Heroicon::CheckCircle)
            ->form([
                \Filament\Forms\Components\Textarea::make('feedback')
                    ->label('Feedback / Remarks (Optional)')
                    ->placeholder('Add any optional remarks or feedback for the students.')
                    ->rows(3),
            ])
            ->successNotificationTitle('Title approved successfully')
            ->action(function ($arguments, array $data): void {

                $proposalId = $arguments['id'] ?? null;
                Proposal::query()->where('id', $proposalId)->update([
                    'status' => ProposalStatus::APPROVED,
                    'feedback' => $data['feedback'] ?? null,
                ]);
            });
    }

    public function setFinalTitleAction(): Action
    {
        return Action::make('setFinalTitle')
            ->requiresConfirmation()
            ->modalCloseButton(false)
            ->modalHeading('Set as Final Title')
            ->modalDescription('Are you sure you want to set this proposal as the group\'s final research title?')
            ->modalSubmitActionLabel('Yes, Set as Final')
            ->color('primary')
            ->icon(Heroicon::Star)
            ->successNotificationTitle('Final title set successfully')
            ->action(function ($arguments): void {
                $proposalId = $arguments['id'] ?? null;

                if ($proposalId) {
                    $this->group->update([
                        'final_title_id' => $proposalId,
                    ]);
                }
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
        <div wire:click="mountAction('viewProposalAction', @js(['id' => $proposalItem['id'], 'title' => $proposalItem['title'], 'description' => $proposalItem['description'], 'status' => strtolower($proposalItem['status'] instanceof \App\Enums\ProposalStatus ? $proposalItem['status']->value : $proposalItem['status']), 'submitted_date' => $proposalItem['submitted_date'], 'submitted_by' => $proposalItem['submitted_by'], 'feedback' => \App\Models\Proposal::find($proposalItem['id'])?->feedback]))" 
             class="bg-white border border-slate-200 rounded-lg p-5 flex flex-col gap-3 shadow-sm hover:shadow-md hover:border-primary-500 cursor-pointer transition-all">
          @php
            $status = $proposalItem['status'];
            $statusValue = $status instanceof \App\Enums\ProposalStatus ? $status->value : strtolower($status);
            
            $badgeColor = match($statusValue) {
                'approved' => 'success',
                'pending', 'under review' => 'warning',
                'rejected' => 'danger',
                default => 'gray',
            };
            
            $badgeLabel = match($statusValue) {
                'approved' => 'Approved',
                'pending' => 'Pending',
                'rejected' => 'Rejected',
                default => ucfirst($statusValue),
            };
          @endphp
          
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <h4 class="text-base font-bold text-slate-900 leading-tight group-hover:text-primary-600 transition-colors">{{ $proposalItem['title'] }}</h4>
                @if($proposalItem['is_final'])
                   <x-filament::badge color="primary" size="sm" icon="heroicon-m-star">
                      Final
                   </x-filament::badge>
                @endif
              </div>
              <p class="text-xs text-slate-500 mt-1">Submitted by <span class="font-bold">{{ $proposalItem['submitted_by'] }}</span> on {{ $proposalItem['submitted_date'] }}</p>
            </div>
            <x-filament::badge :color="$badgeColor" class="flex-shrink-0">
              {{ $badgeLabel }}
            </x-filament::badge>
          </div>

          <p class="text-sm text-slate-600 leading-relaxed line-clamp-2">{{ $proposalItem['description'] }}</p>
        </div>
      @endforeach
    </div>
  @endif
  <x-filament-actions::modals />
</div>