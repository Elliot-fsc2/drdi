<?php

use App\Enums\ProposalStatus;
use App\Models\Proposal;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Group Proposals')]
    class extends Component implements HasActions, HasSchemas
    {
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

            if (! $section) {
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

            if (! $group) {
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
                ->color('info')
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

                    if (! $group) {
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
                ->visible(fn (): bool => $this->group() !== null);
        }

        public function viewProposalAction(): Action
        {
            return Action::make('viewProposal')
                ->modalHeading('Proposal Details')
                ->modalWidth('lg')
                ->form(function (array $arguments) {
                    $proposal = Proposal::find($arguments['proposal']);
                    $isPending = $proposal?->status === ProposalStatus::PENDING->value;

                    if ($isPending) {
                        return [
                            TextInput::make('title')
                                ->label('Proposal Title')
                                ->required()
                                ->maxLength(255),

                            Textarea::make('description')
                                ->label('Description')
                                ->required()
                                ->rows(6),

                            Textarea::make('feedback')
                                ->label('Instructor Feedback')
                                ->disabled()
                                ->visible(filled($proposal?->feedback)),
                        ];
                    }

                    return [
                        \Filament\Forms\Components\Placeholder::make('title')
                            ->label('Proposal Title')
                            ->content($proposal?->title),

                        \Filament\Forms\Components\Placeholder::make('description')
                            ->label('Description')
                            ->content($proposal?->description),

                        \Filament\Forms\Components\Placeholder::make('feedback')
                            ->label('Instructor Feedback')
                            ->content(function () use ($proposal) {
                                $feedback = $proposal?->feedback ?? 'No feedback provided.';
                                $isRejected = $proposal?->status === ProposalStatus::REJECTED->value;

                                $classes = $isRejected
                                    ? 'p-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm italic mt-1'
                                    : 'p-3 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-sm italic mt-1';

                                return new \Illuminate\Support\HtmlString('<div class="'.$classes.'">'.nl2br(e($feedback)).'</div>');
                            })
                            ->visible(filled($proposal?->feedback)),
                    ];
                })
                ->fillForm(function (array $arguments): array {
                    $proposal = Proposal::find($arguments['proposal']);

                    return [
                        'title' => $proposal?->title,
                        'description' => $proposal?->description,
                        'feedback' => $proposal?->feedback,
                    ];
                })
                ->modalSubmitAction(function ($action, array $arguments) {
                    $proposal = Proposal::find($arguments['proposal']);
                    if ($proposal && $proposal->status !== ProposalStatus::PENDING->value) {
                        return $action->hidden();
                    }

                    return $action->label('Save Changes');
                })
                ->successNotificationTitle('Proposal updated successfully')
                ->action(function (array $arguments, array $data): void {
                    $proposal = Proposal::find($arguments['proposal']);

                    if ($proposal && $proposal->status === ProposalStatus::PENDING->value) {
                        $proposal->update([
                            'title' => $data['title'],
                            'description' => $data['description'],
                        ]);

                        unset($this->proposals);
                    }
                });
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
                        {{ $this->createProposalAction }}
                    @endif
                </div>
            </div>
        </div>

        @if($this->group() && $this->group()->finalTitle)
            <div class="mb-6 bg-linear-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4 sm:p-5 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-1">
                        <h2 class="text-sm font-bold text-green-800 uppercase tracking-widest mb-1">Final Approved Title</h2>
                        <h3 class="text-lg sm:text-xl font-bold text-slate-900 leading-tight">{{ $this->group()->finalTitle->title }}</h3>
                        <p class="text-sm text-slate-600 mt-2 line-clamp-3">{{ $this->group()->finalTitle->description }}</p>
                    </div>
                </div>
            </div>
        @endif

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
            <div class="space-y-3 sm:space-y-4">
                @foreach($this->proposals as $proposal)
                    <div
                        class="bg-white border border-slate-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden group flex flex-col sm:flex-row cursor-pointer"
                        wire:key="{{ $proposal->id }}"
                        wire:click="mountAction('viewProposal', { proposal: '{{ $proposal->id }}' })">
                        
                        <!-- Left Status Bar -->
                        <div class="w-full sm:w-1.5 h-1.5 sm:h-auto {{ 
                            $proposal->status === \App\Enums\ProposalStatus::APPROVED->value ? 'bg-green-500' : 
                            ($proposal->status === \App\Enums\ProposalStatus::REJECTED->value ? 'bg-red-500' : 'bg-yellow-500') 
                        }}"></div>
                        
                        <div class="p-3 sm:p-4 lg:p-5 flex-1 flex flex-col lg:grid lg:grid-cols-12 gap-3 lg:gap-4 lg:items-center min-w-0">
                            
                            <!-- Title & Status Column (4 columns wide on lg) -->
                            <div class="lg:col-span-5 flex flex-col gap-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-slate-900 group-hover:text-blue-700 transition-colors text-base sm:text-lg truncate flex-1" title="{{ $proposal->title }}">
                                        {{ $proposal->title }}
                                    </h3>
                                    @if($this->group() && $proposal->id === $this->group()->final_title_id)
                                        <span class="inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium text-green-700 bg-green-50 ring-1 ring-inset ring-green-600/20">
                                            <x-heroicon-s-star class="h-3 w-3" />
                                            Final
                                        </span>
                                    @else
                                        <span class="inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $this->getStatusBadgeClass($proposal->status) }}">
                                            {{ $this->getStatusLabel($proposal->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Description Column (4 columns wide on lg) -->
                            <div class="lg:col-span-5 text-sm text-slate-600 line-clamp-2 lg:line-clamp-1">
                                {{ $proposal->description }}
                            </div>
                            
                            <!-- Meta Info Column (3 columns wide on lg) -->
                            <div class="lg:col-span-2 flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-2 text-xs text-slate-500 whitespace-nowrap pt-3 lg:pt-0 border-t lg:border-0 border-slate-100">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-s-user class="h-3.5 w-3.5" />
                                    <span class="truncate max-w-[120px]">{{ $proposal->submittedBy->full_name ?? 'Unknown Student' }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-s-calendar class="h-3.5 w-3.5" />
                                    <span>{{ $proposal->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    <x-filament-actions::modals />
</div>