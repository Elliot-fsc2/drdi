<?php

use App\Models\Group;
use App\Models\Instructor;
use App\Enums\InstructorRole;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;

new
    #[Title('Groups')]
    class extends Component {

    #[Url]
    public string $tab = 'my_groups';

    #[Computed]
    public function routePrefix(): string
    {
        $user = auth()->user();
        $isRDO = $user->profileable_type === \App\Models\Instructor::class
            && $user->profileable?->role === \App\Enums\InstructorRole::RDO;

        return $isRDO ? 'rdo' : 'instructor';
    }

    #[Computed]
    public function groupsAssigned()
    {
        return Group::with(['section', 'members', 'leader'])
            ->withCount('members')
            ->whereRelation('personnel', 'instructor_id', auth()->user()->profileable->id)
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'section_id' => $group->section_id,
                    'title' => $group->name,
                    'leader' => $group->leader->full_name,
                    'members_count' => $group->members_count,
                ];
            });
    }
    #[Computed]
    public function groups()
    {
        return Group::with(['section', 'members', 'leader'])
            ->withCount('members')
            ->whereHas('section', fn($q) => $q->active())
            ->whereRelation('section', 'instructor_id', auth()->user()->profileable->id)
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'section_id' => $group->section_id,
                    'title' => $group->name,
                    'leader' => $group->leader->full_name,
                    'members_count' => $group->members_count,
                ];
            });
    }

    public function mount()
    {
    }

};
?>

<div class="p-3 lg:p-3 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-900">Groups</h1>
            <p class="text-slate-600 mt-1">Manage your research groups and assigned groups</p>
        </div>

        <!-- Tabs Section -->
        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
            <div class="border-b border-slate-200 px-4">
                <div class="flex gap-6">
                    <a href="?tab=my_groups" wire:navigate
                        class="py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $tab === 'my_groups' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                        My Groups
                    </a>

                    <a href="?tab=assigned_groups" wire:navigate
                        class="py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $tab === 'assigned_groups' ? 'border-blue-600 text-blue-600' : 'border-transparent text-slate-600 hover:text-slate-900' }}">
                        Groups Assigned
                    </a>
                </div>
            </div>

            <!-- Cards Section -->
            <div class="p-4 bg-slate-50/50 min-h-[500px]">
                @if($tab === 'my_groups')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->groups as $group)
                            <a href="{{ route($this->routePrefix . '.classes.group.view', ['section' => $group['section_id'], 'group' => $group['id']]) }}"
                                wire:navigate
                                class="block border border-slate-200 rounded-lg p-5 hover:border-blue-400 hover:shadow-md transition-all bg-white group">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3
                                            class="font-semibold text-lg text-slate-900 group-hover:text-blue-600 transition-colors mb-1">
                                            {{ $group['title'] }}
                                        </h3>
                                        <p class="text-xs text-slate-500">
                                            Led by {{ $group['leader'] }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-sm text-slate-500">
                                        <x-heroicon-o-users class="h-4 w-4" />
                                        <span>{{ $group['members_count'] }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if($tab === 'assigned_groups')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($this->groupsAssigned as $group)
                            <a href="{{ route($this->routePrefix . '.groups.assigned.view', $group['id']) }}" wire:navigate
                                class="block border border-slate-200 rounded-lg p-5 hover:border-blue-400 hover:shadow-md transition-all bg-white group">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3
                                            class="font-semibold text-lg text-slate-900 group-hover:text-blue-600 transition-colors mb-1">
                                            {{ $group['title'] }}
                                        </h3>
                                        <p class="text-xs text-slate-500">
                                            Led by {{ $group['leader'] }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-sm text-slate-500">
                                        <x-heroicon-o-users class="h-4 w-4" />
                                        <span>{{ $group['members_count'] }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>