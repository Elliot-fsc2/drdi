<?php

use App\Models\Program;
use App\Models\Section;
use App\Models\Semester;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Attributes\Url;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Classes')]
  class extends Component implements HasActions, HasSchemas {
  use InteractsWithActions;
  use InteractsWithSchemas;


  #[Url]
  public $search = '';
  public $semester = '2nd Semester 2025-2026';

  #[Computed]
  public function routePrefix(): string
  {
    $user = auth()->user();
    $isRDO = $user->profileable_type === \App\Models\Instructor::class
      && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

    return $isRDO ? 'rdo' : 'instructor';
  }

  public function createSectionAction(): Action
  {
    return Action::make('createSection')
      ->modalWidth('lg')
      ->color('success')
      ->modalCloseButton(false)
      ->label('Create Section')
      ->icon(Heroicon::Plus)
      ->color('primary')
      ->form([
        TextInput::make('name')
          ->label('Section Name')
          ->placeholder('e.g., BSCS-4A')
          ->required()
          ->maxLength(255),

        Select::make('program_id')
          ->label('Program')
          ->options(Program::pluck('name', 'id'))
          ->required()
          ->searchable(),

        Select::make('semester_id')
          ->label('Semester')
          ->options(Semester::active()->pluck('name', 'id'))
          ->required()
          ->searchable(),
      ])
      ->successNotificationTitle('Section created successfully')
      ->action(function (array $data): void {
        Section::create([
          'name' => $data['name'],
          'program_id' => $data['program_id'],
          'semester_id' => $data['semester_id'],
          'instructor_id' => auth()->user()->profileable->id,
        ]);

        unset($this->classes);
      });
  }

  #[Computed]
  public function classes()
  {
    $query = Section::where('instructor_id', auth()->user()->profileable->id)
      ->whereHas('semester', function ($query) {
        $query->active();
      })
      ->withCount('students')
      ->withCount('groups')
      ->when($this->search, function ($query) {
        $query->where('name', 'like', '%' . $this->search . '%');
      });

    return $query->get()
      ->map(function ($section) {
        return [
          'id' => $section->id,
          'section' => $section->name,
          'course' => $section->program->name,
          'students_count' => $section->students_count,
          'groups_count' => $section->groups_count,
        ];
      });
  }
};
?>


@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-3 sm:p-4 lg:p-6 bg-slate-50 min-h-screen">
  <div class="max-w-7xl mx-auto">

    <div
      class="flex flex-col md:flex-row md:items-end justify-between mb-4 sm:mb-6 border-b border-slate-200 pb-3 sm:pb-4 gap-3">
      <div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900">My Classes</h1>
        <p class="text-slate-500 text-xs sm:text-sm font-medium mt-1 uppercase tracking-wider">{{ $semester }}</p>
      </div>

      <div class="mt-4 md:mt-0 flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        <div class="w-full sm:w-auto">
          <x-filament::button wire:click="mountAction('createSection')"
            class=" bg-blue-500 hover:bg-blue-600 focus:ring-blue-500 focus:ring-offset-blue-200 text-white">
            Create Section
          </x-filament::button>
        </div>

        <div class="relative w-full sm:w-64">
          <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search section..."
            class="pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none w-full transition-all">
          <div class="absolute left-3 top-2.5 text-slate-400">
            <x-heroicon-o-magnifying-glass class="h-4 w-4" />
          </div>
        </div>
      </div>
    </div>

    @if(count($this->classes) === 0)
      <div class="bg-white border border-slate-200 rounded-lg p-12 sm:p-20 text-center">
        <x-heroicon-o-academic-cap class="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-slate-300 mb-4" />
        <p class="text-slate-500 text-sm sm:text-base">No classes assigned.</p>
        <p class="text-slate-400 text-xs sm:text-sm mt-2">Create your first section to get started.</p>
      </div>
    @else
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
        @foreach($this->classes as $class)
          <a href="{{ route($this->routePrefix . '.classes.view', ['section' => $class['id']]) }}"
            wire:key="{{ $class['section'] }}" wire:navigate>
            <div
              class="bg-white border border-slate-200 rounded-lg shadow-sm hover:shadow-md hover:border-blue-300 transition-all duration-200 overflow-hidden group cursor-pointer h-full">
              <div
                class="p-3 sm:p-4 border-b border-slate-200 bg-linear-to-r from-blue-50 via-white to-blue-50 group-hover:from-blue-100 group-hover:to-blue-50 transition-colors">
                <div class="font-bold text-slate-900 group-hover:text-blue-700 transition-colors text-sm sm:text-base">
                  {{ $class['section'] }}
                </div>
                <div class="text-xs sm:text-sm text-slate-600 mt-1">{{ $class['course'] }}</div>
              </div>

              <div class="p-3 sm:p-4 bg-white">
                <div class="flex items-center gap-3 sm:gap-5">
                  <div class="flex-1">
                    <div class="text-xs text-slate-500 mb-1.5 font-medium uppercase tracking-wide">Students</div>
                    <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $class['students_count'] }}</div>
                  </div>
                  <div class="h-10 w-px bg-slate-200"></div>
                  <div class="flex-1">
                    <div class="text-xs text-slate-500 mb-1.5 font-medium uppercase tracking-wide">Groups</div>
                    <div class="text-xl sm:text-2xl font-bold text-slate-900">{{ $class['groups_count'] }}</div>
                  </div>
                </div>
              </div>
            </div>
          </a>
        @endforeach
      </div>
    @endif
  </div>
  <x-filament-actions::modals />
</div>