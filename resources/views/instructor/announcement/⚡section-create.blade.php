<?php

use App\Enums\PostType;
use App\Models\Section;
use App\Services\PostService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::instructor.app')]
#[Title('Create Section Announcement')]
class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'title' => '',
            'content' => '',
            'section_ids' => [],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(['default' => 1, 'sm' => 2])
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Extended Proposal Deadline')
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Write your full notice or update details here...')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'strike',
                                ['bulletList', 'orderedList'],
                                'undo', 'redo',
                            ])
                            ->extraInputAttributes(['style' => 'min-height: 20rem; max-height: 50vh; overflow-y: auto;']),

                        FileUpload::make('images_path')
                            ->multiple()
                            ->disk('public')
                            ->directory('post-images')
                            ->panelLayout('grid')
                            ->uploadingMessage('Uploading attachment...')
                            ->visibility('public')
                            ->image(),

                        Select::make('section_ids')
                            ->label('Sections')
                            ->options(
                                Section::where('instructor_id', auth()->user()->profileable->id)
                                    ->active()
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->multiple()
                            ->required()
                            ->searchable()
                            ->live(),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(PostService $service): void
    {
        try {
            $formState = $this->form->getState();
            $formState['target_type'] = PostType::SECTIONS->value;
            $service->createForSection($formState);

            Notification::make()
                ->title('Announcement Published')
                ->body('The update has been successfully broadcast.')
                ->success()
                ->send();

            $this->redirect(route('instructor.classes'), navigate: true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Announcement Creation Error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            Notification::make()
                ->title('Failed to Publish Announcement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
};
?>

@assets
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div>
    <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
        <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">Section Broadcast</span>
    </div>

    <div class="mb-8">
        <h1 class="leading-tight" style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
            Section Announcement<span style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
        </h1>
        <p class="mt-2 text-sm" style="color: #64748B">Send an announcement to a specific section.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 md:p-8 shadow-xs">
        <form wire:submit="create" class="space-y-6">
            <div>{{ $this->form }}</div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6 mt-6">
                <a href="{{ route('instructor.classes') }}" wire:navigate
                    class="inline-flex h-10 items-center justify-center px-4 rounded-xl text-sm font-semibold text-slate-600 border border-slate-200 bg-white transition hover:bg-slate-50 active:scale-[0.98]">
                    Cancel
                </a>
                <button type="submit" wire:loading.attr="disabled" wire:target="data.images_path"
                    class="inline-flex h-10 items-center justify-center gap-2 px-5 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.2)">
                    <x-heroicon-o-paper-airplane class="h-4 w-4 transform rotate-45 -translate-y-0.5" />
                    Publish Announcement
                </button>
            </div>
        </form>
    </div>
    <x-filament-actions::modals />
</div>
