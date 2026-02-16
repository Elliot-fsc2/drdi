<?php

use App\Enums\ThesisRatesType;
use App\Models\ThesisRate;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Thesis Rates Management')]
  class extends Component implements HasActions, HasSchemas {
  use InteractsWithActions;
  use InteractsWithSchemas;

  #[Computed]
  public function rates()
  {
    return ThesisRate::orderBy('type')->orderBy('name')->get();
  }

  public function createRateAction(): Action
  {
    return Action::make('createRate')
      ->modalWidth('2xl')
      ->modalCloseButton(false)
      ->label('Add New Rate')
      ->icon(Heroicon::Plus)
      ->modalHeading('Create Thesis Rate')
      ->form([
        TextInput::make('name')
          ->label('Rate Name')
          ->placeholder('e.g., Base Fee, Technical Adviser Fee')
          ->required()
          ->maxLength(255),

        TextInput::make('amount')
          ->label('Amount (₱)')
          ->numeric()
          ->required()
          ->minValue(0)
          ->prefix('₱')
          ->placeholder('0.00'),

        Select::make('type')
          ->label('Rate Type')
          ->options(ThesisRatesType::class)
          ->required()
          ->native(false),
      ])
      ->successNotificationTitle('Thesis rate created successfully')
      ->action(function (array $data): void {
        ThesisRate::create($data);
        unset($this->rates);
      });
  }

  public function editRateAction(): Action
  {
    return Action::make('editRate')
      ->modalWidth('2xl')
      ->modalCloseButton(false)
      ->icon(Heroicon::PencilSquare)
      ->fillForm(fn(array $arguments): array => [
        'name' => ThesisRate::find($arguments['rateId'])->name,
        'amount' => ThesisRate::find($arguments['rateId'])->amount,
        'type' => ThesisRate::find($arguments['rateId'])->type->value,
      ])
      ->form([
        TextInput::make('name')
          ->label('Rate Name')
          ->required()
          ->maxLength(255),

        TextInput::make('amount')
          ->label('Amount (₱)')
          ->numeric()
          ->required()
          ->minValue(0)
          ->prefix('₱'),

        Select::make('type')
          ->label('Rate Type')
          ->options(ThesisRatesType::class)
          ->required()
          ->native(false),
      ])
      ->successNotificationTitle('Thesis rate updated successfully')
      ->action(function (array $arguments, array $data): void {
        ThesisRate::find($arguments['rateId'])->update($data);
        unset($this->rates);
      });
  }

  public function deleteRateAction(): Action
  {
    return Action::make('deleteRate')
      ->requiresConfirmation()
      ->modalCloseButton(false)
      ->modalHeading('Delete Thesis Rate')
      ->modalDescription('Are you sure you want to delete this rate? This action cannot be undone.')
      ->modalSubmitActionLabel('Yes, Delete')
      ->color('danger')
      ->icon(Heroicon::Trash)
      ->successNotificationTitle('Thesis rate deleted successfully')
      ->action(function (array $arguments): void {
        ThesisRate::find($arguments['rateId'])->delete();
        unset($this->rates);
      });
  }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<x-slot name="title">
  Thesis Rates Management
</x-slot>

<div class="p-3 lg:p-3 bg-slate-50">
  <div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-2 text-sm text-slate-600 mb-3">
        <a href="{{ route('rdo.home') }}" wire:navigate class="hover:text-blue-600">Dashboard</a>
        <span>/</span>
        <span class="text-slate-900 font-medium">Thesis Rates</span>
      </div>

      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-semibold text-slate-900">Thesis rates</h1>
          <p class="text-sm text-slate-500 mt-1">Manage base fees and personnel honoraria.</p>
        </div>

        <div class="flex gap-2 items-center">
          <button wire:click="mountAction('createRate')"
            class="inline-flex items-center gap-2 px-3 py-1.5 border border-slate-200 bg-white text-slate-700 text-sm rounded-md hover:shadow-sm transition">
            <x-heroicon-o-plus class="h-4 w-4 text-slate-600" />
            Add rate
          </button>
        </div>
      </div>
    </div>

    <!-- Rates Table -->
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
      @if($this->rates->isEmpty())
        <div class="text-center py-16">
          <x-heroicon-o-currency-dollar class="h-16 w-16 mx-auto text-slate-300 mb-4" />
          <p class="text-slate-500 text-base font-medium mb-1">No thesis rates configured</p>
          <p class="text-slate-400 text-sm mb-4">Add your first rate to get started</p>
          <x-filament::button wire:click="mountAction('createRate')" class="bg-blue-600 text-white">
            <x-heroicon-o-plus class="h-4 w-4 mr-1" />
            Add New Rate
          </x-filament::button>
        </div>
      @else
        {{-- Mobile Card View --}}
        <div class="lg:hidden space-y-4">
          @foreach($this->rates->groupBy('type') as $type => $ratesGroup)
            <div class="p-3">
              <div class="mb-2">
                <span
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-slate-700">
                  {{ ThesisRatesType::from($type)->getLabel() }}
                </span>
              </div>

              <div class="space-y-3">
                @foreach($ratesGroup as $rate)
                  <div class="bg-white rounded-md p-3 border border-slate-100 shadow-sm">
                    <div class="flex items-start justify-between mb-2 gap-4">
                      <div class="flex-1 min-w-0">
                        <h3 class="font-medium text-slate-900 truncate">{{ $rate->name }}</h3>
                        <p class="text-lg font-semibold text-slate-900 mt-1">₱{{ number_format($rate->amount, 2) }}</p>
                      </div>
                      <div class="flex items-center gap-2">
                        <button wire:click="mountAction('editRate', { rateId: {{ $rate->id }} })"
                          class="text-sm text-slate-600 px-2 py-1 border border-slate-100 rounded hover:bg-slate-50 transition">
                          Edit
                        </button>
                        <button wire:click="mountAction('deleteRate', { rateId: {{ $rate->id }} })"
                          class="text-sm text-red-600 px-2 py-1 border border-red-50 rounded hover:bg-red-50 transition">
                          Delete
                        </button>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden lg:block overflow-x-auto">
          <table class="w-full">
            <thead class="bg-white border-b border-slate-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Rate</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                <th class="px-6 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @foreach($this->rates as $rate)
                <tr class="hover:bg-slate-50 transition-colors">
                  <td class="px-6 py-3">
                    <div class="text-sm font-medium text-slate-900">{{ $rate->name }}</div>
                  </td>
                  <td class="px-6 py-3">
                    <span
                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-gray-100 text-slate-700">{{ $rate->type->getLabel() }}</span>
                  </td>
                  <td class="px-6 py-3 text-right">
                    <div class="text-sm font-semibold text-slate-900">₱{{ number_format($rate->amount, 2) }}</div>
                  </td>
                  <td class="px-6 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button wire:click="mountAction('editRate', { rateId: {{ $rate->id }} })"
                        class="text-sm text-slate-600 px-2 py-1 border border-slate-100 rounded hover:bg-slate-50 transition">Edit</button>
                      <button wire:click="mountAction('deleteRate', { rateId: {{ $rate->id }} })"
                        class="text-sm text-red-600 px-2 py-1 border border-red-50 rounded hover:bg-red-50 transition">Delete</button>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>

    <!-- Summary Cards -->
    @if($this->rates->isNotEmpty())
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="bg-white border border-slate-100 rounded-md p-5 shadow-sm">
          <div class="flex items-start gap-3 mb-3">
            <div class="p-2 bg-gray-50 rounded">
              <x-heroicon-o-user-group class="h-5 w-5 text-slate-700" />
            </div>
            <div>
              <h4 class="text-sm font-medium text-slate-900">Fixed per group</h4>
              <div class="text-xs text-slate-500">Base fees charged per research group</div>
            </div>
          </div>

          <div class="space-y-2">
            @foreach($this->rates->where('type.value', 'fixed_per_group') as $rate)
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600 truncate">{{ $rate->name }}</span>
                <span class="font-medium text-slate-900">₱{{ number_format($rate->amount, 2) }}</span>
              </div>
            @endforeach
            @if($this->rates->where('type.value', 'fixed_per_group')->isEmpty())
              <div class="text-sm text-slate-400 italic">No fixed rates configured</div>
            @endif
          </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-md p-5 shadow-sm">
          <div class="flex items-start gap-3 mb-3">
            <div class="p-2 bg-gray-50 rounded">
              <x-heroicon-o-users class="h-5 w-5 text-slate-700" />
            </div>
            <div>
              <h4 class="text-sm font-medium text-slate-900">Per personnel</h4>
              <div class="text-xs text-slate-500">Honoraria for assigned personnel</div>
            </div>
          </div>

          <div class="space-y-2">
            @foreach($this->rates->where('type.value', 'per_personnel') as $rate)
              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-600 truncate">{{ $rate->name }}</span>
                <span class="font-medium text-slate-900">₱{{ number_format($rate->amount, 2) }}</span>
              </div>
            @endforeach
            @if($this->rates->where('type.value', 'per_personnel')->isEmpty())
              <div class="text-sm text-slate-400 italic">No per-personnel rates configured</div>
            @endif
          </div>
        </div>
      </div>
    @endif
  </div>
  <x-filament-actions::modals />
</div>