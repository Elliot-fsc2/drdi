<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new
#[Title('Rich Editor')]
class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use WithFileUploads;

    public ?array $data = [];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->hiddenLabel()
                    ->placeholder('Title')
                    ->required(),

                RichEditor::make('content')
                    ->required()
                    ->hiddenLabel()
                    ->placeholder('Start typing...')
                    ->toolbarButtons([
                        'bold', 'italic', 'underline', 'strike',
                        ['bulletList', 'orderedList'],
                        'undo', 'redo',
                    ]),
            ])
            ->statePath('data');
    }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div>
    {{ $this->form }}
    <x-filament-actions::modals />
</div>
