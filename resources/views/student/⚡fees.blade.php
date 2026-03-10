<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Fees')] class extends Component {
    #[Computed]
    public function feeData()
    {
        $user = auth()->user();

        $section = $user->profileable->sections()->active()->first();

        if (!$section) {
            return null;
        }

        $group = $user->profileable
            ->groups()
            ->with(['fee', 'members'])
            ->firstWhere('section_id', $section->id);

        if (!$group || !$group->fee) {
            return [
                'has_fee' => false,
                'group_name' => $group ? $group->name : null,
            ];
        }

        $fee = $group->fee;

        $totalFee = $fee->total_merger_amount;
        $memberCount = $group->members->count();
        $perStudentFee = $memberCount > 0 ? $totalFee / $memberCount : 0;

        return [
            'has_fee' => true,
            'group_name' => $group->name,
            'base_fee' => $fee->base_fee,
            'honorarium_total' => $fee->honorarium_total,
            'total_fee' => $totalFee,
            'member_count' => $memberCount,
            'per_student_fee' => $perStudentFee,
        ];
    }
};
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Group Fees</h1>
        <p class="text-gray-600">View the detailed fee breakdown for your active group.</p>
    </div>

    @if ($this->feeData === null)
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500">
            <p>No active section found. Please contact your administrator.</p>
        </div>
    @elseif(!$this->feeData['has_fee'])
        <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500">
            @if ($this->feeData['group_name'])
                <p>No fee records have been assessed for your group ({{ $this->feeData['group_name'] }}) yet.</p>
            @else
                <p>You are not assigned to a group yet.</p>
            @endif
        </div>
    @else
        <!-- Fee Breakdown Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Breakdown Card -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                        Fee Breakdown
                    </h3>
                    <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2 py-1 rounded">
                        {{ $this->feeData['group_name'] }}
                    </span>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Base Fee</p>
                            <p class="text-xs text-gray-500 mt-0.5">Standard group assessment</p>
                        </div>
                        <p class="font-semibold text-gray-900">₱{{ number_format($this->feeData['base_fee'], 2) }}</p>
                    </div>

                    <div class="flex justify-between items-center pb-2">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Honorarium Total</p>
                            <p class="text-xs text-gray-500 mt-0.5">Adviser and panelist fees</p>
                        </div>
                        <p class="font-semibold text-gray-900">
                            ₱{{ number_format($this->feeData['honorarium_total'], 2) }}</p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <p class="text-base font-bold text-gray-900">Total Group Fee</p>
                        <p class="text-lg font-bold text-indigo-600">
                            ₱{{ number_format($this->feeData['total_fee'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Individual Share Card -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 flex flex-col justify-center text-center">
                <div
                    class="inline-flex items-center justify-center w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full mx-auto mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 mb-1">Your Individual Share</h3>
                <p class="text-sm text-gray-500 mb-6">Divided equally among {{ $this->feeData['member_count'] }} members
                </p>

                <div class="py-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                    <p class="text-3xl font-bold text-gray-900">
                        ₱{{ number_format($this->feeData['per_student_fee'], 2) }}</p>
                    <p class="text-xs font-medium text-gray-500 mt-1 uppercase tracking-wider">Per Student</p>
                </div>

                <p class="text-xs text-gray-400 mt-6">* This is an estimate based on the total group fee divided by
                    current regular members of the group.</p>
            </div>
        </div>
    @endif
</div>
