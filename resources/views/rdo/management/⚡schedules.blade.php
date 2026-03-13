<?php

use App\Models\Schedule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function schedules()
    {
        return Schedule::whereHas('section', function ($query) {
            $query->active();
        })
            ->with('section', 'group')
            ->get()
            ->map(function (Schedule $schedule) {
                // $schedule->setAttribute('date_human', $schedule->date?->diffForHumans());

                return $schedule;
            });
    }

    public function mount()
    {
        // dd($this->schedules);
    }

    public function render()
    {
        return $this->view()->title('Schedule Management');
    }
};
?>

<div class="min-h-screen relative" style="background: #F8FAFC">

    {{-- Ambient background glows --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">

        <div x-data="{
            schedules: @js($this->schedules),
            calendarInstance: null,
            normalizeDate(value) {
                if (!value) {
                    return null;
                }
        
                return String(value).slice(0, 10);
            },
            normalizeTime(value, fallback) {
                return String(value ?? fallback).slice(0, 8);
            },
            mapEvents() {
                return this.schedules
                    .map((schedule, index) => {
                        const dateOnly = this.normalizeDate(schedule.date);
        
                        if (!dateOnly) {
                            return null;
                        }
        
                        const startTime = this.normalizeTime(schedule.start_time, '08:00:00');
                        const endTime = this.normalizeTime(schedule.end_time, '09:00:00');
        
                        return {
                            id: String(schedule.id ?? index),
                            title: `${schedule.section?.name ?? 'No section assigned'} - ${schedule.group?.name ?? 'No group assigned'}`,
                            start: `${dateOnly}T${startTime}`,
                            end: `${dateOnly}T${endTime}`,
                        };
                    })
                    .filter(Boolean);
            },
            removeToolbarTooltips() {
                if (!this.$refs.calendar) {
                    return;
                }
        
                this.$refs.calendar
                    .querySelectorAll('.ec-toolbar .ec-button[title]')
                    .forEach((button) => button.removeAttribute('title'));
            },
            initCalendar() {
                const calendarEl = this.$refs.calendar;
        
                if (!calendarEl || typeof EventCalendar === 'undefined') {
                    return;
                }
        
                if (this.calendarInstance) {
                    EventCalendar.destroy(this.calendarInstance);
                    this.calendarInstance = null;
                }
        
                this.calendarInstance = EventCalendar.create(calendarEl, {
                    view: 'dayGridMonth',
                    events: this.mapEvents(),
                    height: 'auto',
                    firstDay: 1,
                    locale: 'en-PH',
                    dayMaxEvents: true,
                    weekNumbers: true,
                    moreLinkContent: (arg) => ({ html: `<span>+${arg.num} more</span>` }),
                    buttonText: (text) => ({
                        ...text,
                        today: 'Today',
                        dayGridMonth: 'Month',
                        timeGridWeek: 'Week',
                        timeGridDay: 'Day',
                    }),
                    eventBackgroundColor: '#0052FF',
                    eventTextColor: '#FFFFFF',
                    editable: false,
                    eventStartEditable: false,
                    eventDurationEditable: false,
                    eventTimeFormat: {
                        hour: 'numeric',
                        minute: '2-digit',
                    },
                    datesSet: () => this.removeToolbarTooltips(),
                    viewDidMount: () => this.removeToolbarTooltips(),
                    headerToolbar: {
                        start: 'title',
                        center: '',
                        end: 'today prev,next dayGridMonth,timeGridWeek,timeGridDay, listMonth',
                    },
                });
        
                this.$nextTick(() => this.removeToolbarTooltips());
            },
        }" x-init="initCalendar()">

            {{-- ── Header ──────────────────────────── --}}
            <div class="mb-8 sm:mb-10">
                <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                    style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                    <span
                        style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                        Schedule Management
                    </span>
                </div>

                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                    <div>
                        <h1 class="leading-tight"
                            style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                            Schedules<span
                                style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
                        </h1>
                        <p class="mt-2 text-sm" style="color: #64748B">
                            View all scheduled group activities in month, week, or day mode.
                        </p>
                    </div>
                </div>
            </div>

            <div wire:ignore x-ref="calendar"
                class="schedule-calendar rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"></div>
        </div>
    </div>
</div>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.4.2/dist/event-calendar.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.4.2/dist/event-calendar.min.js"></script>
@endassets

<style>
    .schedule-calendar .ec {
        --ec-border-color: #e2e8f0;
        --ec-button-bg-color: #f8fafc;
        --ec-button-border-color: #cbd5e1;
        --ec-button-text-color: #334155;
        --ec-button-active-bg-color: #0052ff;
        --ec-button-active-border-color: #0052ff;
        --ec-button-active-text-color: #ffffff;
        --ec-button-hover-bg-color: #e2e8f0;
        --ec-button-hover-border-color: #cbd5e1;
        --ec-today-bg-color: #eef4ff;
        --ec-event-bg-color: #0052ff;
        --ec-event-border-color: #0052ff;
        --ec-event-text-color: #ffffff;
        --ec-more-link-bg-color: #dbeafe;
        --ec-more-link-text-color: #1d4ed8;
        --ec-now-indicator-color: #f43f5e;
    }

    .schedule-calendar .ec-toolbar {
        margin-bottom: 0.9rem;
        gap: 0.5rem;
    }

    .schedule-calendar .ec-button {
        border-radius: 0.65rem;
        font-weight: 600;
    }

    .schedule-calendar .ec-day-head,
    .schedule-calendar .ec-time,
    .schedule-calendar .ec-title {
        color: #0f172a;
    }

    .schedule-calendar .ec-event {
        border-radius: 0.55rem;
        padding: 2px 6px;
        box-shadow: 0 4px 14px rgba(0, 82, 255, 0.18);
    }
</style>
