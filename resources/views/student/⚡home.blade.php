<?php

use App\Enums\PostType;
use App\Enums\ProposalStatus;
use App\Models\Consultation;
use App\Models\Post;
use App\Models\Proposal;
use App\Models\Section;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::student.app')]
#[Title('Home')]
class extends Component
{
    public Collection $announcements;

    public ?Section $section = null;

    public ?int $groupCount = 0;

    public ?int $proposalCount = 0;

    public ?int $consultationCount = 0;

    public Collection $proposals;

    public Collection $consultations;

    public function mount(): void
    {
        $user = auth()->user()->profileable;

        $section = $user->sections()
            ->with('program', 'instructor', 'semester')
            ->withCount('students', 'groups')
            ->active()
            ->first();

        $this->section = $section;
        $this->announcements = Cache::flexible('student_announcements', [600, 1800], fn (): Collection =>
            Post::where('target_type', PostType::STUDENTS)
                ->with('author', 'sections')
                ->latest()
                ->take(10)
                ->get()
        );

        if ($section) {
            $group = $section->groups()
                ->whereHas('members', fn ($q) => $q->where('student_id', $user->id))
                ->with('members')
                ->first();

            $this->groupCount = $section->groups_count;

            if ($group) {
                $this->proposals = Proposal::with('submittedBy')
                    ->where('group_id', $group->id)
                    ->latest()
                    ->get();
                $this->proposalCount = $this->proposals->count();

                $this->consultations = Consultation::where('group_id', $group->id)
                    ->with('instructor')
                    ->orderBy('scheduled_at')
                    ->get();
                $this->consultationCount = $this->consultations->count();
            }
        }

        $this->proposals ??= new Collection();
        $this->consultations ??= new Collection();
    }
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
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
        {{-- Welcome Header --}}
        <div class="mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">Student Portal</span>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <p class="text-sm mb-1" style="color: #64748B">Welcome back,</p>
                    <h1 class="leading-tight" style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        {{ auth()->user()->name }}<span style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">Track your proposals, groups, and consultations all in one place.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Announcements --}}
            <div class="lg:col-span-2 space-y-5">
                @if ($announcements->isNotEmpty())
                    <div class="flex items-center justify-between">
                        <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
                            style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">Announcements</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach ($announcements as $announcement)
                            <livewire-post :post="$announcement" defer />
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-5 lg:sticky lg:top-8 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto">
                @if ($section)
                    @island(defer: true)
                        <div class="rounded-2xl border p-4 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <p style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">My Section</p>
                            <p class="text-xl font-bold mt-1" style="color: #0F172A">{{ $section->name }}</p>
                            <p class="text-xs mt-0.5" style="color: #94A3B8">{{ $section->program?->name }} &bull; {{ $section->semester?->name }}</p>
                        </div>

                        <div class="rounded-2xl border p-4 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <p style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">Instructor</p>
                            <p class="text-xl font-bold mt-1" style="color: #0F172A">{{ $section->instructor?->full_name ?? 'N/A' }}</p>
                            <p class="text-xs mt-0.5" style="color: #94A3B8">Section adviser</p>
                        </div>

                        <div class="rounded-2xl border p-4 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <p style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">Groups</p>
                            <p class="text-xl font-bold mt-1" style="color: #0F172A">{{ $groupCount }}</p>
                            <p class="text-xs mt-0.5" style="color: #94A3B8">Total in your section</p>
                        </div>

                        <div class="rounded-2xl border p-4 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <p style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">Proposals</p>
                            <p class="text-xl font-bold mt-1" style="color: #0F172A">{{ $proposalCount }}</p>
                            <p class="text-xs mt-0.5" style="color: #94A3B8">Submitted by your group</p>
                        </div>

                        <div class="rounded-2xl border p-4 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <p style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">Consultations</p>
                            <p class="text-xl font-bold mt-1" style="color: #0F172A">{{ $consultationCount }}</p>
                            <p class="text-xs mt-0.5" style="color: #94A3B8">Scheduled sessions</p>
                        </div>
                    @endisland

