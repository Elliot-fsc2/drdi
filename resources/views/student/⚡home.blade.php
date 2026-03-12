<?php

use App\Enums\ProposalStatus;
use App\Models\Consultation;
use App\Models\Proposal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')] class extends Component {
    public $user;

    public function mount()
    {
        $this->user = auth()->user()->load('profileable.sections.program', 'profileable.sections.instructor', 'profileable.sections.semester');
    }

    #[Computed]
    public function sections()
    {
        $section = $this->user->profileable->sections()->withCount('students', 'groups')->active()->first();

        if (!$section) {
            return null;
        }

        return [
            'id' => $section->id,
            'name' => $section->name,
            'program_name' => $section->program->name ?? 'N/A',
            'instructor_name' => $section->instructor->full_name ?? 'No instructor assigned',
            'semester_name' => $section->semester->name ?? 'N/A',
            'students_count' => $section->students_count,
            'groups_count' => $section->groups_count,
        ];
    }

    #[Computed]
    public function group()
    {
        $sections = $this->sections();

        $sectionId = $sections['id'] ?? null;

        if (!$sectionId) {
            return null;
        }

        $group = $this->user->profileable->groups()->with('members', 'section')->firstWhere('section_id', $sectionId);

        if ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'section_id' => $group->section->name ?? 'N/A',
                'leader_id' => $group->leader_id,
                'members' => $group->members
                    ->map(
                        fn($member) => [
                            'id' => $member->id,
                            'name' => $member->full_name,
                        ],
                    )
                    ->toArray(),
            ];
        }

        return [
            'id' => null,
            'name' => 'No group assigned',
            'section_id' => $sectionId,
            'leader_id' => null,
            'members' => [],
        ];
    }

    #[Computed]
    public function proposals()
    {
        $group = $this->group();

        // Fixed: Check if group exists and has an id
        if (!$group || !$group['id']) {
            return [];
        }

        $proposals = Proposal::with('submittedBy')->where('group_id', $group['id'])->get();

        return $proposals
            ->map(
                fn($proposal) => [
                    'id' => $proposal->id,
                    'title' => $proposal->title,
                    'description' => $proposal->description,
                    'group_id' => $proposal->group_id,
                    'submitted_by' => $proposal->submittedBy?->full_name ?? 'Unknown Student',
                    'status' => $proposal->status instanceof \BackedEnum ? $proposal->status->value : $proposal->status,
                    'feedback' => $proposal->feedback,
                ],
            )
            ->toArray();
    }

    #[Computed]
    public function consultations()
    {
        $group = $this->group();

        // Fixed: Check if group exists and has an id
        if (!$group || !$group['id']) {
            return [];
        }

        return Consultation::where('group_id', $group['id'])
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(
                fn($consultation) => [
                    'id' => $consultation->id,
                    'group_id' => $consultation->group_id,
                    'instructor_id' => $consultation->instructor_id,
                    'scheduled_at' => $consultation->scheduled_at,
                    'status' => $consultation->status,
                    'remarks' => $consultation->remarks,
                    'type' => $consultation->type,
                ],
            )
            ->toArray();
    }

    // Added: Missing formatTime method
    public function formatTime($datetime): string
    {
        return \Carbon\Carbon::parse($datetime)->format('M d, Y - g:i A');
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            ProposalStatus::APPROVED->value => 'bg-green-50 text-green-700',
            'revision' => 'bg-orange-50 text-orange-700',
            ProposalStatus::PENDING->value, 'scheduled' => 'bg-yellow-50 text-yellow-700',
            ProposalStatus::REJECTED->value => 'bg-red-50 text-red-700',
            'completed' => 'bg-green-50 text-green-700',
            default => 'bg-gray-50 text-gray-700',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'revision' => 'Revision Needed',
            'scheduled' => 'Scheduled',
            ProposalStatus::APPROVED->value => 'Approved',
            ProposalStatus::REJECTED->value => 'Rejected',
            ProposalStatus::PENDING->value => 'Pending',
            'completed' => 'Completed',
            default => ucfirst($status),
        };
    }
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

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

        {{-- ── Welcome Header ──────────────────────────── --}}
        <div class="mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Student Portal
                </span>
            </div>

            <p class="text-sm mb-1" style="color: #64748B">Welcome back,</p>
            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                {{ auth()->user()->name }}<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                Track your proposals, groups, and consultations all in one place.
            </p>
        </div>

        @if ($this->sections)

            {{-- ── Section & Group ─────────────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">

                {{-- My Section --}}
                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4 flex items-center gap-2"
                        style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            My Section
                        </span>
                    </div>

                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4 gap-3">
                            <div>
                                <p class="font-bold text-base" style="color: #0F172A">
                                    {{ $this->sections['name'] }}</p>
                                <p class="text-xs mt-0.5" style="color: #64748B">
                                    {{ $this->sections['program_name'] }}
                                    &bull;
                                    {{ $this->sections['semester_name'] }}
                                </p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium"
                                style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #059669"></span>
                                Active
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-sm">
                                <span style="color: #64748B">Instructor:</span>
                                <span class="font-semibold" style="color: #0F172A">
                                    {{ $this->sections['instructor_name'] }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span style="color: #64748B">Students:</span>
                                <span class="font-semibold" style="color: #0F172A">
                                    {{ $this->sections['students_count'] }} enrolled
                                </span>
                                <span style="color: #94A3B8">&bull; {{ $this->sections['groups_count'] }} groups</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- My Group --}}
                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4 flex items-center gap-2"
                        style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            My Group
                        </span>
                    </div>

                    @php $group = $this->group; @endphp
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4 gap-3">
                            <div>
                                <p class="font-bold text-base" style="color: #0F172A">{{ $group['name'] }}</p>
                                @if ($group['leader_id'] === auth()->user()->profileable->id)
                                    <p class="text-xs mt-0.5 font-semibold" style="color: #059669">Group Leader</p>
                                @endif
                            </div>
                            <span class="shrink-0 inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium"
                                style="background: rgba(0,82,255,0.07); color: #0052FF; border: 1px solid rgba(0,82,255,0.12)">
                                {{ count($group['members']) }} members
                            </span>
                        </div>

                        @if (!empty($group['members']))
                            <div class="mb-4">
                                <p class="text-xs mb-2.5"
                                    style="font-family: 'JetBrains Mono', monospace; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
                                    Members</p>
                                <div class="space-y-2.5">
                                    @foreach ($group['members'] as $member)
                                        <div class="flex items-center justify-between text-sm">
                                            <div class="flex items-center gap-2.5">
                                                <div>
                                                    <p class="font-medium text-sm" style="color: #0F172A">
                                                        {{ $member['name'] }}</p>
                                                    @if ($member['id'] === $group['leader_id'])
                                                        <p class="text-[10px] font-semibold" style="color: #059669">
                                                            Leader</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 pt-4" style="border-top: 1px solid #F1F5F9">
                            <div class="text-center py-2 rounded-xl" style="background: #F8FAFC">
                                <p class="text-lg font-bold" style="color: #0F172A">{{ count($this->proposals) }}</p>
                                <p class="text-xs" style="color: #64748B">Proposals</p>
                            </div>
                            <div class="text-center py-2 rounded-xl" style="background: #F8FAFC">
                                <p class="text-lg font-bold" style="color: #0F172A">
                                    {{ count($this->consultations) }}</p>
                                <p class="text-xs" style="color: #64748B">Consultations</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ── Proposals & Consultations ────────────── --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                {{-- My Proposals --}}
                <div class="lg:col-span-2">
                    <div class="flex items-center justify-between mb-4">
                        <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
                            style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                            <span
                                style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                                My Proposals
                            </span>
                        </div>
                    </div>

                    @php $proposals = $this->proposals; @endphp
                    @if (!empty($proposals))
                        <div class="space-y-3">
                            @foreach ($proposals as $proposal)
                                @php
                                    $borderColor = match ($proposal['status']) {
                                        ProposalStatus::APPROVED->value => '#A7F3D0',
                                        'revision' => '#FED7AA',
                                        ProposalStatus::REJECTED->value => '#FECACA',
                                        default => '#E2E8F0',
                                    };
                                    $stripeColor = match ($proposal['status']) {
                                        ProposalStatus::APPROVED->value
                                            => 'linear-gradient(to bottom, #059669, #34D399)',
                                        'revision' => 'linear-gradient(to bottom, #EA580C, #FB923C)',
                                        ProposalStatus::REJECTED->value
                                            => 'linear-gradient(to bottom, #DC2626, #F87171)',
                                        default => 'linear-gradient(to bottom, #0052FF, #4D7CFF)',
                                    };
                                @endphp
                                <div class="relative overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                                    style="border-color: {{ $borderColor }}; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                                        style="background: {{ $stripeColor }}"></div>
                                    <div class="py-5 pl-6 pr-5">
                                        <div class="flex items-start justify-between mb-2 gap-3">
                                            <h3 class="font-semibold text-sm leading-snug" style="color: #0F172A">
                                                {{ $proposal['title'] }}</h3>
                                            <span
                                                class="shrink-0 inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium"
                                                style="{{ $this->getStatusBadgeClass($proposal['status']) }}">
                                                {{ $this->getStatusLabel($proposal['status']) }}
                                            </span>
                                        </div>

                                        <p class="text-xs mb-3" style="color: #64748B; line-height: 1.5">
                                            {{ str($proposal['description'])->limit(100) }}</p>

                                        @if ($proposal['feedback'])
                                            <div class="rounded-xl p-3 mb-3"
                                                style="background: #FFF7ED; border: 1px solid #FED7AA">
                                                <p class="text-xs font-semibold mb-0.5" style="color: #92400E">
                                                    Feedback</p>
                                                <p class="text-xs" style="color: #B45309">
                                                    "{{ $proposal['feedback'] }}"</p>
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-3 text-xs" style="color: #94A3B8">
                                            <span>Group #{{ $proposal['group_id'] }}</span>
                                            <span style="color: #E2E8F0">&bull;</span>
                                            <span>{{ $proposal['submitted_by'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed py-16 text-center"
                            style="border-color: #E2E8F0; background: #FAFAFA">
                            <p class="text-sm font-semibold mb-1" style="color: #374151">No proposals yet</p>
                            <p class="text-xs" style="color: #94A3B8">Your group's proposals will appear here.</p>
                        </div>
                    @endif
                </div>

                {{-- Consultation Schedule --}}
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 mb-4"
                        style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                            Consultations
                        </span>
                    </div>

                    <div class="rounded-2xl border overflow-hidden"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        @php $consultations = $this->consultations; @endphp
                        @if (!empty($consultations))
                            <div class="divide-y" style="border-color: #F1F5F9">
                                @foreach ($consultations as $consultation)
                                    @php
                                        $isCompleted = $consultation['status'] === 'completed';
                                        $isUpcoming = $consultation['type'] === 'upcoming' && !$isCompleted;
                                    @endphp
                                    <div class="p-5"
                                        style="{{ $isUpcoming ? 'background: rgba(0,82,255,0.03)' : '' }}">
                                        <div class="flex gap-3.5">
                                            <div class="shrink-0 w-12 rounded-xl text-center py-2"
                                                style="{{ $isCompleted ? 'background: #F1F5F9; opacity: 0.7' : ($isUpcoming ? 'background: rgba(0,82,255,0.08); border: 1px solid rgba(0,82,255,0.15)' : 'background: #F8FAFC; border: 1px dashed #E2E8F0') }}">
                                                @if ($consultation['scheduled_at'])
                                                    <p class="text-[10px] font-bold"
                                                        style="color: {{ $isUpcoming ? '#0052FF' : '#64748B' }}">
                                                        {{ \Carbon\Carbon::parse($consultation['scheduled_at'])->format('M') }}
                                                    </p>
                                                    <p class="text-base font-bold leading-none"
                                                        style="color: {{ $isUpcoming ? '#0052FF' : '#374151' }}">
                                                        {{ \Carbon\Carbon::parse($consultation['scheduled_at'])->format('d') }}
                                                    </p>
                                                @else
                                                    <p class="text-xs" style="color: #94A3B8">TBD</p>
                                                    <p class="text-base font-bold" style="color: #CBD5E1">--</p>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold" style="color: #0F172A">
                                                    {{ $this->sections['instructor_name'] ?? 'No instructor assigned' }}
                                                </p>
                                                @if ($consultation['scheduled_at'])
                                                    <p class="text-xs mt-0.5" style="color: #64748B">
                                                        {{ $this->formatTime($consultation['scheduled_at']) }}</p>
                                                @endif
                                                @if ($consultation['remarks'])
                                                    <p class="text-xs mt-1" style="color: #64748B">
                                                        {{ $consultation['remarks'] }}</p>
                                                @endif
                                                <span class="inline-flex items-center gap-1 mt-1.5 text-xs font-medium"
                                                    style="color: {{ $isCompleted ? '#059669' : ($isUpcoming ? '#0052FF' : '#D97706') }}">
                                                    <span class="w-1.5 h-1.5 rounded-full"
                                                        style="background: {{ $isCompleted ? '#059669' : ($isUpcoming ? '#0052FF' : '#D97706') }}"></span>
                                                    {{ $this->getStatusLabel($consultation['status']) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                                <p class="text-sm font-semibold mb-1" style="color: #374151">No consultations</p>
                                <p class="text-xs" style="color: #94A3B8">Scheduled sessions will appear here.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        @else
            {{-- No active section --}}
            <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed py-20 px-8 text-center"
                style="border-color: #E2E8F0; background: #FAFAFA">

                <h3 class="mb-2"
                    style="font-family: 'Calistoga', Georgia, serif; font-size: 1.4rem; color: #0F172A">
                    No active section found</h3>
                <p class="text-sm max-w-xs" style="color: #64748B; line-height: 1.6">
                    Please contact your administrator to be assigned to an active section.
                </p>
            </div>
        @endif

    </div>
</div>
