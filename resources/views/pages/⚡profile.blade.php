<?php

use App\Enums\InstructorRole;
use App\Models\Instructor;
use App\Models\Student;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Profile')] class extends Component implements HasSchemas {
    use InteractsWithSchemas;

    public ?array $avatarData = [];

    public ?array $coverData = [];

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

        if (!$section) {
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
            'isInstructor' => $isInstructor,
        ];
    }

    public function avatarForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar')
                    ->label('Profile Picture')
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public')
                    ->image()
                    ->alignCenter()
                    ->panelLayout('compact circle')
                    ->loadingIndicatorPosition('center bottom')
                    ->removeUploadedFileButtonPosition('center bottom')
                    ->uploadButtonPosition('center bottom')
                    ->uploadProgressIndicatorPosition('center bottom')
                    ->maxSize(5120)
                    ->required(),
            ])
            ->statePath('avatarData');
    }

    public function coverForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('cover_photo')
                    ->label('Cover Photo')
                    ->disk('public')
                    ->directory('covers')
                    ->visibility('public')
                    ->image()
                    ->imageEditor()
                    ->imageEditorAspectRatios([
                        '3:1' => 'Wide banner',
                        '2:1' => 'Banner',
                    ])
                    ->maxSize(10240)
                    ->required(),
            ])
            ->statePath('coverData');
    }

    public function saveAvatar(): void
    {
        $data = $this->avatarForm->getState();

        auth()
            ->user()
            ->update(['avatar' => $data['avatar']]);

        $this->reset('avatarData');

        Notification::make()
            ->title('Avatar updated successfully')
            ->success()
            ->send();

        $this->dispatch('close-modal', id: 'avatar-modal');
    }

    public function saveCover(): void
    {
        $data = $this->coverForm->getState();

        auth()
            ->user()
            ->update(['cover_photo' => $data['cover_photo']]);

        $this->reset('coverData');

        $this->dispatch('close-modal', id: 'cover-modal');
    }

    public function render()
    {
        $layout = match (true) {
            auth()->user()?->profileable_type === Student::class => 'layouts::student.app',
            auth()->user()?->profileable_type === Instructor::class && in_array(auth()->user()?->profileable?->role, [InstructorRole::RDO, InstructorRole::Staff]) => 'layouts::rdo.app',
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
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
    <style>
        .fi-modal-centered>.fi-modal-window-ctn {
            grid-template-rows: 2fr auto 1fr;
        }

        .fi-modal-centered>.fi-modal-window-ctn>.fi-modal-window {
            border-radius: 1.5rem;
        }

        .fi-fo-file-upload-avatar>.fi-fo-file-upload-input-ctn {
            width: 12rem;
            height: 12rem;
        }
    </style>
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
        {{-- ── Profile Header (social media style) ─────── --}}
        <div class="mb-6 overflow-hidden rounded-3xl border"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

            {{-- Cover photo --}}
            <div class="group/cover relative h-44 w-full overflow-hidden sm:h-56 lg:h-64">
                <img src="{{ $user->cover_url }}" alt="Cover photo"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover/cover:scale-105">

                {{-- Change Cover overlay --}}
                <button type="button" wire:click="$dispatch('open-modal', { id: 'cover-modal' })"
                    class="absolute right-4 top-4 inline-flex items-center gap-2 rounded-xl px-3.5 py-2 text-xs font-semibold text-white transition-all duration-200 hover:-translate-y-0.5 sm:text-sm"
                    style="background: rgba(15,23,42,0.55); border: 1px solid rgba(255,255,255,0.25); backdrop-filter: blur(6px)">
                    <x-heroicon-o-camera class="h-4 w-4" />
                    Change Cover
                </button>
            </div>

            {{-- Avatar + identity --}}
            <div class="px-6 pb-6 sm:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="flex min-w-0 items-end gap-4">
                        {{-- Avatar with camera button --}}
                        <div class="relative -mt-10 sm:-mt-12">
                            <div
                                class="h-24 w-24 overflow-hidden rounded-full shadow-lg ring-4 ring-white sm:h-28 sm:w-28">
                                @if ($user->avatar)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                        class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center"
                                        style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%)">
                                        <span class="text-3xl font-bold text-white sm:text-4xl"
                                            style="font-family: 'Calistoga', Georgia, serif">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <button type="button" wire:click="$dispatch('open-modal', { id: 'avatar-modal' })"
                                class="absolute -bottom-1 -right-1 flex h-9 w-9 items-center justify-center rounded-full border-2 border-white text-white transition-all duration-200 hover:scale-105"
                                style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 2px 8px rgba(0,82,255,0.35)"
                                title="Change Avatar">
                                <x-heroicon-o-camera class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="min-w-0 pb-1">
                            <h2 class="break-words text-xl font-bold sm:text-2xl" style="color: #0F172A">
                                {{ $user->name }}</h2>
                            <p class="mt-0.5 break-all text-xs sm:text-sm" style="color: #64748B">{{ $user->email }}
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
                                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                                Student
                            </span>
                        @elseif ($isInstructor)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"
                                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF;
                                font-family: 'JetBrains Mono', monospace; letter-spacing: 0.06em; text-transform: uppercase">
                                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                                {{ $profileable?->role instanceof \App\Enums\InstructorRole ? $profileable->role->value : 'Instructor' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Stats strip --}}
                <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 border-t pt-5"
                    style="border-color: #F1F5F9">
                    @if ($isStudent)
                        @php $section = $this->studentSection(); @endphp
                        @if ($section)
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest"
                                    style="font-family: 'Poppins', sans-serif; color: #64748B; font-size: 12px; letter-spacing: 0.08em">Section</p>
                                <p class="mt-0.5 text-sm font-bold" style="color: #0F172A">{{ $section->name }}</p>
                            </div>
                            @php $group = $this->studentGroup(); @endphp
                            @if ($group)
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-widest"
                                        style="font-family: 'Poppins', sans-serif; color: #64748B; font-size: 12px; letter-spacing: 0.08em">Group</p>
                                    <p class="mt-0.5 text-sm font-bold" style="color: #0F172A">{{ $group->name }}</p>
                                </div>
                            @endif
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest"
                                    style="font-family: 'Poppins', sans-serif; color: #64748B; font-size: 12px; letter-spacing: 0.08em">Program</p>
                                <p class="mt-0.5 text-sm font-bold" style="color: #0F172A">
                                    {{ $profileable->program?->name ?? '—' }}</p>
                            </div>
                        @endif
                    @elseif ($isInstructor)
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest"
                                style="font-family: 'Poppins', sans-serif; color: #64748B; font-size: 12px; letter-spacing: 0.08em">Department</p>
                            <p class="mt-0.5 text-sm font-bold" style="color: #0F172A">
                                {{ $profileable->department?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-widest"
                                style="font-family: 'Poppins', sans-serif; color: #64748B; font-size: 12px; letter-spacing: 0.08em">Role</p>
                            <p class="mt-0.5 text-sm font-bold" style="color: #0F172A">
                                {{ $profileable->role instanceof \App\Enums\InstructorRole ? $profileable->role->value : 'Instructor' }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Account Information ──────────────────────── --}}
        <div class="mb-6">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5"
                style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
                    Account Information
                </span>
            </div>

            <div class="overflow-hidden rounded-2xl border"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <div class="divide-y" style="border-color: #F1F5F9">
                    {{-- Full Name --}}
                    <div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
                        <div class="w-28 flex-shrink-0 sm:w-40">
                            <p class="text-xs font-medium uppercase tracking-wide"
                                style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
                                Full Name
                            </p>
                        </div>
                        <div class="flex min-w-0 flex-1 flex-wrap items-center gap-2">
                            <p class="break-words text-sm font-semibold" style="color: #0F172A">{{ $user->name }}
                            </p>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
                        <div class="w-28 flex-shrink-0 sm:w-40">
                            <p class="text-xs font-medium uppercase tracking-wide"
                                style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
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
                    style="font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.12em; color: #0052FF; text-transform: uppercase">
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
                                    style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
                                    Student ID
                                </p>
                            </div>
                            <div class="flex flex-1 flex-wrap items-center gap-2">
                                <p class="text-sm font-semibold" style="color: #0F172A">
                                    {{ $profileable->student_number ?? '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Course / Program --}}
                        <div class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
                            <div class="w-28 flex-shrink-0 sm:w-40">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
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
                                    style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
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
                                        style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
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
                                <div
                                    class="flex flex-col gap-1 px-4 py-4 sm:flex-row sm:items-center sm:gap-0 sm:px-6">
                                    <div class="w-28 flex-shrink-0 sm:w-40">
                                        <p class="text-xs font-medium uppercase tracking-wide"
                                            style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
                                            Current Group
                                        </p>
                                    </div>
                                    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <p class="break-words text-sm font-semibold" style="color: #0F172A">
                                            {{ $group->name }}
                                        </p>
                                        <p class="text-xs" style="color: #64748B">
                                            {{ $group->members->count() }}
                                            member{{ $group->members->count() !== 1 ? 's' : '' }}
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
                                    style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
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
                                    style="color: #64748B; font-family: 'Poppins', sans-serif; font-size: 12px; letter-spacing: 0.08em">
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

    {{-- ── Avatar modal (explicit, centered) ─────────── --}}
    <x-filament::modal id="avatar-modal" alignment="center" width="xl" closeButton class="fi-modal-centered"
        :extra-modal-window-attribute-bag="new \Illuminate\View\ComponentAttributeBag(['class' => 'rounded-3xl'])">
        <x-slot name="heading">Change Avatar</x-slot>
        <x-slot name="description">Upload a new profile picture. You can crop and resize it in the editor.</x-slot>

        {{ $this->avatarForm }}

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-3">
                <x-filament::button color="gray" tag="button"
                    wire:click="$dispatch('close-modal', { id: 'avatar-modal' })">
                    Cancel
                </x-filament::button>
                <x-filament::button color="primary" tag="button" wire:click="saveAvatar">
                    Save Avatar
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    {{-- ── Cover modal (explicit, centered) ───────────── --}}
    <x-filament::modal id="cover-modal" alignment="center" width="lg" closeButton class="fi-modal-centered"
        :extra-modal-window-attribute-bag="new \Illuminate\View\ComponentAttributeBag(['class' => 'rounded-3xl'])">
        <x-slot name="heading">Change Cover Photo</x-slot>
        <x-slot name="description">Upload a new cover photo. It will be shown as a wide banner on your
            profile.</x-slot>

        {{ $this->coverForm }}

        <x-slot name="footer">
            <div class="flex items-center justify-end gap-3">
                <x-filament::button color="gray" tag="button"
                    wire:click="$dispatch('close-modal', { id: 'cover-modal' })">
                    Cancel
                </x-filament::button>
                <x-filament::button color="primary" tag="button" wire:click="saveCover">
                    Save Cover
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</div>
