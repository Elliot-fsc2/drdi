<?php

use App\Models\Instructor;
use App\Models\Schedule;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function schedules()
    {
        $profile = auth()->user()->profileable;

        if ($profile instanceof Instructor) {
            $departmentId = $profile->department_id;
        } elseif ($profile instanceof Student) {
            $departmentId = $profile->program?->department_id;
        } else {
            $departmentId = null;
        }

        if (! $departmentId) {
            return collect();
        }

        return Schedule::query()
            ->whereHas('section', function ($query) use ($departmentId) {
                $query->whereHas('semester', function ($q) {
                    $q->active();
                })->whereHas('program', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            })
            ->with('section.program', 'group')
            ->orderBy('date')
            ->orderBy('start_time')
            ->get()
            ->map(function (Schedule $schedule) {
                $schedule->setAttribute('calendar_date', $schedule->getRawOriginal('date'));
                $schedule->setAttribute('calendar_start_time', $schedule->getRawOriginal('start_time'));
                $schedule->setAttribute('calendar_end_time', $schedule->getRawOriginal('end_time'));
                $schedule->setAttribute('presentation_type_label', $schedule->presentation_type?->getLabel() ?? 'Uncategorized');
                $schedule->setAttribute('venue_label', $schedule->venue ?: 'No venue');
                $schedule->setAttribute('section_name', $schedule->section?->name ?? 'No section');
                $schedule->setAttribute('program_name', $schedule->section?->program?->name ?? '');

                return $schedule;
            });
    }
};
?>

