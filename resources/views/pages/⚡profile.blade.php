<?php

use App\Enums\InstructorRole;
use App\Models\Instructor;
use App\Models\Student;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Profile')] class extends Component {
    public function mount(): void
    {
        $user = auth()->user();

        if ($user->profileable_type === \App\Models\Student::class) {
            $user->load('profileable.program.department', 'profileable.sections.semester', 'profileable.sections.instructor');
        } elseif ($user->profileable_type === \App\Models\Instructor::class) {
            $user->load('profileable.department');
        }
    }

    public function studentSection(): ?\App\Models\Section
    {
        $student = auth()->user()->profileable;

        return $student->sections()->active()->with('program', 'instructor', 'semester')->first();
    }

    public function studentGroup(): ?\App\Models\Group
    {
        $student = auth()->user()->profileable;

        $section = $this->studentSection();

        if (! $section) {
            return null;
        }

        return $student->groups()->with('members', 'section')->firstWhere('section_id', $section->id);
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
            'isInstructor' => $isInstructor
        ];
    }

     public function render()
     {
         $layout = match (true) {
             auth()->user()?->profileable_type === Student::class => 'layouts::student.app',
             auth()->user()?->profileable_type === Instructor::class && auth()->user()?->profileable?->role === InstructorRole::RDO => 'layouts::rdo.app',
             auth()->user()?->profileable_type === Instructor::class => 'layouts::instructor.app',
             default => 'layouts::app',
         };

         return $this->view()->layout($layout);
     }
};
?>

@assets
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
		rel="stylesheet">
@endassets

