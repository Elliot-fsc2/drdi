<?php

use App\Models\Proposal;
use App\Enums\ProposalStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Group Proposals')]
    class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public $user;

    public function mount()
    {
        $this->user = auth()->user()->load(
            'profileable.sections.program',
            'profileable.sections.semester'
        );
    }

    #[Computed]
    public function section()
    {
        return $this->user->profileable->sections()
            ->active()
            ->first();
    }

    #[Computed]
    public function group()
    {
        $section = $this->section();

        if (!$section) {
            return null;
        }

        return $this->user->profileable->groups()
            ->with('members', 'section')
            ->firstWhere('section_id', $section->id);
    }

    #[Computed]
    public function proposals()
    {
        $group = $this->group();

        if (!$group) {
            return collect();
        }

        return Proposal::where('group_id', $group->id)
            ->with('submittedBy')
            ->latest()
            ->get();
    }

    public function createProposalAction(): Action
    {
        return Action::make('createProposal')
            ->modalWidth('lg')
            ->color('success')
            ->modalCloseButton(false)
            ->label('Propose Title')
            ->icon('heroicon-o-plus')
            ->form([
                TextInput::make('title')
                    ->label('Proposal Title')
                    ->placeholder('Enter the title of your proposal')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Provide a brief description of your proposal')
                    ->required()
                    ->rows(4),
            ])
            ->successNotificationTitle('Proposal submitted successfully')
            ->action(function (array $data): void {
                $group = $this->group();

                if (!$group) {
                    return;
                }

                Proposal::create([
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'group_id' => $group->id,
                    'submitted_by' => $this->user->profileable->id,
                    'status' => ProposalStatus::PENDING->value,
                ]);

                unset($this->proposals);
            })
            ->visible(fn(): bool => $this->group() !== null);
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            ProposalStatus::APPROVED->value => 'text-green-700 bg-green-50 ring-green-600/20',
            ProposalStatus::PENDING->value => 'text-yellow-700 bg-yellow-50 ring-yellow-600/20',
            ProposalStatus::REJECTED->value => 'text-red-700 bg-red-50 ring-red-600/10',
            default => 'text-gray-700 bg-gray-50 ring-gray-600/20',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            ProposalStatus::APPROVED->value => 'Approved',
            ProposalStatus::REJECTED->value => 'Rejected',
            ProposalStatus::PENDING->value => 'Pending',
            default => ucfirst($status),
        };
    }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-3 sm:p-4 lg:p-6 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <div
            class="flex flex-col md:flex-row md:items-end justify-between mb-4 sm:mb-6 border-b border-slate-200 pb-3 sm:pb-4 gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Group Proposals</h1>
                @if($this->group())
                    <p class="text-slate-500 text-xs sm:text-sm font-medium mt-1 uppercase tracking-wider">
                        {{ $this->group()->name }} • {{ $this->section()->name }}
                    </p>
                @else
                    <p class="text-slate-500 text-xs sm:text-sm font-medium mt-1 uppercase tracking-wider">
                        No active group
                    </p>
                @endif
            </div>

            <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                <div class="w-full sm:w-auto">
                    @if($this->group())
                        {{ ($this->createProposalAction)(['class' => 'bg-blue-500 hover:bg-blue-600 focus:ring-blue-500 focus:ring-offset-blue-200 text-white w-full sm:w-auto']) }}
                    @endif
                </div>
            </div>
        </div>

        @if(!$this->group())
            <div class="bg-white border border-slate-200 rounded-lg p-12 sm:p-20 text-center">
                <x-heroicon-o-user-group class="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-500 text-sm sm:text-base">No active group assigned.</p>
                <p class="text-slate-400 text-xs sm:text-sm mt-2">You must be part of a group to propose a title.</p>
            </div>
        @elseif(count($this->proposals) === 0)
            <div class="bg-white border border-slate-200 rounded-lg p-12 sm:p-20 text-center">
                <x-heroicon-o-document-text class="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-slate-300 mb-4" />
                <p class="text-slate-500 text-sm sm:text-base">No titles proposed yet.</p>
                <p class="text-slate-400 text-xs sm:text-sm mt-2">Create your first proposal to get started.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                @foreach($this->proposals as $proposal)
                    <div
                        class="bg-white border border-slate-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden group flex flex-col h-full">
                        <div
                            class="p-3 sm:p-4 border-b border-slate-200 bg-linear-to-r from-blue-50 via-white to-blue-50 group-hover:from-blue-100 group-hover:to-blue-50 transition-colors flex-1 flex flex-col">
                            <div class="flex justify-between items-start mb-2">
                                <div
                                    class="font-bold text-slate-900 group-hover:text-blue-700 transition-colors text-sm sm:text-base line-clamp-2">
                                    {{ $proposal->title }}
                                </div>
                            </div>
                            <div class="text-xs sm:text-sm text-slate-600 mt-1 flex-1 line-clamp-3">
                                {{ $proposal->description }}
                            </div>
                            <div class="mt-3">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $this->getStatusBadgeClass($proposal->status) }}">
                                    {{ $this->getStatusLabel($proposal->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-3 sm:p-4 bg-white">
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-s-user class="h-4 w-4" />
                                    <span class="truncate">{{ $proposal->submittedBy->full_name ?? 'Unknown Student' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-s-calendar class="h-4 w-4" />
                                    <span>{{ $proposal->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @if($proposal->feedback)
                                <div class="mt-3 pt-3 border-t border-slate-100">
                                    <p class="text-xs font-medium text-slate-700 mb-1">Feedback:</p>
                                    <p class="text-xs text-slate-500 italic line-clamp-2">"{{ $proposal->feedback }}"</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <x-filament-actions::modals />
</div>