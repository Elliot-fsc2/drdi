<?php

use App\Enums\PresentationStatus;
use App\Enums\PresentationType;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\Section;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Presentation Details')] class extends Component
{
    public Section $section;

    public Group $group;

    public Schedule $schedule;

    public string $status;

    public function mount(Section $section, Group $group, Schedule $schedule): void
    {
        abort_if($section->instructor_id !== auth()->user()->profileable->id, 403);
        abort_if($group->id !== $schedule->group_id || $section->id !== $schedule->section_id, 404);

        $this->section = $section;
        $this->group = $group;
        $this->schedule = $schedule->loadMissing(['group', 'section.program', 'section.semester']);
        $this->status = $this->schedule->status?->value ?? PresentationStatus::SCHEDULED->value;
    }

    #[Computed]
    public function panelists(): array
    {
        $panelists = collect($this->schedule->panelists ?? []);

        if ($panelists->isEmpty()) {
            return [];
        }

        $isCurrentShape = $panelists
            ->keys()
            ->every(fn ($key): bool => is_string($key) || is_int($key) || ctype_digit((string) $key))
            && $panelists->every(fn (mixed $role): bool => $role instanceof \BackedEnum ? true : \App\Enums\PanelistRole::tryFrom((string) $role) !== null);

        if (! $isCurrentShape) {
            $instructors = Instructor::query()
                ->whereIn('id', $panelists->values()->all())
                ->get()
                ->keyBy('id');

            return $panelists
                ->map(fn (mixed $panelistId): array => [
                    'name' => $instructors->get((int) $panelistId)?->full_name ?? 'Instructor #'.$panelistId,
                    'role' => 'Member',
                ])
                ->values()
                ->all();
        }

        $instructors = Instructor::query()
            ->whereIn('id', $panelists->keys()->all())
            ->get()
            ->keyBy('id');

        return $panelists
            ->map(function (string $role, string|int $id) use ($instructors): array {
                $instructor = $instructors->get((int) $id);

                return [
                    'name' => $instructor?->full_name ?? 'Instructor #'.$id,
                    'role' => ucwords(str_replace('_', ' ', $role)),
                ];
            })
            ->values()
            ->all();
    }

    public function updateStatus(): void
    {
        $validated = $this->validate([
            'status' => ['required', Rule::enum(PresentationStatus::class)],
        ]);

        $newStatus = PresentationStatus::from($validated['status']);

        if ($newStatus === $this->schedule->status) {
            Notification::make()
                ->title('No changes made')
                ->body('The presentation status is already set to that value.')
                ->warning()
                ->send();

            return;
        }

        $this->schedule->update([
            'status' => $newStatus->value,
        ]);

        $this->schedule->refresh();
        $this->status = $newStatus->value;

        Notification::make()
            ->title('Presentation status updated successfully')
            ->success()
            ->send();
    }

    #[Computed]
    public function statusMeta(): array
    {
        return match ($this->schedule->status ?? PresentationStatus::SCHEDULED) {
            PresentationStatus::PASSED => [
                'label' => 'Passed',
                'bg' => 'rgba(5,150,105,0.08)',
                'border' => 'rgba(5,150,105,0.2)',
                'color' => '#059669',
                'icon' => 'heroicon-o-check-badge',
            ],
            PresentationStatus::REDEFENSE => [
                'label' => 'Re-defense',
                'bg' => 'rgba(217,119,6,0.08)',
                'border' => 'rgba(217,119,6,0.2)',
                'color' => '#D97706',
                'icon' => 'heroicon-o-arrow-path',
            ],
            PresentationStatus::FAILED => [
                'label' => 'Failed',
                'bg' => 'rgba(239,68,68,0.08)',
                'border' => 'rgba(239,68,68,0.2)',
                'color' => '#DC2626',
                'icon' => 'heroicon-o-x-circle',
            ],
            default => [
                'label' => 'Scheduled',
                'bg' => 'rgba(100,116,139,0.07)',
                'border' => 'rgba(100,116,139,0.15)',
                'color' => '#64748B',
                'icon' => 'heroicon-o-calendar-days',
            ],
        };
    }

    public function statusOptions(): array
    {
        return collect(PresentationStatus::cases())
            ->mapWithKeys(fn (PresentationStatus $status): array => [$status->value => $this->statusLabel($status)])
            ->all();
    }

    public function statusLabel(PresentationStatus $status): string
    {
        return match ($status) {
            PresentationStatus::PASSED => 'Passed',
            PresentationStatus::REDEFENSE => 'Re-defense',
            PresentationStatus::FAILED => 'Failed',
            PresentationStatus::SCHEDULED => 'Scheduled',
        };
    }

    public function typeLabel(): string
    {
        return $this->schedule->presentation_type?->getLabel() ?? 'Not set';
    }

    public function typeStyle(): array
    {
        return match ($this->schedule->presentation_type) {
            PresentationType::THESIS_A_PROPOSAL => ['accent' => '#0052FF', 'bg' => 'rgba(0,82,255,0.08)', 'border' => 'rgba(0,82,255,0.18)'],
            PresentationType::THESIS_A_ORAL => ['accent' => '#7C3AED', 'bg' => 'rgba(124,58,237,0.08)', 'border' => 'rgba(124,58,237,0.18)'],
            PresentationType::THESIS_A_MOCK => ['accent' => '#D97706', 'bg' => 'rgba(217,119,6,0.08)', 'border' => 'rgba(217,119,6,0.18)'],
            PresentationType::THESIS_A_FINAL => ['accent' => '#059669', 'bg' => 'rgba(5,150,105,0.08)', 'border' => 'rgba(5,150,105,0.18)'],
            PresentationType::THESIS_B_ORAL => ['accent' => '#4F46E5', 'bg' => 'rgba(79,70,229,0.08)', 'border' => 'rgba(79,70,229,0.18)'],
            PresentationType::THESIS_B_MOCK => ['accent' => '#DB2777', 'bg' => 'rgba(219,39,119,0.08)', 'border' => 'rgba(219,39,119,0.18)'],
            PresentationType::THESIS_B_FINAL => ['accent' => '#0D9488', 'bg' => 'rgba(13,148,136,0.08)', 'border' => 'rgba(13,148,136,0.18)'],
            default => ['accent' => '#64748B', 'bg' => 'rgba(100,116,139,0.08)', 'border' => 'rgba(100,116,139,0.18)'],
        };
    }
};
?>

