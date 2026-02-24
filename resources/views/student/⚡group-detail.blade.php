<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Group Details')]
class extends Component
{
    public $user;

    public function mount()
    {
        $this->user = auth()->user()->load(
            'profileable.sections.program',
            'profileable.sections.semester'
        );
    }

    #[Computed]
    public function section()
    {
        return $this->user->profileable->sections()
            ->active()
            ->first();
    }

    #[Computed]
    public function group()
    {
        $section = $this->section();

        if (! $section) {
            return null;
        }

        return $this->user->profileable->groups()
            ->with([
                'members' => function ($q) {
                    $q->orderBy('last_name');
                },
                'leader',
                'section.instructor',
                'section.program',
                'section.semester',
                'finalTitle',
            ])
            ->where('groups.section_id', $section->id)
            ->first();
    }
};
?>

@assets
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

<div class="p-3 sm:p-4 lg:p-6 bg-slate-50 min-h-screen">
    <div class="max-w-5xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-4 sm:mb-6 border-b border-slate-200 pb-3 sm:pb-4 gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Group Details</h1>
                <p class="text-slate-500 text-xs sm:text-sm font-medium mt-1">
                    Overview of your group, members, and essential information
                </p>
            </div>
            @if($this->group())
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1 text-sm font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                    Active Group
                </span>
            </div>
            @endif
        </div>

        @if(! $this->group())
            <div class="bg-white border border-slate-200 rounded-lg p-12 sm:p-20 text-center shadow-sm">
                <x-heroicon-o-user-group class="h-12 w-12 sm:h-16 sm:w-16 mx-auto text-slate-300 mb-4" />
                <h3 class="text-lg font-medium text-slate-900">No active group</h3>
                <p class="text-slate-500 text-sm sm:text-base mt-1">You are not assigned to any active group for the current semester.</p>
            </div>
        @else
            <!-- Group Core Information Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-5 flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">{{ $this->group()->name }}</h2>
                            <div class="flex items-center gap-2 mt-1 text-sm text-slate-500">
                                <span>{{ $this->group()->section->name ?? 'Unknown Section' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ count($this->group()->members) }} Members</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-100 bg-white">
                    <div class="p-6 flex flex-col gap-1 hover:bg-slate-50/50 transition-colors">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <x-heroicon-o-academic-cap class="w-4 h-4 text-slate-400" /> Instructor
                        </span>
                        <span class="text-slate-900 font-medium text-base mt-1">
                            {{ $this->group()->section?->instructor?->full_name ?? 'Not Assigned' }}
                        </span>
                    </div>
                    <div class="p-6 flex flex-col gap-1 hover:bg-slate-50/50 transition-colors">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <x-heroicon-o-book-open class="w-4 h-4 text-slate-400" /> Program
                        </span>
                        <span class="text-slate-900 font-medium text-base mt-1">
                            {{ $this->group()->section?->program?->name ?? 'Not Set' }}
                        </span>
                    </div>
                    <div class="p-6 flex flex-col gap-1 hover:bg-slate-50/50 transition-colors">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" /> Semester
                        </span>
                        <span class="text-slate-900 font-medium text-base mt-1">
                            {{ $this->group()->section?->semester?->name ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Approved Title Highlight (If exists) -->
            @if($this->group()->finalTitle)
            <div class="relative overflow-hidden bg-white border border-slate-200 rounded-xl shadow-sm">
                <!-- Decorative left border -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-green-500"></div>
                
                <div class="px-6 py-5 sm:p-6 bg-linear-to-r from-green-50/50 to-white">
                    <div class="flex items-center gap-2 mb-3">
                        <x-heroicon-s-star class="h-5 w-5 text-green-500" />
                        <h3 class="font-bold text-slate-900 text-sm uppercase tracking-wider">Final Approved Title</h3>
                    </div>
                    
                    <h4 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight mb-2">
                        {{ $this->group()->finalTitle->title }}
                    </h4>
                    
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                        {{ $this->group()->finalTitle->description }}
                    </p>
                </div>
            </div>
            @endif

            <!-- Members List -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 flex items-center gap-2">
                        <x-heroicon-o-users class="h-5 w-5 text-slate-500" />
                        Group Members
                    </h3>
                </div>
                
                <div class="divide-y divide-slate-100">
                    @foreach($this->group()->members as $member)
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50 transition-colors duration-150">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 text-sm font-bold shadow-sm">
                                    {{ collect(explode(' ', $member->full_name))->map(fn($n) => strtoupper($n[0] ?? ''))->take(2)->implode('') }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-900">{{ $member->full_name }}</span>
                                    <span class="text-xs text-slate-500 mt-0.5">{{ $member->student_number ?? 'Student' }}</span>
                                </div>
                            </div>
                            
                            @if($member->id === $this->group()->leader_id)
                                <div class="flex mt-2 sm:mt-0">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        <x-heroicon-s-shield-check class="h-3.5 w-3.5" /> Leader
                                    </span>
                                </div>
                            @elseif($member->id === auth()->user()->profileable->id)
                                <div class="flex mt-2 sm:mt-0">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/10">
                                        You
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="flex items-center justify-center mt-6">
                 <p class="text-xs text-slate-400">If you need to update group information, please contact your instructor.</p>
            </div>
        @endif
    </div>
</div>