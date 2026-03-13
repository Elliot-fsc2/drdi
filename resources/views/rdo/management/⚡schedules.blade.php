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
            dayMaxEvents: true,
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
            },
            headerToolbar: {
                start: 'title',
                center: '',
                end: 'today prev,next dayGridMonth,timeGridWeek,timeGridDay',
            },
        });
    },
}" x-init="initCalendar()">
    <div class="mb-4">
        <h1 class="text-xl font-semibold text-slate-800">Schedule Calendar</h1>
        <p class="mt-1 text-sm text-slate-500">View all scheduled group activities in month, week, or day mode.</p>
    </div>

    <div wire:ignore x-ref="calendar" class="rounded-lg border border-slate-200 bg-white p-3"></div>
</div>

@assets
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.4.2/dist/event-calendar.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@event-calendar/build@5.4.2/dist/event-calendar.min.js"></script>
@endassets
