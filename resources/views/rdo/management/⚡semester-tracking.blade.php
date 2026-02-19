<?php

use App\Models\Semester;
use App\Models\ThesisRate;
use App\Services\FeeService;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

new #[Title('Semester Tracking')]
  class extends Component implements HasActions, HasSchemas {
  use InteractsWithActions;
  use InteractsWithSchemas;

  public ?int $selectedId = null;

  public function mount()
  {
    $this->selectedId = Semester::active()->latest('start_date')->first()?->id
      ?? Semester::latest('start_date')->first()?->id;
  }

  public function selectSemester($id)
  {
    $this->selectedId = $id;
  }

  public function clearSelection()
  {
    $this->selectedId = null;
  }

  #[Computed]
  public function semesters()
  {
    return Semester::orderBy('start_date', 'desc')->get();
  }

  #[Computed]
  public function selectedSemester()
  {
    if (!$this->selectedId)
      return null;
    return Semester::with('rates')->find($this->selectedId);
  }

  public function createAction(): Action
  {
    return Action::make('createAction')
      ->label('Add Semester')
      ->modalCloseButton(false)
      ->modalAutoFocus(false)
      ->modalWidth('md')
      ->successNotificationTitle('Semester Added')
      ->modalHeading('Add New Semester')
      ->form([
        TextInput::make('name')
          ->label('Semester Name')
          ->required()
          ->placeholder('e.g. 2nd Semester AY 2024-2025'),
        DatePicker::make('start_date')
          ->label('Start Date')
          ->required(),
        DatePicker::make('end_date')
          ->label('End Date')
          ->required()
      ])
      ->action(function (array $data) {
        $sem = Semester::create($data);
        $this->selectedId = $sem->id;
      });
  }

  public function editAction(): Action
  {
    return Action::make('editAction')
      ->label('Edit Semester')
      ->modalCloseButton(false)
      ->successNotificationTitle('Semester Updated')
      ->modalAutoFocus(false)
      ->modalWidth('md')
      ->modalHeading('Edit Semester Details')
      ->fillForm(function () {
        $sem = $this->selectedSemester;
        return [
          'name' => $sem->name,
          'start_date' => $sem->start_date,
          'end_date' => $sem->end_date,
        ];
      })
      ->form([
        TextInput::make('name')
          ->label('Semester Name')
          ->required(),
        DatePicker::make('start_date')
          ->label('Start Date')
          ->required(),
        DatePicker::make('end_date')
          ->label('End Date')
          ->required()
      ])
      ->action(function (array $data) {
        $this->selectedSemester->update($data);
        unset($this->selectedSemester);
      });
  }

  public function syncRatesAction(): Action
  {
    return Action::make('syncRatesAction')
      ->label('Add Rates')
      ->modalCloseButton(false)
      ->successNotificationTitle('Rates Assigned')
      ->modalAutoFocus(false)
      ->modalWidth('2xl')
      ->databaseTransaction()
      ->modalHeading('Assign Master Rates')
      ->modalDescription('Select rates to add to this semester. Group fees will update automatically.')
      ->schema([
        CheckboxList::make('selectedRateIds')
          ->label('Available Rates')
          ->options(function () {
            $assignedIds = DB::table('semester_rates')
              ->where('semester_id', $this->selectedId)
              ->pluck('thesis_rate_id');

            return ThesisRate::whereNotIn('id', $assignedIds)
              ->get()
              ->mapWithKeys(fn($rate) => [
                $rate->id => "{$rate->name} (₱" . number_format($rate->amount, 2) . ")"
              ]);
          })
          ->columns(2)
          ->required()
      ])
      ->action(function (array $data, FeeService $service) {
        $semester = $this->selectedSemester;
        $semester->rates()->attach($data['selectedRateIds']);
        $service->updateAllGroupsInSemester($semester);
        unset($this->selectedSemester);
      });
  }

  public function removeRateAction(): Action
  {
    return Action::make('removeRateAction')
      ->requiresConfirmation()
      ->successNotificationTitle('Rate Removed')
      ->modalCloseButton(false)
      ->modalHeading('Remove Rate')
      ->modalDescription('Are you sure? This will immediately recalculate the fees for all groups under this semester.')
      ->modalSubmitActionLabel('Yes, Remove')
      ->color('danger')
      ->action(function (array $arguments, FeeService $service) {
        $rateId = $arguments['rate'] ?? null;
        if (!$rateId || !$this->selectedId)
          return;

        $semester = $this->selectedSemester;
        $semester->rates()->detach($rateId);

        $service->updateAllGroupsInSemester($semester);
        unset($this->selectedSemester);
      });
  }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 font-sans">

  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Academic Semesters</h1>
      <p class="text-sm text-gray-500 mt-1">Manage academic terms and synchronize global thesis rates.</p>
    </div>
    <div class="w-full sm:w-auto flex-shrink-0">
      <x-filament::button wire:click="mountAction('createAction')" icon="heroicon-m-plus"
        class="bg-blue-600 hover:bg-blue-700 text-white w-full sm:w-auto">
        Add Semester
      </x-filament::button>
    </div>
  </div>

  <div class="flex flex-col lg:flex-row gap-6 sm:gap-8 relative">

    <div class="w-full lg:w-1/3 flex-shrink-0 {{ $this->selectedId ? 'hidden lg:flex' : 'flex' }} flex-col">
      <div
        class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[75vh] lg:h-[600px]">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/80 sticky top-0 z-10">
          <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">All Terms</h3>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 custom-scrollbar bg-white">
          @forelse($this->semesters as $sem)
            @php
              $isActive = $sem->start_date->lte(now()->startOfDay()) && $sem->end_date->gte(now()->startOfDay());
            @endphp
            <button wire:click="selectSemester({{ $sem->id }})"
              class="w-full text-left px-5 py-4 transition-all focus:outline-none flex flex-col gap-2 group
                                                      {{ $this->selectedId === $sem->id ? 'bg-blue-50/40 border-l-4 border-blue-600' : 'hover:bg-gray-50 border-l-4 border-transparent' }}">
              <div class="flex items-start justify-between w-full">
                <h4
                  class="text-sm font-semibold pr-2 leading-tight {{ $this->selectedId === $sem->id ? 'text-blue-700' : 'text-gray-900 group-hover:text-gray-700' }}">
                  {{ $sem->name }}
                </h4>
                <span
                  class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wide
                                                          {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                  {{ $isActive ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 font-medium">
                {{ $sem->start_date->format('M Y') }} - {{ $sem->end_date->format('M Y') }}
              </p>
            </button>
          @empty
            <div class="p-8 text-center text-sm text-gray-500">
              No semesters recorded yet.
            </div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="w-full lg:w-2/3 {{ $this->selectedId ? 'block' : 'hidden lg:block' }}">
      @if($this->selectedSemester)
        @php
          $isSelectedActive = $this->selectedSemester->start_date->lte(now()->startOfDay()) && $this->selectedSemester->end_date->gte(now()->startOfDay());
        @endphp

        <button wire:click="clearSelection"
          class="lg:hidden flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-gray-900 mb-4 transition-colors">
          <x-heroicon-m-arrow-left class="w-5 h-5" />
          Back to Semesters
        </button>

        <div class="space-y-6">
          <div
            class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sm:p-6 flex flex-col sm:flex-row sm:items-start lg:items-center justify-between gap-4">
            <div class="space-y-2">
              <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight">{{ $this->selectedSemester->name }}
                </h2>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide {{ $isSelectedActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                  {{ $isSelectedActive ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500">
                <x-heroicon-o-calendar class="w-4 h-4 flex-shrink-0" />
                <span>
                  {{ $this->selectedSemester->start_date->format('F j, Y') }} —
                  {{ $this->selectedSemester->end_date->format('F j, Y') }}
                </span>
              </div>
            </div>
            <div class="w-full sm:w-auto flex-shrink-0">
              <x-filament::button color="gray" variant="outline" wire:click="mountAction('editAction')"
                icon="heroicon-m-pencil-square" class="w-full">
                Edit Details
              </x-filament::button>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div
              class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50/30">
              <h3 class="text-sm font-semibold text-gray-900">Assigned Master Rates</h3>
              <x-filament::button size="sm" wire:click="mountAction('syncRatesAction')" icon="heroicon-m-currency-dollar"
                class="bg-blue-600 hover:bg-blue-700 text-white w-full sm:w-auto">
                Add Rates
              </x-filament::button>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left min-w-[500px]">
                <thead
                  class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                  <tr>
                    <th class="px-5 py-4 whitespace-nowrap">Description</th>
                    <th class="px-5 py-4 whitespace-nowrap">Type</th>
                    <th class="px-5 py-4 text-right whitespace-nowrap">Amount</th>
                    <th class="px-5 py-4 text-center whitespace-nowrap">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  @forelse($this->selectedSemester->rates as $rate)
                    <tr class="hover:bg-gray-50/80 transition-colors group">
                      <td class="px-5 py-4 font-medium text-gray-900 whitespace-nowrap">{{ $rate->name }}</td>
                      <td class="px-5 py-4 whitespace-nowrap">
                        <span
                          class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-600 text-[10px] font-medium tracking-wide">
                          {{ $rate->type->getLabel() }}
                        </span>
                      </td>
                      <td class="px-5 py-4 text-right font-bold text-gray-900 whitespace-nowrap">
                        ₱{{ number_format($rate->amount, 2) }}
                      </td>
                      <td class="px-5 py-4 text-center whitespace-nowrap">
                        <button type="button" title="Remove Rate"
                          wire:click="mountAction('removeRateAction', { rate: {{ $rate->id }} })"
                          class="text-red-600 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-md transition-colors inline-flex items-center justify-center">
                          <x-heroicon-o-trash class="w-5 h-5" />
                        </button>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="4" class="px-5 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                          <x-heroicon-o-document-plus class="h-10 w-10 text-gray-300 mb-3" />
                          <p class="text-sm font-medium">No master rates assigned yet.</p>
                          <p class="text-xs mt-1">Tap "Add Rates" to sync fees.</p>
                        </div>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      @else
        <div
          class="hidden lg:flex h-full min-h-[400px] items-center justify-center bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-12">
          <div class="text-center text-gray-500">
            <x-heroicon-o-calendar class="mx-auto h-12 w-12 text-gray-300 mb-4" />
            <p class="text-base font-medium text-gray-900">No Semester Selected</p>
            <p class="text-sm mt-1">Select an academic term from the sidebar to manage its details.</p>
          </div>
        </div>
      @endif
    </div>
  </div>

  <x-filament-actions::modals />
</div>

<style>
  .custom-scrollbar::-webkit-scrollbar {
    width: 6px;
  }

  .custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
  }

  .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
  }

  .custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background: #d1d5db;
  }
</style>
