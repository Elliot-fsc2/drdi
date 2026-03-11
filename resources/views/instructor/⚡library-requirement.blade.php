<?php

use App\Models\Group;
use App\Models\ResearchLibrary;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component implements HasSchemas {
    use InteractsWithSchemas;
    use WithFileUploads;

    public Group $group;

    public ?array $data = [];

    public function mount(): void
    {
        $existing = $this->group->researchLibrary;

        if ($existing) {
            $this->form->fill([
                'title' => $existing->title,
                'academic_year' => $existing->academic_year,
                'abstract' => $existing->abstract,
                'file_path' => $existing->file_path,
                'is_published' => $existing->is_published,
                'published_at' => $existing->published_at,
            ]);
        } else {
            $this->form->fill([
                'academic_year' => now()->year . '-' . (now()->year + 1),
                'is_published' => false,
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->label('Research Title')->placeholder('Enter the full title of the research')->required()->maxLength(255),

                TextInput::make('academic_year')->label('Academic Year')->placeholder('e.g. 2024–2025')->required()->maxLength(20),

                Textarea::make('abstract')->label('Abstract')->placeholder('Provide a concise summary of the research…')->required()->rows(6)->maxLength(5000),

                FileUpload::make('file_path')
                    ->required()
                    ->label('Research Document')
                    ->helperText('Upload the final manuscript (PDF, max 20 MB)')
                    ->disk('public')
                    ->directory('research-library')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(20480)
                    ->visibility('public'),

                Toggle::make('is_published')->label('Publish to Library')->helperText('Make this research publicly visible in the library repository'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if (!$data['is_published']) {
            $data['published_at'] = null;
        }

        $this->group->researchLibrary()->updateOrCreate(['group_id' => $this->group->id], array_merge($data, ['group_id' => $this->group->id]));

        Notification::make()->title('Saved successfully')->body('The research library entry has been saved.')->success()->send();
    }

    #[Computed]
    public function isExisting(): bool
    {
        return $this->group->researchLibrary !== null;
    }

    public function render()
    {
        return $this->view()->title('Library Requirement');
    }
};
?>

@assets
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

<div class="relative min-h-screen" style="background: #F8FAFC">

    {{-- Ambient background glows --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 h-[400px] w-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative mx-auto max-w-4xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Page Header ────────────────────────────── --}}
        <div class="mb-8">
            <div class="mb-5 flex items-center gap-2" style="font-size: 12px; color: #94A3B8">
                <a href="{{ route('instructor.groups') }}" wire:navigate class="transition-colors hover:underline"
                    style="color: #64748B">Groups</a>
                <span style="color: #CBD5E1">/</span>
                <span style="color: #0052FF; font-weight: 500">Library Repository</span>
            </div>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full" style="background: #0052FF"></span>
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                            Final Turnover
                        </span>
                    </div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.6rem, 3.5vw, 2.25rem); letter-spacing: -0.015em; color: #0F172A">
                        Library Repository Form<span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        {{ $group->name }}
                        @if ($group->section)
                            &bull; {{ $group->section->program->name }} &bull; {{ $group->section->name }}
                        @endif
                    </p>
                </div>

                @if ($this->isExisting)
                    <div class="inline-flex items-center gap-1.5 self-start rounded-full border px-3 py-1.5 text-xs font-medium"
                        style="border-color: #A7F3D0; background: #ECFDF5; color: #059669">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: #059669"></span>
                        Entry Saved
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Info Banner ─────────────────────────────── --}}
        <div class="mb-6 flex items-start gap-3 rounded-2xl border px-5 py-4"
            style="border-color: rgba(0,82,255,0.15); background: rgba(0,82,255,0.04)">
            <div>
                <p class="text-sm font-medium" style="color: #1E3A8A">Final research turnover</p>
                <p class="mt-0.5 text-xs leading-relaxed" style="color: #3B5BDB">
                    This form completes the group's journey before archiving to the research library. Ensure all details
                    are accurate and the final manuscript is attached before submitting.
                </p>
            </div>
        </div>

        {{-- ── Form Card ───────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.06)">

            {{-- Section: Research Details --}}
            <div class="border-b px-6 py-4" style="border-color: #F1F5F9">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-semibold" style="color: #0F172A">Research Details</h2>
                </div>
                <p class="mt-1 text-xs" style="color: #94A3B8">Provide the title, academic year, and abstract of the
                    research.</p>
            </div>

            <div class="p-6">
                <form wire:submit="save">
                    <div class="space-y-5">

                        {{-- Title + Academic Year row --}}
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                {{ $this->form->getComponent('title') }}
                            </div>
                            <div>
                                {{ $this->form->getComponent('academic_year') }}
                            </div>
                        </div>

                        {{-- Abstract --}}
                        {{ $this->form->getComponent('abstract') }}

                    </div>

                    {{-- Section: Document Upload --}}
                    <div class="my-6 border-t pt-6" style="border-color: #F1F5F9">
                        {{ $this->form->getComponent('file_path') }}
                    </div>

                    {{-- Section: Publication --}}
                    <div class="my-6 mt-5 border-t pt-6" style="border-color: #F1F5F9">
                        <div class="space-y-4">
                            {{ $this->form->getComponent('is_published') }}
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="border-t pt-5" style="border-color: #F1F5F9">
                        <div class="flex items-center justify-between">
                            <p class="text-xs" style="color: #94A3B8">
                                All fields marked as required must be filled before submitting.
                            </p>
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                                style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 14px rgba(0,82,255,0.3)">
                                <x-heroicon-o-archive-box-arrow-down class="h-4 w-4" />
                                {{ $this->isExisting ? 'Update Entry' : 'Submit to Library' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
