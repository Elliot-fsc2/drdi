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

new #[Title('Semester Tracking')] class extends Component implements HasActions, HasSchemas {
    use InteractsWithActions;
    use InteractsWithSchemas;

    public ?int $selectedId = null;

    public function mount()
    {
        $this->selectedId = Semester::active()->latest('start_date')->first()?->id ?? Semester::latest('start_date')->first()?->id;
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
        if (!$this->selectedId) {
            return null;
        }
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
            ->form([TextInput::make('name')->label('Semester Name')->required()->placeholder('e.g. 2nd Semester AY 2024-2025'), DatePicker::make('start_date')->label('Start Date')->required(), DatePicker::make('end_date')->label('End Date')->required()])
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
            ->form([TextInput::make('name')->label('Semester Name')->required(), DatePicker::make('start_date')->label('Start Date')->required(), DatePicker::make('end_date')->label('End Date')->required()])
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
                        $assignedIds = DB::table('semester_rates')->where('semester_id', $this->selectedId)->pluck('thesis_rate_id');

                        return ThesisRate::whereNotIn('id', $assignedIds)->get()->mapWithKeys(
                            fn($rate) => [
                                $rate->id => "{$rate->name} (₱" . number_format($rate->amount, 2) . ')',
                            ],
                        );
                    })
                    ->columns(2)
                    ->required(),
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
                if (!$rateId || !$this->selectedId) {
                    return;
                }

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

