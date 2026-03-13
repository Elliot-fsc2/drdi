<?php

use Livewire\Component;
use App\Models\Consultation;
use Filament\Actions\Action;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

new #[Title('Group Consultations')] class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public $user;

    public function mount()
    {
        $this->user = auth()->user()->load('profileable.sections.program', 'profileable.sections.semester');
    }

    #[Computed]
    public function section()
    {
        return $this->user->profileable->sections()->active()->first();
    }

    #[Computed]
    public function group()
    {
        $section = $this->section();

        if (!$section) {
            return null;
        }

        return $this->user->profileable->groups()->with('members', 'section')->firstWhere('section_id', $section->id);
    }

    #[Computed]
    public function consultations()
    {
        $group = $this->group();

        if (!$group) {
            return collect();
        }

        return Consultation::where('group_id', $group->id)->with('instructor')->orderBy('scheduled_at', 'asc')->get();
    }

    public function viewConsultationAction(): Action
    {
        return Action::make('viewConsultation')
            ->modalHeading('Consultation Details')
            ->modalCloseButton(false)
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->form(function (array $arguments) {
                $consultation = Consultation::find($arguments['consultation']);

                return [
                    Placeholder::make('scheduled_at')->label('Scheduled Time')->content(fn() => $consultation?->scheduled_at ? $consultation->scheduled_at->format('M d, Y') : 'To Be Determined'),

                    Placeholder::make('instructor')->label('Instructor')->content(
                        fn() => collect([$consultation?->instructor?->first_name, $consultation?->instructor?->last_name])
                            ->filter()
                            ->join(' ') ?:
                        'Instructor #' . $consultation?->instructor_id,
                    ),

                    Placeholder::make('type')->label('Type')->content(fn() => ucfirst($consultation?->type ?? 'N/A')),

                    Placeholder::make('status')->label('Status')->content(fn() => ucfirst($consultation?->status ?? 'N/A')),

                    Placeholder::make('remarks')
                        ->label('Instructor Remarks')
                        ->content(function () use ($consultation) {
                            $remarks = $consultation?->remarks ?? 'No remarks provided.';

                            return new \Illuminate\Support\HtmlString('<div class="p-3 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-sm italic mt-1">' . nl2br(e($remarks)) . '</div>');
                        })
                        ->visible(filled($consultation?->remarks)),
                ];
            });
    }

    public function getStatusColorClass(string $status): string
    {
        return match ($status) {
            'approved', 'completed' => 'text-emerald-700 bg-emerald-50 ring-emerald-600/20',
            'pending', 'scheduled' => 'text-amber-700 bg-amber-50 ring-amber-600/20',
            'rejected' => 'text-rose-700 bg-rose-50 ring-rose-600/10',
            default => 'text-slate-700 bg-slate-50 ring-slate-600/20',
        };
    }
};
?>

@assets
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-4 sm:p-6 lg:p-8 min-h-screen bg-slate-50">
    <div class="max-w-4xl mx-auto">

        <div class="mb-6 pb-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">Consultations</h1>
                @if ($this->group())
                    <p class="text-slate-500 text-sm mt-1">
                        {{ $this->group()->name }} <span class="mx-1">&middot;</span> {{ $this->section()->name }}
                    </p>
                @else
                    <p class="text-slate-500 text-sm mt-1">No group assigned</p>
                @endif
            </div>
        </div>

        @if (!$this->group())
            <div class="border border-slate-200 bg-white rounded-xl p-12 text-center text-slate-500">
                You do not have an active group assigned.
            </div>
        @elseif(count($this->consultations) === 0)
            <div class="border border-slate-200 bg-white rounded-xl p-12 text-center text-slate-500">
                No consultations have been scheduled yet.
            </div>
        @else
            <div
                class="border border-slate-200 bg-white rounded-xl shadow-sm overflow-hidden divide-y divide-slate-100">
                @foreach ($this->consultations as $consultation)
                    <div class="group hover:bg-slate-50 transition-colors cursor-pointer p-4 sm:p-5 flex flex-col sm:flex-row gap-4 sm:items-center relative"
                        wire:key="{{ $consultation->id }}"
                        wire:click="mountAction('viewConsultation', { consultation: '{{ $consultation->id }}' })">

                        <div
                            class="absolute inset-y-0 left-0 w-1 {{ $consultation->status === 'completed' || $consultation->status === 'approved' ? 'bg-emerald-400' : 'bg-transparent group-hover:bg-slate-300' }} transition-colors">
                        </div>

                        <div class="sm:w-36 shrink-0">
                            @if ($consultation->scheduled_at)
                                <div class="text-xs font-bold tracking-wide">
                                    {{ $consultation->scheduled_at->format('M d, Y') }}</div>
                            @else
                                <span
                                    class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600">Unscheduled</span>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-base font-medium text-slate-900">
                                    {{ ucfirst($consultation->type) }} Session
                                </h3>
                                <span
                                    class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $this->getStatusColorClass($consultation->status) }}">
                                    {{ ucfirst($consultation->status) }}
                                </span>
                            </div>
                            <div class="text-sm text-slate-600 truncate">
                                {{ $consultation->remarks ?? 'No remarks provided.' }}
                            </div>
                        </div>

                        <div class="sm:text-right text-sm text-slate-500 hidden sm:block">
                            Instructor: {{ $consultation->instructor->full_name }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>

    <x-filament-actions::modals />
</div>