<div>
    @php
        $profile = auth()->user()->profileable;
        $deptName = $profile instanceof \App\Models\Instructor
            ? $profile->department?->name
            : ($profile instanceof \App\Models\Student ? $profile->program?->department?->name : null);
        $deptHasSchedules = $this->schedules->isNotEmpty();
    @endphp

    <div class="mb-5 flex items-center justify-between gap-3">
        @if ($deptName)
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full" style="background: #0052FF"></span>
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em]"
                    style="font-family: 'JetBrains Mono', monospace; color: #0052FF">
                    {{ $deptName }} Department
                </span>
            </div>
        @else
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(148,163,184,0.25); background: rgba(148,163,184,0.08)">
                <span class="h-1.5 w-1.5 rounded-full" style="background: #94A3B8"></span>
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em]"
                    style="font-family: 'JetBrains Mono', monospace; color: #64748B">
                    No department linked
                </span>
            </div>
        @endif
    </div>

    @if (! $deptHasSchedules)
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100">
                <x-heroicon-o-calendar-days class="h-6 w-6 text-slate-400" />
            </div>
            <p class="text-sm font-semibold text-slate-700">No scheduled presentations yet</p>
            <p class="mx-auto mt-1 max-w-sm text-xs text-slate-400">
                @if ($deptName)
                    There are no upcoming presentation schedules for your department right now.
                @else
                    Your account isn't linked to a department yet, so no schedules can be shown. Contact your administrator.
                @endif
            </p>
        </div>
    @else
        <div x-data="departmentCalendar(@js($this->schedules))" @keydown.escape.window="closeDetails()">
            {{-- Stat chips --}}
            <div class="mb-5 flex flex-wrap items-center gap-3">
                <template x-for="stat in stats()" :key="stat.label">
                    <div class="flex items-center gap-2.5 rounded-full border bg-white py-1.5 pl-2 pr-4 shadow-sm"
                        :style="'border-color:' + stat.color + '55'">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold text-white"
                            :style="'background:' + stat.color" x-text="stat.count"></span>
                        <span class="text-xs font-medium" style="color: #475569" x-text="stat.label"></span>
                    </div>
                </template>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Toolbar --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-4 py-3 sm:px-5">
                    <div class="flex flex-wrap items-center gap-2" x-show="viewMode === 'calendar'">
                        <template x-for="opt in typeFilters()" :key="opt.value">
                            <button @click="toggleTypeFilter(opt.value)"
                                :class="activeTypeFilters.includes(opt.value) ? 'text-white shadow-sm' : 'bg-slate-50 text-slate-500 hover:bg-slate-100'"
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-semibold transition"
                                :style="activeTypeFilters.includes(opt.value)
                                    ? 'background:' + opt.color + '; border-color:' + opt.color
                                    : 'border-color:#E2E8F0'">
                                <span class="h-1.5 w-1.5 rounded-full"
                                    :style="activeTypeFilters.includes(opt.value) ? 'background:#fff' : 'background:' + opt.color"></span>
                                <span x-text="opt.label"></span>
                            </button>
                        </template>
                        <button @click="resetTypeFilters()"
                            class="rounded-full px-2.5 py-1 text-[11px] font-semibold text-slate-400 transition hover:text-blue-600"
                            x-show="activeTypeFilters.length !== typeFilters().length">
                            Reset
                        </button>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <template x-for="opt in [{ key: 'calendar', label: 'Calendar' }, { key: 'list', label: 'List' }]" :key="opt.key">
                            <button @click="switchViewMode(opt.key)"
                                :class="viewMode === opt.key ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="rounded-lg border px-3 py-1 text-xs font-semibold transition-colors"
                                x-text="opt.label"></button>
                        </template>
                    </div>
                </div>

                {{-- Calendar --}}
                <div class="p-2 sm:p-4" x-show="viewMode === 'calendar'">
                    <div x-ref="calendar" class="department-calendar"></div>
                </div>

                {{-- List --}}
                <div x-show="viewMode === 'list'" x-cloak>
                    <div class="p-4 sm:p-5">
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-slate-700">Upcoming Schedules</p>
                            <p class="mt-0.5 text-xs text-slate-400">All presentations in your department.</p>
                        </div>
                        <div class="space-y-3">
                            <template x-for="ev in sortedListEvents()" :key="ev.id">
                                <button @click="openEventDetails(ev)"
                                    class="flex w-full items-start gap-4 rounded-xl border border-slate-200 bg-white p-3.5 text-left transition hover:border-slate-300 hover:shadow-sm">
                                    <span class="mt-0.5 inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white"
                                        :style="'background:' + ev.extendedProps.color" x-text="ev.extendedProps.day"></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-slate-800" x-text="ev.title"></span>
                                        <span class="mt-0.5 block text-xs text-slate-400"
                                            x-text="ev.extendedProps.timeLabel + ' · ' + ev.extendedProps.sectionName + (ev.extendedProps.programName ? ' · ' + ev.extendedProps.programName : '')"></span>
                                    </span>
                                    <span class="inline-flex shrink-0 items-center rounded-full px-2.5 py-1 text-[10px] font-bold"
                                        :style="'background:' + ev.extendedProps.statusMeta.bg + '; color:' + ev.extendedProps.statusMeta.color + '; border:1px solid ' + ev.extendedProps.statusMeta.border"
                                        x-text="ev.extendedProps.statusMeta.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Details modal --}}
            <div x-show="selectedOpen" x-cloak x-transition.opacity
                class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
                @click.self="closeDetails()">
                <div x-show="selectedOpen" x-cloak x-transition
                    class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
                    <template x-if="selectedEvent">
                        <div>
                            <div class="relative overflow-hidden px-6 py-5 sm:px-8">
                                <div class="pointer-events-none absolute -right-10 -top-12 h-40 w-40 rounded-full opacity-20"
                                    :style="'background: radial-gradient(circle, ' + selectedEvent.extendedProps.color + ', transparent 70%)'"></div>
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="mb-2 inline-flex items-center rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-white"
                                            :style="'background:' + selectedEvent.extendedProps.color"
                                            x-text="selectedEvent.extendedProps.presentationType"></div>
                                        <h3 class="truncate text-lg font-bold text-slate-900" x-text="selectedEvent.extendedProps.groupName"></h3>
                                        <p class="text-sm text-slate-500"
                                            x-text="selectedEvent.extendedProps.sectionName + (selectedEvent.extendedProps.programName ? ' · ' + selectedEvent.extendedProps.programName : '')"></p>
                                    </div>
                                    <button @click="closeDetails()"
                                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700">
                                        <x-heroicon-o-x-mark class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                            <div class="space-y-3 px-6 pb-6 sm:px-8">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                        <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                            <x-heroicon-o-calendar-days class="h-3.5 w-3.5" /> Date
                                        </div>
                                        <p class="mt-1 text-sm font-semibold text-slate-800" x-text="selectedEvent.extendedProps.dateLabel"></p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                        <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                            <x-heroicon-o-clock class="h-3.5 w-3.5" /> Time
                                        </div>
                                        <p class="mt-1 text-sm font-semibold text-slate-800" x-text="selectedEvent.extendedProps.timeLabel"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm">
                                        <x-heroicon-o-map-pin class="h-4.5 w-4.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Venue</p>
                                        <p class="truncate text-sm font-semibold text-slate-800" x-text="selectedEvent.extendedProps.venue"></p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-[11px] font-semibold"
                                        :style="'background:' + selectedEvent.extendedProps.statusMeta.bg + '; color:' + selectedEvent.extendedProps.statusMeta.color + '; border:1px solid ' + selectedEvent.extendedProps.statusMeta.border"
                                        x-text="selectedEvent.extendedProps.statusMeta.label"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @endif
