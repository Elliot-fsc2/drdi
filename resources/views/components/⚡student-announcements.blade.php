<?php

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Component;

new
    #[Lazy]
    class extends Component {
    public Collection $announcements;

    public function mount(): void
    {
        $user = auth()->user()->profileable;

        $sectionIds = $user->sections()
            ->active()
            ->pluck('sections.id');

        $this->announcements = Post::where(function ($q) use ($sectionIds) {
            $q->where('target_type', PostType::STUDENTS)
                ->orWhere(function ($q) use ($sectionIds) {
                    $q->where('target_type', PostType::SECTIONS)
                        ->whereHas('sections', fn ($q) => $q->whereIn('sections.id', $sectionIds));
                });
        })
            ->with('author', 'sections')
            ->latest()
            ->take(10)
            ->get();
    }
};
?>

@placeholder
<div class="space-y-4">
    @for ($i = 0; $i < 3; $i++)
        <div class="w-full rounded-2xl border border-slate-200/80 bg-white px-5 pt-5 pb-4 shadow-xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 shrink-0 rounded-full bg-slate-200 animate-pulse"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-32 rounded bg-slate-200 animate-pulse"></div>
                    <div class="h-3 w-20 rounded bg-slate-100 animate-pulse"></div>
                    <div class="h-3 w-16 rounded bg-slate-100 animate-pulse"></div>
                </div>
                <div class="h-6 w-16 rounded-lg bg-slate-200 animate-pulse"></div>
            </div>
            <div class="space-y-2">
                <div class="h-5 w-3/4 rounded bg-slate-200 animate-pulse"></div>
                <div class="space-y-1.5">
                    <div class="h-3 w-full rounded bg-slate-100 animate-pulse"></div>
                    <div class="h-3 w-5/6 rounded bg-slate-100 animate-pulse"></div>
                    <div class="h-3 w-2/3 rounded bg-slate-100 animate-pulse"></div>
                </div>
                <div class="h-48 w-full rounded-xl bg-slate-100 animate-pulse"></div>
            </div>
        </div>
    @endfor
</div>
@endplaceholder

<div>
    @if ($announcements->isNotEmpty())
        <div class="space-y-4">
            @foreach ($announcements as $announcement)
                <livewire-post :post="$announcement" defer />
            @endforeach
        </div>
    @endif
</div>
