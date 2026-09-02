import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';

const TYPE_PALETTE = {
    'Title Proposal': '#0052FF',
    'Oral Defense': '#7C3AED',
    'Mock Defense': '#D97706',
    'Final Defense': '#059669',
    'Thesis B - Oral Defense': '#4F46E5',
    'Thesis B - Mock Defense': '#DB2777',
    'Thesis B - Final Defense': '#0D9488',
};
const FALLBACK_COLOR = '#64748B';
const STATUS_STYLES = {
    passed: { bg: 'rgba(5,150,105,0.08)', border: 'rgba(5,150,105,0.2)', color: '#059669', label: 'Passed' },
    redefense: { bg: 'rgba(217,119,6,0.08)', border: 'rgba(217,119,6,0.2)', color: '#D97706', label: 'Re-defense' },
    failed: { bg: 'rgba(239,68,68,0.08)', border: 'rgba(239,68,68,0.25)', color: '#DC2626', label: 'Failed' },
    scheduled: { bg: 'rgba(100,116,139,0.07)', border: 'rgba(100,116,139,0.15)', color: '#64748B', label: 'Scheduled' },
};

function colorForType(type) {
    return TYPE_PALETTE[type] ?? FALLBACK_COLOR;
}
function statusStyle(status) {
    return STATUS_STYLES[status] ?? STATUS_STYLES.scheduled;
}
function normalizeDate(value) {
    if (!value) return null;
    return String(value).slice(0, 10);
}
function normalizeTime(value, fallback) {
    return String(value ?? fallback).slice(0, 8);
}
function formatTimeLabel(value) {
    if (!value) return '--:--';
    const [rawHour, rawMinute] = String(value).slice(0, 8).split(':');
    const hour = Number(rawHour);
    const minute = String(rawMinute ?? '00').padStart(2, '0');
    if (Number.isNaN(hour)) return String(value).slice(0, 5);
    const meridian = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 === 0 ? 12 : hour % 12;
    return `${displayHour}:${minute} ${meridian}`;
}
function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}
function formatDateLong(dateStr) {
    if (!dateStr) return '—';
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}
function dayOfMonth(dateStr) {
    if (!dateStr) return '';
    return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { day: 'numeric' });
}
function typeFilters() {
    return Object.entries(TYPE_PALETTE).map(([label, color]) => ({ label, value: label, color }));
}