<div class="min-h-screen relative" style="background: #F8FAFC">

    {{-- ── Ambient background glows ────────────────────── --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-32 -right-32 w-[500px] h-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 w-[400px] h-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-12">

        {{-- ── Page Header ─────────────────────────────── --}}
        <div class="mb-8 sm:mb-10">

            <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Academic Terms
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        Academic
                        <span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                            Semesters
                        </span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        Manage academic terms and synchronize global thesis rates.
                    </p>
                </div>

                <button wire:click="mountAction('createAction')"
                    class="group inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                    style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 16px rgba(0,82,255,0.35)">
                    <x-heroicon-m-plus class="h-4 w-4" />
                    Add Semester
                    <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Two-column layout ────────────────────────── --}}
        <div class="flex flex-col lg:flex-row gap-6 relative">

            {{-- ── Semester List Sidebar ────────────────── --}}
            <div class="w-full lg:w-[300px] flex-shrink-0 {{ $this->selectedId ? 'hidden lg:flex' : 'flex' }} flex-col">
                <div class="overflow-hidden rounded-2xl border flex flex-col"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); height: 75vh; max-height: 640px">

                    <div class="px-5 py-4 shrink-0" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <span
                            style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                            All Terms
                        </span>
                    </div>

                    <div class="flex-1 overflow-y-auto divide-y custom-scrollbar" style="border-color: #F8FAFC">
                        @forelse ($this->semesters as $sem)
                            @php
                                $isActive =
                                    $sem->start_date->lte(now()->startOfDay()) &&
                                    $sem->end_date->gte(now()->startOfDay());
                                $isSelected = $this->selectedId === $sem->id;
                            @endphp
                            <button wire:click="selectSemester({{ $sem->id }})"
                                class="w-full text-left px-5 py-4 transition-all duration-150 focus:outline-none flex flex-col gap-1.5"
                                style="{{ $isSelected ? 'background: rgba(0,82,255,0.04); border-left: 3px solid #0052FF' : 'border-left: 3px solid transparent' }}">
                                <div class="flex items-start justify-between w-full gap-2">
                                    <h4 class="text-sm font-semibold leading-snug pr-1"
                                        style="color: {{ $isSelected ? '#0052FF' : '#0F172A' }}">
                                        {{ $sem->name }}
                                    </h4>
                                    <span
                                        class="shrink-0 inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                                        style="{{ $isActive ? 'background: #ECFDF5; color: #059669' : 'background: #F1F5F9; color: #64748B' }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <p style="font-family: 'JetBrains Mono', monospace; font-size: 10px; color: #94A3B8">
                                    {{ $sem->start_date->format('M Y') }} — {{ $sem->end_date->format('M Y') }}
                                </p>
                            </button>
                        @empty
                            <div class="p-8 text-center text-sm" style="color: #94A3B8">
                                No semesters recorded yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ── Detail Panel ─────────────────────────── --}}
            <div class="flex-1 min-w-0 {{ $this->selectedId ? 'block' : 'hidden lg:block' }}">
                @if ($this->selectedSemester)
                    @php
                        $isSelectedActive =
                            $this->selectedSemester->start_date->lte(now()->startOfDay()) &&
                            $this->selectedSemester->end_date->gte(now()->startOfDay());
                    @endphp

                    {{-- Mobile back button --}}
                    <button wire:click="clearSelection"
                        class="lg:hidden flex items-center gap-2 text-sm font-medium mb-5 transition-colors duration-150"
                        style="color: #64748B">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        Back to Semesters
                    </button>

                    <div class="space-y-5">

                        {{-- Semester info card --}}
                        <div class="rounded-2xl border p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                            <div class="space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <h2 class="text-lg font-bold leading-tight" style="color: #0F172A">
                                        {{ $this->selectedSemester->name }}
                                    </h2>
                                    <span
                                        class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide"
                                        style="{{ $isSelectedActive ? 'background: #ECFDF5; color: #059669' : 'background: #F1F5F9; color: #64748B' }}">
                                        {{ $isSelectedActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 text-xs" style="color: #64748B">
                                    <x-heroicon-o-calendar class="w-4 h-4 shrink-0" />
                                    <span>
                                        {{ $this->selectedSemester->start_date->format('F j, Y') }} —
                                        {{ $this->selectedSemester->end_date->format('F j, Y') }}
                                    </span>
                                </div>
                            </div>
                            <button wire:click="mountAction('editAction')"
                                class="shrink-0 inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all duration-150 hover:-translate-y-px"
                                style="border: 1px solid #E2E8F0; color: #374151; background: white; box-shadow: 0 1px 2px rgba(0,0,0,0.04)">
                                <x-heroicon-m-pencil-square class="h-4 w-4" />
                                Edit Details
                            </button>
                        </div>

                        {{-- Assigned rates card --}}
                        <div class="overflow-hidden rounded-2xl border"
                            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                                style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                                <span
                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">
                                    Assigned Master Rates
                                </span>
                                <button wire:click="mountAction('syncRatesAction')"
                                    class="group inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold text-white transition-all duration-200 hover:-translate-y-px"
                                    style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 3px 10px rgba(0,82,255,0.3)">
                                    <x-heroicon-m-currency-dollar class="h-3.5 w-3.5" />
                                    Add Rates
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full min-w-[480px]">
                                    <thead>
                                        <tr style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                                            <th class="px-6 py-3.5 text-left">
                                                <span
                                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Description</span>
                                            </th>
                                            <th class="px-5 py-3.5 text-left">
                                                <span
                                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Type</span>
                                            </th>
                                            <th class="px-5 py-3.5 text-right">
                                                <span
                                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Amount</span>
                                            </th>
                                            <th class="px-6 py-3.5 text-center">
                                                <span
                                                    style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Action</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($this->selectedSemester->rates as $rate)
                                            <tr class="transition-colors duration-150 hover:bg-[#F5F8FF]"
                                                style="border-bottom: 1px solid #F1F5F9">
                                                <td class="px-6 py-4">
                                                    <span class="text-sm font-semibold"
                                                        style="color: #0F172A">{{ $rate->name }}</span>
                                                </td>
                                                <td class="px-5 py-4">
                                                    <span
                                                        class="inline-flex items-center rounded-lg px-2.5 py-0.5 text-xs font-medium"
                                                        style="background: rgba(0,82,255,0.06); color: #0052FF; border: 1px solid rgba(0,82,255,0.12)">
                                                        {{ $rate->type->getLabel() }}
                                                    </span>
                                                </td>
                                                <td class="px-5 py-4 text-right">
                                                    <span class="text-sm font-bold"
                                                        style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                                                        ₱{{ number_format($rate->amount, 2) }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <button type="button"
                                                        wire:click="mountAction('removeRateAction', { rate: {{ $rate->id }} })"
                                                        class="inline-flex items-center justify-center rounded-lg p-1.5 transition-colors duration-150"
                                                        style="color: #DC2626" title="Remove Rate">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-16 text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                                                            style="background: #F1F5F9">
                                                            <x-heroicon-o-document-plus class="h-7 w-7"
                                                                style="color: #CBD5E1" />
                                                        </div>
                                                        <p class="text-sm font-semibold mb-1" style="color: #374151">
                                                            No rates assigned yet</p>
                                                        <p class="text-xs" style="color: #94A3B8">Tap "Add Rates" to
                                                            sync fees.</p>
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
                    {{-- Empty state (desktop only) --}}
                    <div class="hidden lg:flex h-full min-h-[400px] items-center justify-center rounded-2xl border-2 border-dashed p-12"
                        style="border-color: #E2E8F0; background: #FAFAFA">
                        <div class="text-center">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
                                style="background: #F1F5F9">
                                <x-heroicon-o-calendar class="h-8 w-8" style="color: #CBD5E1" />
                            </div>
                            <p class="text-base font-semibold mb-1" style="color: #0F172A">No Semester Selected</p>
                            <p class="text-sm max-w-xs" style="color: #64748B; line-height: 1.6">
                                Select an academic term from the sidebar to manage its details.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <x-filament-actions::modals />
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #E2E8F0;
        border-radius: 10px;
    }

    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background: #CBD5E1;
    }
</style>