<div class="relative min-h-screen" style="background: #F8FAFC">

	{{-- Ambient background glows --}}
	<div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
		<div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
			style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)">
		</div>
		<div class="absolute -left-24 bottom-1/3 h-[400px] w-[400px] rounded-full"
			style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
		</div>
	</div>

	<div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">
		{{-- ── Profile Hero Card ──────────────────────── --}}
		<div class="mb-6 overflow-hidden rounded-2xl border"
			style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
			{{-- Blue gradient banner --}}
			<div class="h-16 w-full sm:h-24" style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 60%, #7B9FFF 100%)">
			</div>

			{{-- Avatar + name --}}
			<div class="px-6 pb-6">
				<div class="-mt-8 flex flex-col gap-4 sm:-mt-10 sm:flex-row sm:items-end sm:justify-between">
					<div class="flex min-w-0 items-end gap-3 sm:gap-4">
						{{-- Avatar --}}
						<div
							class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl shadow-md ring-4 ring-white sm:h-20 sm:w-20"
							style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%)">
							<span class="text-2xl font-bold text-white" style="font-family: 'Calistoga', Georgia, serif">
								{{ strtoupper(substr($user->name, 0, 1)) }}
							</span>
						</div>

						<div class="min-w-0 pb-1">
							<h2 class="break-words text-lg font-bold sm:text-xl" style="color: #0F172A">
								{{ $user->name }}</h2>
							<p class="mt-0.5 break-all text-xs sm:text-sm" style="color: #64748B">{{ $user->email }}
							</p>
						</div>
					</div>

					{{-- Role badge --}}
					<div class="sm:pb-1">
						@if ($isStudent)
							<span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
								style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF;
                                font-family: 'JetBrains Mono', monospace; letter-spacing: 0.06em; text-transform: uppercase">
								<span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
								Student
							</span>
						@elseif ($isInstructor)
							<span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
								style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF;
                                font-family: 'JetBrains Mono', monospace; letter-spacing: 0.06em; text-transform: uppercase">
								<span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
								{{ $profileable?->role instanceof \App\Enums\InstructorRole ? $profileable->role->value : 'Instructor' }}
							</span>
						@endif
					</div>
				</div>
			</div>
		</div>

		{{-- ── Account Information ──────────────────────── --}}
		<div class="mb-6">
			<div class="mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
				style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
				<span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
				<span
					style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
					Account Information
				</span>
			</div>

			<div class="overflow-hidden rounded-2xl border"
				style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
				<div class="absolute bottom-0 left-0 top-0 w-[3px] rounded-l-2xl"
					style="background: linear-gradient(to bottom, #0052FF, #4D7CFF)"></div>

				<div class="divide-y" style="border-color: #F1F5F9">
					{{-- Full Name --}}
					<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
						<div class="w-28 flex-shrink-0 sm:w-40">
							<p class="text-xs font-medium uppercase tracking-wide"
								style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
								Full Name
							</p>
						</div>
						<div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
							<p class="break-words text-sm font-semibold" style="color: #0F172A">{{ $user->name }}</p>
						</div>
					</div>

					{{-- Email --}}
					<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
						<div class="w-28 flex-shrink-0 sm:w-40">
							<p class="text-xs font-medium uppercase tracking-wide"
								style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
								Email Address
							</p>
						</div>
						<div class="min-w-0 flex-1">
							<p class="break-all text-sm" style="color: #334155">{{ $user->email }}</p>
						</div>
					</div>
				</div>
			</div>
		</div>

		{{-- ── Profile Details (role-specific) ─────────────── --}}
		<div>
			<div class="mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
				style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
				<span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
				<span
					style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
					Profile Details
				</span>
			</div>

			<div class="overflow-hidden rounded-2xl border"
				style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

				@if ($isStudent && $profileable)
					<div class="divide-y" style="border-color: #F1F5F9">
						{{-- Student ID --}}
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Student ID
								</p>
							</div>
							<div class="flex flex-1 flex-wrap items-center gap-2">
								<p class="text-sm font-semibold" style="color: #0F172A; font-family: 'JetBrains Mono', monospace">
									{{ $profileable->student_number ?? '—' }}
								</p>
							</div>
						</div>

						{{-- Course / Program --}}
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Course
								</p>
							</div>
							<div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
								<p class="break-words text-sm" style="color: #334155">
									{{ $profileable->program?->name ?? '—' }}
								</p>
							</div>
						</div>

						{{-- Department --}}
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Department
								</p>
							</div>
							<div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
								<p class="break-words text-sm" style="color: #334155">
									{{ $profileable->program?->department?->name ?? '—' }}
								</p>
							</div>
						</div>

						{{-- Current Section --}}
						@php $section = $this->studentSection(); @endphp
						@if ($section)
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Current Section
								</p>
							</div>
							<div class="flex min-w-0 flex-1 flex-col gap-0.5">
								<p class="break-words text-sm font-semibold" style="color: #0F172A">
									{{ $section->name }}
								</p>
								<p class="text-xs" style="color: #64748B">
									{{ $section->program?->name }} &bull; {{ $section->semester?->name }}
								</p>
							</div>
						</div>

						{{-- Current Group --}}
						@php $group = $this->studentGroup(); @endphp
						@if ($group)
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Current Group
								</p>
							</div>
							<div class="flex min-w-0 flex-1 flex-col gap-0.5">
								<p class="break-words text-sm font-semibold" style="color: #0F172A">
									{{ $group->name }}
								</p>
								<p class="text-xs" style="color: #64748B">
									{{ $group->members->count() }} member{{ $group->members->count() !== 1 ? 's' : '' }}
								</p>
							</div>
						</div>
						@endif
						@endif
					</div>
				@elseif ($isInstructor && $profileable)
					<div class="divide-y" style="border-color: #F1F5F9">
						{{-- Role --}}
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Role
								</p>
							</div>
							<div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
								<p class="break-words text-sm font-semibold" style="color: #0F172A">
									{{ $profileable->role instanceof \App\Enums\InstructorRole ? $profileable->role->value : $profileable->role ?? '—' }}
								</p>
							</div>
						</div>

						{{-- Department --}}
						<div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
							<div class="w-28 flex-shrink-0 sm:w-40">
								<p class="text-xs font-medium uppercase tracking-wide"
									style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
									Department
								</p>
							</div>
							<div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
								<p class="break-words text-sm" style="color: #334155">
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