</div>

@assets
<style>
    .department-calendar {
        --fc-border-color: #e2e8f0;
        --fc-page-bg-color: #ffffff;
        --fc-neutral-bg-color: #f8fafc;
        --fc-today-bg-color: #eef4ff;
        --fc-button-bg-color: #ffffff;
        --fc-button-border-color: #e2e8f0;
        --fc-button-text-color: #475569;
        --fc-button-hover-bg-color: #f1f5f9;
        --fc-button-hover-border-color: #cbd5e1;
        --fc-button-active-bg-color: #0052ff;
        --fc-button-active-border-color: #0052ff;
        --fc-event-border-color: transparent;
        --fc-list-event-hover-bg-color: #f8fafc;
        --fc-now-indicator-color: #f43f5e;
        font-family: inherit;
    }

    .department-calendar .fc-toolbar-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }

    .department-calendar .fc-button {
        font-weight: 600;
        border-radius: 0.55rem;
        padding: 0.3rem 0.65rem;
        text-transform: capitalize;
    }

    .department-calendar .fc-button-primary:not(:disabled):focus,
    .department-calendar .fc-button-primary:not(:disabled):active:focus {
        box-shadow: none;
    }

    .department-calendar .fc-col-header-cell {
        padding: 0.45rem 0;
        background: #f8fafc;
        border-style: solid;
    }

    .department-calendar .fc-col-header-cell-cushion {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .department-calendar .fc-daygrid-day-number {
        color: #334155;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .department-calendar .fc-day-today .fc-daygrid-day-number {
        background: #0052ff;
        color: #ffffff;
        border-radius: 9999px;
        min-width: 1.6rem;
        text-align: center;
        margin: 3px 4px 0 0;
        padding: 1px 0;
    }

    .department-calendar .fc-event {
        border-radius: 0.45rem;
        border: none;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
        cursor: pointer;
        font-size: 0.72rem;
        font-weight: 600;
        padding: 1px 4px;
    }

    .department-calendar .fc-event:hover {
        filter: brightness(1.08);
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);
    }

    .department-calendar .fc-timegrid-slot-label-cushion {
        font-size: 0.7rem;
        color: #64748b;
    }

    .department-calendar .fc-list-day-cushion {
        background: #f8fafc;
        padding: 0.5rem 1rem;
    }

    .department-calendar .fc-list-day-cushion .fc-list-day-text,
    .department-calendar .fc-list-day-cushion .fc-list-day-side-text {
        font-weight: 700;
        color: #334155;
    }

    .department-calendar .fc-list-event-time {
        color: #64748b;
        font-size: 0.75rem;
    }

    .department-calendar .fc-list-event-title {
        font-weight: 600;
        color: #0f172a;
    }

    .department-calendar [title]:hover::before,
    .department-calendar [title]:hover::after {
        content: none !important;
    }

    .department-hover-tooltip {
        position: fixed;
        z-index: 9999;
        max-width: 320px;
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(15, 23, 42, 0.96);
        color: #f8fafc;
        font-size: 12px;
        line-height: 1.45;
        white-space: pre-line;
        pointer-events: none;
        box-shadow: 0 14px 34px rgba(2, 6, 23, 0.4);
        backdrop-filter: blur(4px);
    }

    [x-cloak] {
        display: none !important;
    }
</style>
@endassets
