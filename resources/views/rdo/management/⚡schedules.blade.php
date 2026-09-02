<?php

use App\Models\Instructor;
use App\Models\Schedule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::rdo.app')] class extends Component {
  #[Computed]
  public function schedules()
  {
    return Schedule::whereHas('section', function ($query) {
      $query->active();
    })
      ->with('section', 'group')
      ->orderBy('presentation_type')
      ->orderBy('venue')
      ->orderBy('date')
      ->orderBy('start_time')
      ->get()
      ->pipe(function ($schedules) {
        $allPanelistIds = $schedules->flatMap(fn(Schedule $s) => $s->panelists ?? [])->unique()->filter()->values();
        $instructors = $allPanelistIds->isNotEmpty() ? Instructor::whereIn('id', $allPanelistIds)->orderBy('last_name')->get()->keyBy('id') : collect();

        return $schedules->map(function (Schedule $schedule) use ($instructors) {
          $schedule->setAttribute('calendar_date', $schedule->getRawOriginal('date'));
          $schedule->setAttribute('calendar_start_time', $schedule->getRawOriginal('start_time'));
          $schedule->setAttribute('calendar_end_time', $schedule->getRawOriginal('end_time'));
          $schedule->setAttribute('presentation_type_label', $schedule->presentation_type?->getLabel() ?? 'Uncategorized');
          $schedule->setAttribute('venue_label', $schedule->venue ?: 'No venue');
          $schedule->setAttribute(
            'panelist_names',
            collect($schedule->panelists ?? [])
              ->map(fn($id) => $instructors->get($id)?->full_name)
              ->filter()
              ->join(', ') ?:
              null,
          );

          return $schedule;
        });
      });
  }

  public function render()
  {
    return $this->view()->title('Schedule Management');
  }
};
?>

@assets
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
  rel="stylesheet">
@endassets

