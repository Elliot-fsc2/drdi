<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Profile')] class extends Component {
    public function mount(): void
    {
        $user = auth()->user();

        if ($user->profileable_type === \App\Models\Student::class) {
            $user->load('profileable.program.department');
        } elseif ($user->profileable_type === \App\Models\Instructor::class) {
            $user->load('profileable.department');
        }
    }

    public function with(): array
    {
        $user = auth()->user();
        $profileable = $user->profileable;
        $isStudent = $user->profileable_type === \App\Models\Student::class;
        $isInstructor = $user->profileable_type === \App\Models\Instructor::class;

        return [
            'user' => $user,
            'profileable' => $profileable,
            'isStudent' => $isStudent,
            'isInstructor' => $isInstructor,
        ];
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
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)">
        </div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">
        {{-- ── Profile Hero Card ──────────────────────── --}}
        <div class="rounded-2xl border overflow-hidden mb-6"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
            {{-- Blue gradient banner --}}
            <div class="h-16 sm:h-24 w-full"
                style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 60%, #7B9FFF 100%)">
            </div>

            {{-- Avatar + name --}}
            <div class="px-6 pb-6">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 -mt-8 sm:-mt-10">
                    <div class="flex items-end gap-3 sm:gap-4 min-w-0">
                        {{-- Avatar --}}
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl flex items-center justify-center ring-4 ring-white shadow-md flex-shrink-0"
                            style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%)">
                            <span class="text-white font-bold text-2xl"
                                style="font-family: 'Calistoga', Georgia, serif">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                        </div>

                        <div class="pb-1 min-w-0">
                            <h2 class="text-lg sm:text-xl font-bold break-words" style="color: #0F172A">
                                {{ $user->name }}</h2>
                            <p class="text-xs sm:text-sm mt-0.5 break-all" style="color: #64748B">{{ $user->email }}
                            </p>
                        </div>
                    </div>

                    {{-- Role badge --}}
                    <div class="sm:pb-1">
                        @if ($isStudent)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
                                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF;
                                font-family: 'JetBrains Mono', monospace; letter-spacing: 0.06em; text-transform: uppercase">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                                Student
                            </span>
                        @elseif ($isInstructor)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
                                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF;
                                font-family: 'JetBrains Mono', monospace; letter-spacing: 0.06em; text-transform: uppercase">
                                <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                                {{ $profileable?->role instanceof \App\Enums\InstructorRole ? $profileable->role->value : 'Instructor' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Account Information ──────────────────────── --}}
        <div class="mb-6">
            <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 mb-4"
                style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                    Account Information
                </span>
            </div>

            <div class="rounded-2xl border overflow-hidden"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
                    style="background: linear-gradient(to bottom, #0052FF, #4D7CFF)"></div>

                <div class="divide-y" style="border-color: #F1F5F9">
                    {{-- Full Name --}}
                    <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                        <div class="w-28 sm:w-40 flex-shrink-0">
                            <p class="text-xs font-medium uppercase tracking-wide"
                                style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                Full Name
                            </p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                            <p class="text-sm font-semibold break-words" style="color: #0F172A">{{ $user->name }}</p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                        <div class="w-28 sm:w-40 flex-shrink-0">
                            <p class="text-xs font-medium uppercase tracking-wide"
                                style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                Email Address
                            </p>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm break-all" style="color: #334155">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Profile Details (role-specific) ─────────────── --}}
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 mb-4"
                style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                <span class="w-1.5 h-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                    Profile Details
                </span>
            </div>

            <div class="rounded-2xl border overflow-hidden"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                @if ($isStudent && $profileable)
                    <div class="divide-y" style="border-color: #F1F5F9">
                        {{-- Student ID --}}
                        <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                            <div class="w-28 sm:w-40 flex-shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Student ID
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 flex-1">
                                <p class="text-sm font-semibold"
                                    style="color: #0F172A; font-family: 'JetBrains Mono', monospace">
                                    {{ $profileable->student_number ?? '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Course / Program --}}
                        <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                            <div class="w-28 sm:w-40 flex-shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Course
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                                <p class="text-sm break-words" style="color: #334155">
                                    {{ $profileable->program?->name ?? '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Department --}}
                        <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                            <div class="w-28 sm:w-40 flex-shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Department
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                                <p class="text-sm break-words" style="color: #334155">
                                    {{ $profileable->program?->department?->name ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif ($isInstructor && $profileable)
                    <div class="divide-y" style="border-color: #F1F5F9">
                        {{-- Role --}}
                        <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                            <div class="w-28 sm:w-40 flex-shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Role
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                                <p class="text-sm font-semibold break-words" style="color: #0F172A">
                                    {{ $profileable->role instanceof \App\Enums\InstructorRole ? $profileable->role->value : $profileable->role ?? '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Department --}}
                        <div class="px-4 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-0">
                            <div class="w-28 sm:w-40 flex-shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Department
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                                <p class="text-sm break-words" style="color: #334155">
                                    {{ $profileable->department?->name ?? '—' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="px-6 py-8 text-center">
                        <p class="text-sm" style="color: #94A3B8">No profile details available.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
