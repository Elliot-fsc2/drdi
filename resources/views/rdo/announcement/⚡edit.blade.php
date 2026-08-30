<?php

use App\Enums\InstructorRole;
use App\Enums\PostType;
use App\Models\Instructor;
use App\Models\Post;
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
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * Holds the multi-field form state.
     */
    public ?array $data = [];

    public Post $post;

    /**
     * Initializes state handling at component mount and fills form from the bound Post.
     */
    public function mount(): void
    {
        $this->form->fill([
            'title' => $this->post->title,
            'content' => $this->post->content,
            'images_path' => $this->post->images_path,
            'target_type' => $this->post->target_type,
        ]);
    }

    /**
     * Builds standard Filament Form architecture.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                ])
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

                        Select::make('target_type')
                            ->label('Target Audience')
                            ->options(collect(PostType::cases())
                                ->filter(fn(PostType $type) => $type !== PostType::SECTIONS)
                                ->mapWithKeys(fn(PostType $type) => [
                                    $type->value => \Illuminate\Support\Str::headline($type->value),
                                ])
                                ->toArray())
                            ->placeholder('Select target audience'),
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Persists valid state records to database for updates.
     */
    public function update(PostService $service): void
    {
        try {
            $formState = $this->form->getState();

            $service->updatePost($this->post, $formState);

            Notification::make()
                ->title('Announcement Updated')
                ->body('The update has been successfully saved.')
                ->success()
                ->send();

            $this->redirect('/rdo/announcements', navigate: true);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Announcement Update Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            Notification::make()
                ->title('Failed to Update Announcement')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        $layout = match (true) {
            auth()->user()?->profileable_type === Instructor::class && in_array(auth()->user()?->profileable?->role, [InstructorRole::RDO, InstructorRole::Staff]) => 'layouts::rdo.app',
            auth()->user()?->profileable_type === Instructor::class => 'layouts::instructor.app',
            default => 'layouts::app',
        };

        return $this->view()->layout($layout)->title('Edit Announcement');
    }
};
?>

@assets
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
    rel="stylesheet">
@endassets

<div>
    {{-- Decorative Badge --}}
    <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
        style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
        <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
        <span
            style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
            System Broadcast
        </span>
    </div>

    {{-- Content Header --}}
    <div class="mb-8">
        <h1 class="leading-tight"
            style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
            Edit Announcement<span
                style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
        </h1>
        <p class="mt-2 text-sm" style="color: #64748B">
            Update your post below. Rich content options allow you to highlight code snippets, link guidelines, and
            emphasize notes.
        </p>
    </div>

    {{-- Form Interface Panel --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 md:p-8 shadow-xs">
        <form wire:submit="update" class="space-y-6">

            {{-- Standardized live wire form call object --}}
            <div>
                {{ $this->form }}
            </div>

            {{-- Action Actions Bar --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6 mt-6">
                <a href="/announcements" wire:navigate
                    class="inline-flex h-10 items-center justify-center px-4 rounded-xl text-sm font-semibold text-slate-600 border border-slate-200 bg-white transition hover:bg-slate-50 active:scale-[0.98]">
                    Cancel
                </a>

                <button type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 px-5 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.2)">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    Update Announcement
                </button>
            </div>
        </form>
    </div>
    <x-filament-actions::modals />
</div>