<div class="min-h-screen" style="background: #F8FAFC">

  <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10"
    x-data="scheduleCalendarPage(@js($this->schedules))" @keydown.escape.window="closeDetails()">

    {{-- ── Header ──────────────────────────── --}}
    <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
          style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
          <span class="h-1.5 w-1.5 animate-pulse rounded-full" style="background: #0052FF"></span>
          <span class="text-[11px] font-semibold uppercase tracking-[0.14em]"
            style="font-family: 'JetBrains Mono', monospace; color: #0052FF">
            Schedule Management
          </span>
        </div>
        <h1 class="leading-tight" style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
          Schedules<span
            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
        </h1>
        <p class="mt-2 text-sm" style="color: #64748B">
          View all scheduled group activities in month, week, or day mode.
        </p>
      </div>
    </div>

    {{-- ── Summary stat chips ──────────────────────────── --}}
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

    {{-- ── Calendar / List panel ──────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

      {{-- Toolbar: legend filters + view toggle --}}
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

      {{-- Calendar body --}}
      <div class="p-2 sm:p-4" x-show="viewMode === 'calendar'">
        <div wire:ignore x-ref="calendar" class="schedule-calendar"></div>
      </div>

      {{-- List body --}}
      <div x-show="viewMode === 'list'" x-cloak>
        <div class="p-4 sm:p-5">
          <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-700">All Schedules</p>
              <p class="mt-0.5 text-xs text-slate-400">Grouped by presentation type, status, or venue.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <span class="mr-1 text-xs font-medium text-slate-400">Group by:</span>
              <template
                x-for="opt in [{key:'type',label:'Type'},{key:'status',label:'Status'},{key:'venue',label:'Venue'}]"
                :key="opt.key">
                <button @click="tableGroupBy = opt.key"
                  :class="tableGroupBy === opt.key ? 'bg-blue-600 text-white border-blue-600 shadow-sm' :
                                      'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                  class="rounded-lg border px-3 py-1 text-xs font-semibold transition-colors"
                  x-text="opt.label"></button>
              </template>
            </div>
          </div>

          <template x-if="schedules.length === 0">
            <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-10 text-center">
              <p class="font-semibold text-slate-600">No schedules found</p>
              <p class="mt-1 text-xs text-slate-400">No active semester schedules to display.</p>
            </div>
          </template>

          <template x-if="schedules.length > 0">
            <div class="space-y-5">
              <template x-for="[groupKey, items] in groupedSchedules()" :key="groupKey">
                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                  <div class="flex items-center gap-2 border-b border-slate-100 bg-slate-50/70 px-4 py-2.5">
                    <template x-if="tableGroupBy === 'type'">
                      <span class="h-2 w-2 shrink-0 rounded-full"
                        :style="'background:' + colorForType(groupKey).bg"></span>
                    </template>
                    <template x-if="tableGroupBy === 'status'">
                      <span class="h-2 w-2 shrink-0 rounded-full"
                        :style="'background:' + statusStyle(groupKey).color"></span>
                    </template>
                    <span class="text-xs font-semibold text-slate-700"
                      x-text="tableGroupBy === 'status' ? statusStyle(groupKey).label : groupKey"></span>
                    <span class="text-[10px] text-slate-400"
                      x-text="'(' + items.length + ' slot' + (items.length !== 1 ? 's' : '') + ')'"></span>
                  </div>
                  {{-- Desktop table --}}
                  <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full">
                      <thead>
                        <tr class="border-b border-slate-100">
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Section / Group</th>
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Venue</th>
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Date</th>
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Time</th>
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Type</th>
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Status</th>
                          <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-slate-400">Panelists</th>
                        </tr>
                      </thead>
                      <tbody>
                        <template x-for="sched in items" :key="sched.id">
                          <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-4 py-3">
                              <p class="text-xs font-semibold text-slate-800" x-text="sched.section?.name ?? '—'"></p>
                              <p class="text-[11px] text-slate-400" x-text="sched.group?.name ?? '—'"></p>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600" x-text="sched.venue_label ?? '—'"></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600" x-text="formatDate(sched.calendar_date)"></td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600"
                              x-text="formatTimeLabel(sched.calendar_start_time) + ' – ' + formatTimeLabel(sched.calendar_end_time)"></td>
                            <td class="px-4 py-3">
                              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :style="'background:' + colorForType(sched.presentation_type_label).bg + '22; color:' + colorForType(sched.presentation_type_label).bg + '; border:1px solid ' + colorForType(sched.presentation_type_label).bg + '55'"
                                x-text="sched.presentation_type_label ?? '—'"></span>
                            </td>
                            <td class="px-4 py-3">
                              <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                                :style="'background:' + statusStyle(sched.status).bg + '; color:' + statusStyle(sched.status).color + '; border:1px solid ' + statusStyle(sched.status).border"
                                x-text="statusStyle(sched.status).label"></span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500" x-text="sched.panelist_names ?? '—'"></td>
                          </tr>
                        </template>
                      </tbody>
                    </table>
                  </div>
                  {{-- Mobile cards --}}
                  <div class="divide-y divide-slate-50 sm:hidden">
                    <template x-for="sched in items" :key="'m' + sched.id">
                      <div class="space-y-2 px-4 py-3">
                        <div class="flex items-start justify-between gap-2">
                          <div>
                            <p class="text-xs font-semibold text-slate-800" x-text="sched.section?.name ?? '—'"></p>
                            <p class="text-[11px] text-slate-400" x-text="sched.group?.name ?? '—'"></p>
                          </div>
                          <span class="inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                            :style="'background:' + statusStyle(sched.status).bg + '; color:' + statusStyle(sched.status).color + '; border:1px solid ' + statusStyle(sched.status).border"
                            x-text="statusStyle(sched.status).label"></span>
                        </div>
                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                          <span x-text="formatDate(sched.calendar_date)"></span>
                          <span x-text="formatTimeLabel(sched.calendar_start_time) + ' – ' + formatTimeLabel(sched.calendar_end_time)"></span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                          <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold"
                            :style="'background:' + colorForType(sched.presentation_type_label).bg + '22; color:' + colorForType(sched.presentation_type_label).bg + '; border:1px solid ' + colorForType(sched.presentation_type_label).bg + '55'"
                            x-text="sched.presentation_type_label ?? '—'"></span>
                          <span class="text-xs text-slate-500" x-text="sched.venue_label ?? '—'"></span>
                        </div>
                        <template x-if="sched.panelist_names">
                          <p class="text-[11px] text-slate-400" x-text="sched.panelist_names"></p>
                        </template>
                      </div>
                    </template>
                  </div>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>
    </div>

    {{-- ── Event details modal ──────────────────────────── --}}
    <div x-show="selectedOpen" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0" x-cloak
      class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
      @click.self="closeDetails()">
      <div x-show="selectedOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl">
        <template x-if="selectedEvent">
          <div>
            {{-- Modal header --}}
            <div class="relative overflow-hidden px-6 py-5 sm:px-8">
              <div class="pointer-events-none absolute -right-10 -top-12 h-40 w-40 rounded-full opacity-20"
                :style="'background: radial-gradient(circle, ' + selectedEvent.extendedProps.color + ', transparent 70%)'"></div>
              <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                  <div class="mb-2 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-white"
                    :style="'background:' + selectedEvent.extendedProps.color"
                    x-text="selectedEvent.extendedProps.presentationType"></div>
                  <h3 class="truncate text-lg font-bold text-slate-900" x-text="selectedEvent.extendedProps.groupName"></h3>
                  <p class="text-sm text-slate-500" x-text="selectedEvent.extendedProps.sectionName"></p>
                </div>
                <button @click="closeDetails()"
                  class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700">
                  <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
              </div>
            </div>

            {{-- Modal body --}}
            <div class="space-y-3 px-6 pb-6 sm:px-8">
              <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                  <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                    <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
                    Date
                  </div>
                  <p class="mt-1 text-sm font-semibold text-slate-800" x-text="selectedEvent.extendedProps.dateLabel"></p>
                </div>
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                  <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                    <x-heroicon-o-clock class="h-3.5 w-3.5" />
                    Time
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
              <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                <div class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                  <x-heroicon-o-users class="h-3.5 w-3.5" />
                  Panelists
                </div>
                <p class="mt-1.5 text-sm text-slate-700"
                  x-text="selectedEvent.extendedProps.panelists ?? 'No panelists assigned'"></p>
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-semibold"
                  :style="'background:' + selectedEvent.extendedProps.statusMeta.bg + '; color:' + selectedEvent.extendedProps.statusMeta.color + '; border:1px solid ' + selectedEvent.extendedProps.statusMeta.border"
                  x-text="selectedEvent.extendedProps.statusMeta.label"></span>
                <template x-if="selectedEvent.extendedProps.panelFee">
                  <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-600">
                    <x-heroicon-o-currency-dollar class="h-3.5 w-3.5" />
                    <span x-text="'₱' + Number(selectedEvent.extendedProps.panelFee).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                  </span>
                </template>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

<style>
  /* ── FullCalendar theming ─────────────────────── */
  .schedule-calendar {
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

  .schedule-calendar .fc-toolbar-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
  }

  .schedule-calendar .fc-button {
    font-weight: 600;
    border-radius: 0.55rem;
    padding: 0.3rem 0.65rem;
    text-transform: capitalize;
  }

  .schedule-calendar .fc-button-primary:not(:disabled):focus,
  .schedule-calendar .fc-button-primary:not(:disabled):active:focus {
    box-shadow: none;
  }

  .schedule-calendar .fc-col-header-cell {
    padding: 0.45rem 0;
    background: #f8fafc;
    border-style: solid;
  }

  .schedule-calendar .fc-col-header-cell-cushion {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #64748b;
  }

  .schedule-calendar .fc-daygrid-day-number,
  .schedule-calendar .fc-daygrid-week-number {
    color: #334155;
    font-size: 0.8rem;
    font-weight: 600;
  }

  .schedule-calendar .fc-day-today .fc-daygrid-day-number {
    background: #0052ff;
    color: #ffffff;
    border-radius: 9999px;
    min-width: 1.6rem;
    text-align: center;
    margin: 3px 4px 0 0;
    padding: 1px 0;
  }

  .schedule-calendar .fc-daygrid-day-frame {
    min-height: 6.5rem;
  }

  .schedule-calendar .fc-event {
    border-radius: 0.45rem;
    border: none;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.12);
    cursor: pointer;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 1px 4px;
  }

  .schedule-calendar .fc-event:hover {
    filter: brightness(1.08);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.2);
  }

  .schedule-calendar .fc-timegrid-slot-label-cushion {
    font-size: 0.7rem;
    color: #64748b;
  }

  .schedule-calendar .fc-timegrid-now-indicator-line,
  .schedule-calendar .fc-timegrid-now-indicator-arrow {
    border-color: #f43f5e;
  }

  .schedule-calendar .fc-list-day-cushion {
    background: #f8fafc;
    padding: 0.5rem 1rem;
  }

  .schedule-calendar .fc-list-day-cushion .fc-list-day-text,
  .schedule-calendar .fc-list-day-cushion .fc-list-day-side-text {
    font-weight: 700;
    color: #334155;
  }

  .schedule-calendar .fc-list-event-time {
    color: #64748b;
    font-size: 0.75rem;
  }

  .schedule-calendar .fc-list-event-title {
    font-weight: 600;
    color: #0f172a;
  }

  .schedule-calendar .fc-popover {
    border-radius: 0.75rem;
    border-color: #e2e8f0;
    box-shadow: 0 12px 30px rgba(2, 6, 23, 0.12);
    overflow: hidden;
  }

  .schedule-calendar .fc-popover-header {
    background: #f8fafc;
    color: #0f172a;
    font-size: 0.8rem;
  }

  /* The app layout renders custom tooltips for [title] — the calendar shows its
     own rich tooltip on events, so suppress the layout one inside the calendar. */
  .schedule-calendar [title]:hover::before,
  .schedule-calendar [title]:hover::after {
    content: none !important;
  }

  @media (max-width: 640px) {
    .schedule-calendar .fc-toolbar.fc-header-toolbar {
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .schedule-calendar .fc-toolbar-title {
      order: -1;
      width: 100%;
    }

    .schedule-calendar .fc-daygrid-day-frame {
      min-height: 4.5rem;
    }
  }

  .schedule-hover-tooltip {
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