                    {{-- Proposals --}}
                    <div class="rounded-2xl border overflow-hidden"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">My Proposals</span>
                        </div>
                        <div class="p-5 space-y-3">
                            @forelse ($proposals as $proposal)
                                @php
                                    $statusStyle = match ($proposal->status) {
                                        ProposalStatus::APPROVED => [
                                            'bar' => 'linear-gradient(to bottom, #059669, #34D399)',
                                            'bg' => '#ECFDF5',
                                            'color' => '#059669',
                                            'border' => '#A7F3D0',
                                        ],
                                        ProposalStatus::REJECTED => [
                                            'bar' => 'linear-gradient(to bottom, #DC2626, #F87171)',
                                            'bg' => '#FEF2F2',
                                            'color' => '#DC2626',
                                            'border' => '#FECACA',
                                        ],
                                        default => [
                                            'bar' => 'linear-gradient(to bottom, #EA580C, #FB923C)',
                                            'bg' => '#FFF7ED',
                                            'color' => '#EA580C',
                                            'border' => '#FED7AA',
                                        ],
                                    };
                                @endphp
                                <div class="relative overflow-hidden rounded-xl border transition-all duration-200 hover:-translate-y-px hover:shadow-md"
                                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                                    <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-xl" style="background: {{ $statusStyle['bar'] }}"></div>
                                    <div class="py-3.5 pl-5 pr-4">
                                        <div class="flex items-start justify-between mb-1.5 gap-2">
                                            <h3 class="text-sm font-semibold leading-snug" style="color: #0F172A">{{ $proposal->title }}</h3>
                                            <span class="shrink-0 inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium"
                                                style="background: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['color'] }}; border: 1px solid {{ $statusStyle['border'] }}">
                                                {{ ucfirst($proposal->status->value) }}
                                            </span>
                                        </div>
                                        @if ($proposal->description)
                                            <p class="text-xs mb-2" style="color: #64748B; line-height: 1.5">{{ Str::limit($proposal->description, 90) }}</p>
                                        @endif
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs" style="color: #94A3B8">
                                            <span class="flex items-center gap-1">
                                                <x-heroicon-o-user class="w-3 h-3" />
                                                {{ $proposal->submittedBy?->full_name ?? 'Unknown' }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <x-heroicon-o-clock class="w-3 h-3" />
                                                {{ $proposal->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-6">
                                    <x-heroicon-o-document-text class="w-6 h-6 mx-auto mb-2" style="color: #CBD5E1" />
                                    <p class="text-xs" style="color: #94A3B8">No proposals yet</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Consultations --}}
                    <div class="rounded-2xl border overflow-hidden"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                        <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                            <span style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Consultation Schedule</span>
                        </div>
                        <div class="p-5 space-y-4">
                            @forelse ($consultations as $c)
                                @unless ($loop->first)
                                    <div style="border-top: 1px solid #F1F5F9"></div>
                                @endunless
                                <div class="flex gap-3 items-start">
                                    <div class="shrink-0 w-12 rounded-xl text-center py-2"
                                        style="background: rgba(0,82,255,0.06); border: 1px solid rgba(0,82,255,0.12)">
                                        <div class="text-[10px] font-bold uppercase" style="color: #0052FF">{{ $c->scheduled_at?->format('M') }}</div>
                                        <div class="text-lg font-bold leading-none" style="color: #0052FF">{{ $c->scheduled_at?->format('d') }}</div>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold" style="color: #0F172A">{{ $section->instructor?->full_name ?? 'No instructor assigned' }}</p>
                                        <p class="text-xs" style="color: #64748B">
                                            {{ $c->scheduled_at?->format('g:i A') }}
                                            @if ($c->type)
                                                &middot; {{ ucfirst($c->type) }}
                                            @endif
                                        </p>
                                        @if ($c->remarks)
                                            <p class="text-xs mt-0.5 truncate" style="color: #94A3B8">{{ $c->remarks }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-center py-4" style="color: #94A3B8">No scheduled consultations</p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed py-20 px-8 text-center"
                        style="border-color: #E2E8F0; background: #FAFAFA">
                        <h3 class="mb-2" style="font-family: 'Calistoga', Georgia, serif; font-size: 1.4rem; color: #0F172A">No active section found</h3>
                        <p class="text-sm max-w-xs" style="color: #64748B; line-height: 1.6">Please contact your administrator to be assigned to an active section.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