document.addEventListener('alpine:init', () => {
    Alpine.data('scheduleCalendarPage', (schedules) => ({
        schedules: schedules ?? [],
        calendarInstance: null,
        hoverTooltipEl: null,
        viewMode: 'calendar',
        tableGroupBy: 'type',
        activeTypeFilters: [],
        currentCalendarView: null,
        selectedEvent: null,
        selectedOpen: false,

        isMobileCalendar() {
            return window.matchMedia('(max-width: 640px)').matches;
        },
        getInitialCalendarView() {
            return this.isMobileCalendar() ? 'listMonth' : 'dayGridMonth';
        },
        colorForType(type) {
            return colorForType(type);
        },
        statusStyle(status) {
            return statusStyle(status);
        },
        stats() {
            const counts = { scheduled: 0, passed: 0, redefense: 0, failed: 0 };
            this.schedules.forEach((s) => {
                const status = s.status ?? 'scheduled';
                if (counts[status] !== undefined) counts[status]++;
            });
            return [
                { label: 'Scheduled', count: counts.scheduled, color: statusStyle('scheduled').color },
                { label: 'Passed', count: counts.passed, color: statusStyle('passed').color },
                { label: 'Re-defense', count: counts.redefense, color: statusStyle('redefense').color },
                { label: 'Failed', count: counts.failed, color: statusStyle('failed').color },
                { label: 'Total', count: this.schedules.length, color: '#0052FF' },
            ];
        },
        typeFilters() {
            return typeFilters();
        },
        toggleTypeFilter(value) {
            if (this.activeTypeFilters.includes(value)) {
                this.activeTypeFilters = this.activeTypeFilters.filter((v) => v !== value);
            } else {
                this.activeTypeFilters = [...this.activeTypeFilters, value];
            }
            this.rerenderCalendar();
        },
        resetTypeFilters() {
            this.activeTypeFilters = this.typeFilters().map((f) => f.value);
            this.rerenderCalendar();
        },
        isTypeHidden(typeLabel) {
            return !this.activeTypeFilters.includes(typeLabel);
        },
        mapEvents() {
            return this.schedules
                .filter((schedule) => !this.isTypeHidden(schedule.presentation_type_label ?? 'Uncategorized'))
                .map((schedule, index) => {
                    const dateOnly = normalizeDate(schedule.calendar_date ?? schedule.date);
                    if (!dateOnly) return null;
                    const startTime = normalizeTime(schedule.calendar_start_time ?? schedule.start_time, '08:00:00');
                    const endTime = normalizeTime(schedule.calendar_end_time ?? schedule.end_time, '09:00:00');
                    const presentationType = schedule.presentation_type_label ?? 'Uncategorized';
                    const venue = schedule.venue_label ?? 'No venue';
                    const color = this.colorForType(presentationType);
                    return {
                        id: String(schedule.id ?? index),
                        title: `${schedule.group?.name ?? 'No group'} · ${venue}`,
                        start: `${dateOnly}T${startTime}`,
                        end: `${dateOnly}T${endTime}`,
                        backgroundColor: color,
                        borderColor: color,
                        textColor: '#FFFFFF',
                        extendedProps: {
                            presentationType,
                            venue,
                            sectionName: schedule.section?.name ?? 'No section assigned',
                            groupName: schedule.group?.name ?? 'No group assigned',
                            date: dateOnly,
                            dateLabel: formatDateLong(dateOnly),
                            startTime,
                            endTime,
                            timeLabel: `${formatTimeLabel(startTime)} – ${formatTimeLabel(endTime)}`,
                            panelists: schedule.panelist_names ?? null,
                            panelFee: schedule.panel_fee ?? null,
                            color,
                            statusMeta: this.statusStyle(schedule.status ?? 'scheduled'),
                            status: schedule.status ?? 'scheduled',
                        },
                    };
                })
                .filter(Boolean);
        },
        eventTooltip(event) {
            const props = event.extendedProps ?? {};
            return [
                props.presentationType,
                `Venue: ${props.venue}`,
                `Section: ${props.sectionName}`,
                `Group: ${props.groupName}`,
                `Time: ${props.timeLabel}`,
                `Status: ${props.statusMeta?.label ?? ''}`,
            ].join('\n');
        },
        ensureHoverTooltip() {
            if (this.hoverTooltipEl) return this.hoverTooltipEl;
            const el = document.createElement('div');
            el.className = 'schedule-hover-tooltip';
            el.style.display = 'none';
            document.body.appendChild(el);
            this.hoverTooltipEl = el;
            return el;
        },
        showHoverTooltip(info) {
            const tooltip = this.ensureHoverTooltip();
            tooltip.textContent = this.eventTooltip(info.event);
            tooltip.style.display = 'block';
            this.moveHoverTooltip(info.jsEvent);
        },
        moveHoverTooltip(event) {
            if (!this.hoverTooltipEl || !event) return;
            const offset = 16;
            const rect = this.hoverTooltipEl.getBoundingClientRect();
            let left = event.clientX + offset;
            let top = event.clientY + offset;
            if (left + rect.width > window.innerWidth - 8) left = Math.max(8, event.clientX - rect.width - offset);
            if (top + rect.height > window.innerHeight - 8) top = Math.max(8, event.clientY - rect.height - offset);
            this.hoverTooltipEl.style.left = `${left}px`;
            this.hoverTooltipEl.style.top = `${top}px`;
        },
        hideHoverTooltip() {
            if (this.hoverTooltipEl) this.hoverTooltipEl.style.display = 'none';
        },
        removeToolbarTooltips() {
            const el = this.$refs.calendar;
            if (!el) return;
            el.querySelectorAll('.fc-header-toolbar button[title]').forEach((button) => button.removeAttribute('title'));
        },
        destroyCalendar() {
            const calendarEl = this.$refs.calendar;
            if (!calendarEl) return;
            if (this.calendarInstance?.destroy) this.calendarInstance.destroy();
            this.calendarInstance = null;
            calendarEl.innerHTML = '';
        },
        calendarOptions(view = 'dayGridMonth', date = null) {
            const self = this;
            const options = {
                plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
                initialView: view,
                events: this.mapEvents(),
                height: 'auto',
                firstDay: 1,
                locale: 'en',
                dayMaxEvents: true,
                weekNumbers: true,
                nowIndicator: true,
                allDaySlot: false,
                eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
                slotMinTime: '06:00:00',
                slotMaxTime: '20:00:00',
                slotDuration: '00:30:00',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'today prev,next dayGridMonth,timeGridWeek,timeGridDay,listMonth',
                },
                buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day', list: 'List' },
                eventDidMount(info) {
                    info.el.setAttribute('title', self.eventTooltip(info.event));
                },
                eventMouseEnter(info) {
                    self.showHoverTooltip(info);
                },
                eventMouseLeave() {
                    self.hideHoverTooltip();
                },
                eventClick(info) {
                    self.openEventDetails(info.event);
                },
                dateClick(info) {
                    const isListOrMonth = ['dayGridMonth', 'listMonth', 'listWeek', 'listDay'].includes(info.view.type);
                    if (isListOrMonth && self.calendarInstance?.changeView) {
                        self.calendarInstance.changeView('timeGridDay', info.dateStr);
                    }
                },
            };
            if (date) options.initialDate = date;
            return options;
        },
        createCalendar(view = 'dayGridMonth', date = null) {
            const calendarEl = this.$refs.calendar;
            if (!calendarEl) return;
            this.destroyCalendar();
            this.calendarInstance = new Calendar(calendarEl, this.calendarOptions(view, date));
            this.calendarInstance.render();
            this.$nextTick(() => this.removeToolbarTooltips());
        },
        rerenderCalendar() {
            // Filter events in place — never rebuild the calendar instance,
            // otherwise the visible date/view resets (and toISOString() can
            // shift the date by a day in non-UTC timezones).
            if (this.viewMode !== 'calendar') return;
            if (this.calendarInstance) {
                this.calendarInstance.removeAllEvents();
                this.calendarInstance.addEventSource(this.mapEvents());
            }
        },
        syncCalendarView() {
            const nextView = this.getInitialCalendarView();
            if (this.currentCalendarView === nextView && this.calendarInstance) return;
            this.currentCalendarView = nextView;
            this.createCalendar(nextView);
        },
        switchViewMode(mode) {
            this.viewMode = mode;
            if (mode === 'calendar') {
                this.$nextTick(() => {
                    this.syncCalendarView();
                    this.calendarInstance?.updateSize?.();
                });
            }
        },
        openEventDetails(event) {
            this.selectedEvent = event;
            this.selectedOpen = true;
        },
        closeDetails() {
            this.selectedOpen = false;
            this.selectedEvent = null;
        },
        groupedSchedules() {
            const grouped = {};
            this.schedules.forEach((s) => {
                let key;
                if (this.tableGroupBy === 'type') key = s.presentation_type_label ?? 'Uncategorized';
                else if (this.tableGroupBy === 'status') key = s.status ?? 'scheduled';
                else key = s.venue_label ?? 'No venue';
                if (!grouped[key]) grouped[key] = [];
                grouped[key].push(s);
            });
            return Object.entries(grouped).sort((a, b) => a[0].localeCompare(b[0]));
        },
        init() {
            this.activeTypeFilters = this.typeFilters().map((f) => f.value);
            this.syncCalendarView();
            window.addEventListener('resize', () => {
                if (this.viewMode === 'calendar') this.syncCalendarView();
            });
            this.$watch('selectedOpen', (open) => {
                document.body.style.overflow = open ? 'hidden' : '';
            });
        },
    }));

    Alpine.data('departmentCalendar', (schedules) => ({
        schedules: schedules ?? [],
        calendarInstance: null,
        hoverTooltipEl: null,
        viewMode: 'calendar',
        activeTypeFilters: [],
        currentCalendarView: null,
        selectedEvent: null,
        selectedOpen: false,

        isMobileCalendar() {
            return window.matchMedia('(max-width: 640px)').matches;
        },
        getInitialCalendarView() {
            return this.isMobileCalendar() ? 'listMonth' : 'dayGridMonth';
        },
        colorForType(type) {
            return colorForType(type);
        },
        statusStyle(status) {
            return statusStyle(status);
        },
        stats() {
            const counts = { scheduled: 0, passed: 0, redefense: 0, failed: 0 };
            this.schedules.forEach((s) => {
                const status = s.status ?? 'scheduled';
                if (counts[status] !== undefined) counts[status]++;
            });
            return [
                { label: 'Scheduled', count: counts.scheduled, color: statusStyle('scheduled').color },
                { label: 'Passed', count: counts.passed, color: statusStyle('passed').color },
                { label: 'Re-defense', count: counts.redefense, color: statusStyle('redefense').color },
                { label: 'Failed', count: counts.failed, color: statusStyle('failed').color },
                { label: 'Total', count: this.schedules.length, color: '#0052FF' },
            ];
        },
        typeFilters() {
            return typeFilters();
        },
        toggleTypeFilter(value) {
            if (this.activeTypeFilters.includes(value)) {
                this.activeTypeFilters = this.activeTypeFilters.filter((v) => v !== value);
            } else {
                this.activeTypeFilters = [...this.activeTypeFilters, value];
            }
            this.rerenderCalendar();
        },
        resetTypeFilters() {
            this.activeTypeFilters = this.typeFilters().map((f) => f.value);
            this.rerenderCalendar();
        },
        mapEvents() {
            return this.schedules
                .filter((schedule) => this.activeTypeFilters.length === 0 || this.activeTypeFilters.includes(schedule.presentation_type_label ?? 'Uncategorized'))
                .map((schedule, index) => {
                    const dateOnly = normalizeDate(schedule.calendar_date ?? schedule.date);
                    if (!dateOnly) return null;
                    const startTime = normalizeTime(schedule.calendar_start_time ?? schedule.start_time, '08:00:00');
                    const endTime = normalizeTime(schedule.calendar_end_time ?? schedule.end_time, '09:00:00');
                    const presentationType = schedule.presentation_type_label ?? 'Uncategorized';
                    const color = this.colorForType(presentationType);
                    return {
                        id: String(schedule.id ?? index),
                        title: `${schedule.group?.name ?? 'Group'} — ${presentationType}`,
                        start: `${dateOnly}T${startTime}`,
                        end: `${dateOnly}T${endTime}`,
                        backgroundColor: color,
                        borderColor: color,
                        textColor: '#FFFFFF',
                        extendedProps: {
                            presentationType,
                            venue: schedule.venue_label ?? 'No venue',
                            groupName: schedule.group?.name ?? 'Group',
                            sectionName: schedule.section_name ?? 'No section',
                            programName: schedule.program_name ?? '',
                            dateLabel: formatDateLong(dateOnly),
                            day: dayOfMonth(dateOnly),
                            timeLabel: `${formatTimeLabel(startTime)} – ${formatTimeLabel(endTime)}`,
                            statusMeta: this.statusStyle(schedule.status ?? 'scheduled'),
                            color,
                        },
                    };
                })
                .filter(Boolean);
        },
        sortedListEvents() {
            return [...this.mapEvents()].sort((a, b) => (a.start < b.start ? -1 : 1));
        },
        tooltipText(event) {
            const p = event.extendedProps ?? {};
            return [
                p.presentationType,
                `Venue: ${p.venue}`,
                `Section: ${p.sectionName}`,
                `Time: ${p.timeLabel}`,
                `Status: ${p.statusMeta?.label ?? ''}`,
            ].join('\n');
        },
        ensureHoverTooltip() {
            if (this.hoverTooltipEl) return this.hoverTooltipEl;
            const el = document.createElement('div');
            el.className = 'schedule-hover-tooltip';
            el.style.display = 'none';
            document.body.appendChild(el);
            this.hoverTooltipEl = el;
            return el;
        },
        showHoverTooltip(info) {
            const tooltip = this.ensureHoverTooltip();
            tooltip.textContent = this.tooltipText(info.event);
            tooltip.style.display = 'block';
            this.moveHoverTooltip(info.jsEvent);
        },
        moveHoverTooltip(event) {
            if (!this.hoverTooltipEl || !event) return;
            const offset = 16;
            const rect = this.hoverTooltipEl.getBoundingClientRect();
            let left = event.clientX + offset;
            let top = event.clientY + offset;
            if (left + rect.width > window.innerWidth - 8) left = Math.max(8, event.clientX - rect.width - offset);
            if (top + rect.height > window.innerHeight - 8) top = Math.max(8, event.clientY - rect.height - offset);
            this.hoverTooltipEl.style.left = `${left}px`;
            this.hoverTooltipEl.style.top = `${top}px`;
        },
        hideHoverTooltip() {
            if (this.hoverTooltipEl) this.hoverTooltipEl.style.display = 'none';
        },
        removeToolbarTooltips() {
            const el = this.$refs.calendar;
            if (!el) return;
            el.querySelectorAll('.fc-header-toolbar button[title]').forEach((button) => button.removeAttribute('title'));
        },
        destroyCalendar() {
            const calendarEl = this.$refs.calendar;
            if (!calendarEl) return;
            if (this.calendarInstance?.destroy) this.calendarInstance.destroy();
            this.calendarInstance = null;
            calendarEl.innerHTML = '';
        },
        calendarOptions(view = 'dayGridMonth', date = null) {
            const self = this;
            const options = {
                plugins: [dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin],
                initialView: view,
                events: this.mapEvents(),
                height: 'auto',
                firstDay: 1,
                locale: 'en',
                dayMaxEvents: true,
                weekNumbers: true,
                nowIndicator: true,
                allDaySlot: false,
                eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },
                slotMinTime: '06:00:00',
                slotMaxTime: '20:00:00',
                slotDuration: '00:30:00',
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'today prev,next dayGridMonth,timeGridWeek,timeGridDay,listMonth',
                },
                buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day', list: 'List' },
                eventDidMount(info) {
                    info.el.setAttribute('title', self.tooltipText(info.event));
                },
                eventMouseEnter(info) {
                    self.showHoverTooltip(info);
                },
                eventMouseLeave() {
                    self.hideHoverTooltip();
                },
                eventClick(info) {
                    self.openEventDetails(info.event);
                },
                dateClick(info) {
                    const isListOrMonth = ['dayGridMonth', 'listMonth', 'listWeek', 'listDay'].includes(info.view.type);
                    if (isListOrMonth && self.calendarInstance?.changeView) {
                        self.calendarInstance.changeView('timeGridDay', info.dateStr);
                    }
                },
            };
            if (date) options.initialDate = date;
            return options;
        },
        createCalendar(view = 'dayGridMonth', date = null) {
            const calendarEl = this.$refs.calendar;
            if (!calendarEl) return;
            this.destroyCalendar();
            this.calendarInstance = new Calendar(calendarEl, this.calendarOptions(view, date));
            this.calendarInstance.render();
            this.$nextTick(() => this.removeToolbarTooltips());
        },
        rerenderCalendar() {
            // Filter events in place — never rebuild the calendar instance.
            if (this.viewMode !== 'calendar') return;
            if (this.calendarInstance) {
                this.calendarInstance.removeAllEvents();
                this.calendarInstance.addEventSource(this.mapEvents());
            }
        },
        syncCalendarView() {
            const nextView = this.getInitialCalendarView();
            if (this.currentCalendarView === nextView && this.calendarInstance) return;
            this.currentCalendarView = nextView;
            this.createCalendar(nextView);
        },
        switchViewMode(mode) {
            this.viewMode = mode;
            if (mode === 'calendar') {
                this.$nextTick(() => {
                    this.syncCalendarView();
                    this.calendarInstance?.updateSize?.();
                });
            }
        },
        openEventDetails(event) {
            this.selectedEvent = event;
            this.selectedOpen = true;
        },
        closeDetails() {
            this.selectedOpen = false;
            this.selectedEvent = null;
        },
        init() {
            this.activeTypeFilters = this.typeFilters().map((f) => f.value);
            this.syncCalendarView();
            window.addEventListener('resize', () => {
                if (this.viewMode === 'calendar') this.syncCalendarView();
            });
            this.$watch('selectedOpen', (open) => {
                document.body.style.overflow = open ? 'hidden' : '';
            });
        },
    }));
});
