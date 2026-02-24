<div class="space-y-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900">{{ $title ?? 'Research Proposal' }}</h3>
            <p class="text-sm text-slate-500 mt-1">Submitted: {{ $submitted_date ?? '' }}</p>
        </div>
        @php
            $statusValue = strtolower($status ?? 'pending');
            $badgeColor = match($statusValue) {
                'approved' => 'success',
                'pending', 'under review' => 'warning',
                'rejected' => 'danger',
                default => 'gray',
            };
            $badgeLabel = match($statusValue) {
                'approved' => 'Approved',
                'pending' => 'Pending',
                'rejected' => 'Rejected',
                default => ucfirst($statusValue),
            };
        @endphp
        <x-filament::badge :color="$badgeColor" class="flex-shrink-0 text-base py-1 px-3">
            {{ $badgeLabel }}
        </x-filament::badge>
    </div>

    <div class="bg-slate-50 rounded-lg p-4 border border-slate-100">
        <h4 class="text-sm font-semibold text-slate-700 mb-2">Description / Abstract</h4>
        <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $description ?? '' }}</p>
    </div>
</div>