@assets
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">{{ $this->group->name }} Presentation Details</x-slot>

<div class="min-h-screen bg-slate-50">
    <div class="relative mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">
        <div class="mb-6 flex flex-wrap items-center gap-2 text-sm text-slate-500">
            <a href="{{ route('instructor.classes.view', ['section' => $this->section->id]) }}" wire:navigate
                class="inline-flex items-center gap-1.5 font-medium text-slate-500 transition hover:text-blue-600">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Section
            </a>
            <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />
            <a href="{{ route('instructor.classes.view', ['section' => $this->section->id]) }}" wire:navigate
                class="font-medium transition hover:text-blue-600">
                {{ $this->section->name }}
            </a>
            <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />
            <a href="{{ route('instructor.classes.group.view', ['section' => $this->section->id, 'group' => $this->group->id]) }}"
                wire:navigate class="font-medium transition hover:text-blue-600">
                {{ $this->group->name }}
            </a>
            <x-heroicon-o-chevron-right class="h-4 w-4 text-slate-300" />
            <span class="font-semibold text-slate-900">Presentation Details</span>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-6 py-6 sm:px-8">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <div class="mb-3 inline-flex items-center gap-2 rounded-full border px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em]"
                                    style="background: {{ $this->typeStyle()['bg'] }}; border-color: {{ $this->typeStyle()['border'] }}; color: {{ $this->typeStyle()['accent'] }}">
                                    <x-heroicon-o-academic-cap class="h-3.5 w-3.5" />
                                    {{ $this->typeLabel() }}
                                </div>
                                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                                    {{ $this->group->name }}
                                </h1>
                                <p class="mt-2 text-sm text-slate-500">
                                    Full presentation schedule and status controls for this group.
                                </p>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 text-xs font-semibold"
                                style="background: {{ $this->statusMeta()['bg'] }}; border-color: {{ $this->statusMeta()['border'] }}; color: {{ $this->statusMeta()['color'] }}">
                                <x-dynamic-component :component="$this->statusMeta()['icon']" class="h-4 w-4" />
                                {{ $this->statusMeta()['label'] }}
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-5 sm:grid-cols-2 sm:px-8 lg:grid-cols-4">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-calendar-days class="h-4 w-4 text-slate-400" />
                                Date
                            </div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ Carbon::parse($this->schedule->date)->format('F j, Y') }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-clock class="h-4 w-4 text-slate-400" />
                                Time
                            </div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ Carbon::parse($this->schedule->start_time)->format('h:i A') }} - {{ Carbon::parse($this->schedule->end_time)->format('h:i A') }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-map-pin class="h-4 w-4 text-slate-400" />
                                Venue
                            </div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $this->schedule->venue }}
                            </p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-building-library class="h-4 w-4 text-slate-400" />
                                Semester
                            </div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ $this->section->semester->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 px-6 py-6 sm:px-8 lg:grid-cols-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-user-group class="h-4 w-4 text-slate-400" />
                                Group
                            </div>
                            <p class="text-base font-semibold text-slate-900">{{ $this->group->name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $this->section->name }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-document-text class="h-4 w-4 text-slate-400" />
                                Presentation Type
                            </div>
                            <p class="text-base font-semibold text-slate-900">{{ $this->typeLabel() }}</p>
                            <p class="mt-1 text-sm text-slate-500">Managed by the presentation service.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <x-heroicon-o-check-badge class="h-4 w-4 text-slate-400" />
                                Current Status
                            </div>
                            <p class="text-base font-semibold text-slate-900">{{ $this->statusMeta()['label'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">Update it from the form on the right.</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 sm:px-8">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Panelists</h2>
                            <p class="text-sm text-slate-500">Instructor assignments and their roles.</p>
                        </div>
                        <x-heroicon-o-users class="h-6 w-6 text-slate-300" />
                    </div>

                    <div class="p-6 sm:p-8">
                        @if (count($this->panelists()) > 0)
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($this->panelists() as $panelist)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                                <x-heroicon-o-user class="h-5 w-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-900">{{ $panelist['name'] }}</p>
                                                <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ $panelist['role'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center">
                                <x-heroicon-o-user-group class="mx-auto h-10 w-10 text-slate-300" />
                                <p class="mt-4 text-sm font-semibold text-slate-700">No panelists assigned yet</p>
                                <p class="mt-1 text-sm text-slate-500">Add panelists when you update the schedule status or edit the schedule.</p>
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <x-heroicon-o-adjustments-horizontal class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">Update Status</h2>
                            <p class="text-sm text-slate-500">Set the group's presentation result.</p>
                        </div>
                    </div>

                    <form wire:submit.prevent="updateStatus" class="space-y-4">
                        <div>
                            <label for="status" class="mb-2 block text-sm font-medium text-slate-700">Presentation status</label>
                            <select id="status" wire:model.live="status"
                                class="block w-full rounded-2xl border-slate-300 bg-white py-3 pl-4 pr-10 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                @foreach ($this->statusOptions() as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-linear-to-r from-blue-600 to-blue-500 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:from-blue-700 hover:to-blue-600">
                            <x-heroicon-o-check class="h-4 w-4" />
                            Save Status
                        </button>
                    </form>
                </section>

                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-semibold text-slate-900">Schedule Snapshot</h2>
                    <div class="mt-4 space-y-3 text-sm text-slate-600">
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="inline-flex items-center gap-2 font-medium text-slate-500">
                                <x-heroicon-o-building-office-2 class="h-4 w-4 text-slate-400" /> Section
                            </span>
                            <span class="font-semibold text-slate-900">{{ $this->section->name }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="inline-flex items-center gap-2 font-medium text-slate-500">
                                <x-heroicon-o-users class="h-4 w-4 text-slate-400" /> Members
                            </span>
                            <span class="font-semibold text-slate-900">{{ $this->group->members()->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                            <span class="inline-flex items-center gap-2 font-medium text-slate-500">
                                <x-heroicon-o-calendar class="h-4 w-4 text-slate-400" /> Venue date
                            </span>
                            <span class="font-semibold text-slate-900">{{ Carbon::parse($this->schedule->date)->format('M j, Y') }}</span>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
