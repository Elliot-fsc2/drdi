<?php

use App\Enums\ThesisRatesType;
use App\Models\ThesisRate;
use App\Services\FeeService;
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

new #[Title('Thesis Rates Management')] class extends Component implements HasActions, HasSchemas {
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
            ->form([TextInput::make('name')->label('Rate Name')->placeholder('e.g., Base Fee, Technical Adviser Fee')->required()->maxLength(255), TextInput::make('amount')->label('Amount (₱)')->numeric()->required()->minValue(0)->prefix('₱')->placeholder('0.00'), Select::make('type')->label('Rate Type')->options(ThesisRatesType::class)->required()->native(false)])
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
            ->fillForm(
                fn(array $arguments): array => [
                    'name' => ThesisRate::find($arguments['rateId'])->name,
                    'amount' => ThesisRate::find($arguments['rateId'])->amount,
                    'type' => ThesisRate::find($arguments['rateId'])->type->value,
                ],
            )
            ->form([TextInput::make('name')->label('Rate Name')->required()->maxLength(255), TextInput::make('amount')->label('Amount (₱)')->numeric()->required()->minValue(0)->prefix('₱'), Select::make('type')->label('Rate Type')->options(ThesisRatesType::class)->required()->native(false)])
            ->successNotificationTitle('Thesis rate updated successfully')
            ->action(function (array $arguments, array $data, FeeService $feeService): void {
                $rate = ThesisRate::find($arguments['rateId']);
                $rate->update($data);

                foreach ($rate->semesters as $semester) {
                    $feeService->updateAllGroupsInSemester($semester);
                }

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
            ->action(function (array $arguments, FeeService $feeService): void {
                $rate = ThesisRate::find($arguments['rateId']);
                $semesters = $rate->semesters;

                $rate->delete();

                foreach ($semesters as $semester) {
                    $feeService->updateAllGroupsInSemester($semester);
                }

                unset($this->rates);
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
                    Fee Management
                </span>
            </div>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
                <div>
                    <h1 class="leading-tight"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                        Thesis
                        <span
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                            Rates
                        </span>
                    </h1>
                    <p class="mt-2 text-sm" style="color: #64748B">
                        Manage base fees and personnel honoraria applied to research groups.
                    </p>
                </div>

                <button wire:click="mountAction('createRate')"
                    class="group inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all duration-200 hover:-translate-y-px active:scale-[0.98]"
                    style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 16px rgba(0,82,255,0.35)">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Add Rate
                    <svg class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- ── Rates Table ──────────────────────────────── --}}
        <div class="overflow-hidden rounded-2xl border"
            style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

            @if ($this->rates->isEmpty())
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-6"
                        style="background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 8px 24px rgba(0,82,255,0.3)">
                        <x-heroicon-o-currency-dollar class="h-10 w-10 text-white" />
                    </div>
                    <h3 class="mb-2"
                        style="font-family: 'Calistoga', Georgia, serif; font-size: 1.5rem; color: #0F172A">
                        No rates configured
                    </h3>
                    <p class="text-sm max-w-xs mb-6" style="color: #64748B; line-height: 1.6">
                        Add your first thesis rate to start calculating group fees.
                    </p>
                    <button wire:click="mountAction('createRate')"
                        class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white"
                        style="background: linear-gradient(135deg, #0052FF 0%, #4D7CFF 100%); box-shadow: 0 4px 16px rgba(0,82,255,0.35)">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Add New Rate
                    </button>
                </div>
            @else
                {{-- ── Mobile Card View ──────────────────── --}}
                <div class="lg:hidden">
                    @foreach ($this->rates->groupBy('type') as $type => $ratesGroup)
                        <div class="p-5" style="border-bottom: 1px solid #F1F5F9">
                            <div class="mb-3">
                                <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-medium"
                                    style="background: rgba(0,82,255,0.06); color: #0052FF; border: 1px solid rgba(0,82,255,0.12)">
                                    {{ ThesisRatesType::from($type)->getLabel() }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                @foreach ($ratesGroup as $rate)
                                    <div class="rounded-xl p-4 transition-colors duration-150 hover:bg-[#F5F8FF]"
                                        style="border: 1px solid #F1F5F9; background: #FAFAFA">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="font-semibold text-sm truncate" style="color: #0F172A">
                                                    {{ $rate->name }}</h3>
                                                <p class="mt-1 font-bold text-lg"
                                                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
                                                    ₱{{ number_format($rate->amount, 2) }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <button
                                                    wire:click="mountAction('editRate', { rateId: {{ $rate->id }} })"
                                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                                                    style="border: 1px solid #E2E8F0; color: #374151; background: white">
                                                    Edit
                                                </button>
                                                <button
                                                    wire:click="mountAction('deleteRate', { rateId: {{ $rate->id }} })"
                                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors duration-150"
                                                    style="border: 1px solid #FEE2E2; color: #DC2626; background: #FFF5F5">
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

                {{-- ── Desktop Table View ─────────────────── --}}
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                                <th class="px-6 py-4 text-left">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Rate</span>
                                </th>
                                <th class="px-5 py-4 text-left">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Type</span>
                                </th>
                                <th class="px-5 py-4 text-right">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Amount</span>
                                </th>
                                <th class="px-6 py-4 text-right">
                                    <span
                                        style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.12em; color: #94A3B8; text-transform: uppercase">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->rates as $rate)
                                <tr class="transition-colors duration-150 hover:bg-[#F5F8FF]"
                                    style="border-bottom: 1px solid #F1F5F9">
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-semibold" style="color: #0F172A">
                                            {{ $rate->name }}
                                        </span>
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
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                wire:click="mountAction('editRate', { rateId: {{ $rate->id }} })"
                                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 hover:-translate-y-px"
                                                style="border: 1px solid #E2E8F0; color: #374151; background: white">
                                                Edit
                                            </button>
                                            <button
                                                wire:click="mountAction('deleteRate', { rateId: {{ $rate->id }} })"
                                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 hover:-translate-y-px"
                                                style="border: 1px solid #FEE2E2; color: #DC2626; background: #FFF5F5">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            @endif
        </div>

        {{-- ── Summary Cards ────────────────────────────── --}}
        @if ($this->rates->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-6">

                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="text-sm font-semibold" style="color: #0F172A">Fixed per group</h4>
                                <p style="font-size: 11px; color: #94A3B8">Base fees charged per research group</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-2">
                        @foreach ($this->rates->where('type.value', 'fixed_per_group') as $rate)
                            <div class="flex items-center justify-between text-sm">
                                <span style="color: #64748B">{{ $rate->name }}</span>
                                <span class="font-semibold" style="color: #0F172A">
                                    ₱{{ number_format($rate->amount, 2) }}
                                </span>
                            </div>
                        @endforeach
                        @if ($this->rates->where('type.value', 'fixed_per_group')->isEmpty())
                            <p class="text-sm italic" style="color: #94A3B8">No fixed rates configured</p>
                        @endif
                    </div>
                </div>
                <div class="rounded-2xl border overflow-hidden"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="px-5 py-4" style="border-bottom: 1px solid #F1F5F9; background: #FAFAFA">
                        <div class="flex items-center gap-3">
                            <div>
                                <h4 class="text-sm font-semibold" style="color: #0F172A">Per personnel</h4>
                                <p style="font-size: 11px; color: #94A3B8">Honoraria for assigned personnel</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 space-y-2">
                        @foreach ($this->rates->where('type.value', 'per_personnel') as $rate)
                            <div class="flex items-center justify-between text-sm">
                                <span style="color: #64748B">{{ $rate->name }}</span>
                                <span class="font-semibold" style="color: #0F172A">
                                    ₱{{ number_format($rate->amount, 2) }}
                                </span>
                            </div>
                        @endforeach
                        @if ($this->rates->where('type.value', 'per_personnel')->isEmpty())
                            <p class="text-sm italic" style="color: #94A3B8">No per-personnel rates configured</p>
                        @endif
                    </div>
                </div>

            </div>
        @endif

    </div>

    <x-filament-actions::modals />
</div>
