<?php

use App\Models\Consultation;
use App\Models\Proposal;
use App\Enums\ProposalStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')]
  class extends Component {
  public $user;

  public function mount()
  {
    $this->user = auth()->user()->load(
      'profileable.sections.program',
      'profileable.sections.instructor',
      'profileable.sections.semester'
    );
  }

  #[Computed]
  public function sections()
  {
    $section = $this->user->profileable->sections()
      ->withCount('students', 'groups')
      ->active()
      ->first();

    if (!$section) {
      return null;
    }

    return [
      'id' => $section->id,
      'name' => $section->name,
      'program_name' => $section->program->name ?? 'N/A',
      'instructor_name' => $section->instructor->full_name ?? 'No instructor assigned',
      'semester_name' => $section->semester->name ?? 'N/A',
      'students_count' => $section->students_count,
      'groups_count' => $section->groups_count,
    ];
  }

  #[Computed]
  public function group()
  {
    $sections = $this->sections();

    $sectionId = $sections['id'] ?? null;

    if (!$sectionId) {
      return null;
    }

    $group = $this->user->profileable->groups()
      ->with('members', 'section')
      ->firstWhere('section_id', $sectionId);

    if ($group) {
      return [
        'id' => $group->id,
        'name' => $group->name,
        'section_id' => $group->section->name ?? 'N/A',
        'leader_id' => $group->leader_id,
        'members' => $group->members->map(fn($member) => [
          'id' => $member->id,
          'name' => $member->full_name,
        ])->toArray(),
      ];
    }

    return [
      'id' => null,
      'name' => 'No group assigned',
      'section_id' => $sectionId,
      'leader_id' => null,
      'members' => [],
    ];
  }

  #[Computed]
  public function proposals()
  {
    $group = $this->group();

    // Fixed: Check if group exists and has an id
    if (!$group || !$group['id']) {
      return [];
    }

    $proposals = Proposal::where('group_id', $group['id'])->get();

    return $proposals->map(fn($proposal) => [
      'id' => $proposal->id,
      'title' => $proposal->title,
      'description' => $proposal->description,
      'group_id' => $proposal->group_id,
      'submitted_by' => $proposal->submitted_by,
      'status' => $proposal->status,
      'feedback' => $proposal->feedback,
    ])->toArray();
  }

  #[Computed]
  public function consultations()
  {
    $group = $this->group();

    // Fixed: Check if group exists and has an id
    if (!$group || !$group['id']) {
      return [];
    }

    return Consultation::where('group_id', $group['id'])
      ->orderBy('scheduled_at', 'asc')
      ->get()
      ->map(fn($consultation) => [
        'id' => $consultation->id,
        'group_id' => $consultation->group_id,
        'instructor_id' => $consultation->instructor_id,
        'scheduled_at' => $consultation->scheduled_at,
        'status' => $consultation->status,
        'remarks' => $consultation->remarks,
        'type' => $consultation->type,
      ])->toArray();
  }

  // Added: Missing formatTime method
  public function formatTime($datetime): string
  {
    return \Carbon\Carbon::parse($datetime)->format('M d, Y - g:i A');
  }

  public function getStatusBadgeClass(string $status): string
  {
    return match ($status) {
      ProposalStatus::APPROVED->value => 'bg-green-50 text-green-700',
      'revision' => 'bg-orange-50 text-orange-700',
      ProposalStatus::PENDING->value, 'scheduled' => 'bg-yellow-50 text-yellow-700',
      ProposalStatus::REJECTED->value => 'bg-red-50 text-red-700',
      'completed' => 'bg-green-50 text-green-700',
      default => 'bg-gray-50 text-gray-700',
    };
  }

  public function getStatusLabel(string $status): string
  {
    return match ($status) {
      'revision' => 'Revision Needed',
      'scheduled' => 'Scheduled',
      ProposalStatus::APPROVED->value => 'Approved',
      ProposalStatus::REJECTED->value => 'Rejected',
      ProposalStatus::PENDING->value => 'Pending',
      'completed' => 'Completed',
      default => ucfirst($status),
    };
  }
};
?>

<div class="space-y-6">
  <!-- Welcome Header -->
  <div class="bg-white border border-gray-200 rounded-lg p-6">
    <p class="text-sm text-gray-500 mb-1">Welcome back,</p>
    <h1 class="text-2xl font-bold text-gray-900">{{ auth()->user()->name }}</h1>
    <p class="text-gray-600 mt-1">Track your proposals, groups, and consultations all in one place.</p>
  </div>

  @if($this->sections)
    <!-- Section & Group -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- My Section -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
          My Section
        </h3>

        <div class="border border-gray-100 rounded-lg p-4">
          <div class="flex justify-between items-start mb-4">
            <div>
              <p class="font-bold text-gray-900">{{ $this->sections['name'] }}</p>
              <p class="text-sm text-gray-500">
                {{ $this->sections['program_name'] }} • {{ $this->sections['semester_name'] }}
              </p>
            </div>
            <span class="text-xs bg-green-50 text-green-700 px-2 py-1 rounded">Active</span>
          </div>

          <div class="space-y-3">
            <div class="flex items-center gap-3 text-sm">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <div>
                <span class="text-gray-500">Instructor:</span>
                <span class="text-gray-900 font-medium ml-1">{{ $this->sections['instructor_name'] }}</span>
              </div>
            </div>

            <div class="flex items-center gap-3 text-sm">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
              <div>
                <span class="text-gray-500">Students:</span>
                <span class="text-gray-900 font-medium ml-1">{{ $this->sections['students_count'] }} enrolled</span>
                <span class="text-gray-400 ml-1">• {{ $this->sections['groups_count'] }} groups</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- My Group -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          My Group
        </h3>

        <div class="border border-gray-100 rounded-lg p-4">
          @php $group = $this->group; @endphp
          <div class="flex justify-between items-start mb-4">
            <div>
              <p class="font-bold text-gray-900">{{ $group['name'] }}</p>
              @if($group['leader_id'] === auth()->user()->profileable->id)
                <p class="text-sm text-green-600">Group Leader</p>
              @endif
            </div>
            <span class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded">
              {{ count($group['members']) }} members
            </span>
          </div>

          @if(!empty($group['members']))
            <div class="mb-4">
              <p class="text-xs text-gray-500 mb-2">Members</p>
              <div class="space-y-2">
                @foreach($group['members'] as $member)
                  <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                      <div
                        class="w-7 h-7 rounded-full bg-gray-200 text-gray-600 text-xs flex items-center justify-center font-medium">
                        {{ collect(explode(' ', $member['name']))->map(fn($n) => strtoupper($n[0] ?? ''))->take(2)->implode('') }}
                      </div>
                      <div>
                        <p class="text-gray-900">{{ $member['name'] }}</p>
                        @if($member['id'] === $group['leader_id'])
                          <p class="text-xs text-green-600">Leader</p>
                        @endif
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @endif

          <div class="grid grid-cols-2 gap-2 pt-4 border-t border-gray-100">
            <div class="text-center">
              <p class="font-bold text-gray-900">{{ count($this->proposals) }}</p>
              <p class="text-xs text-gray-500">Proposals</p>
            </div>
            <div class="text-center">
              <p class="font-bold text-gray-900">{{ count($this->consultations) }}</p>
              <p class="text-xs text-gray-500">Consultations</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Proposals & Consultation -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- My Proposals -->
      <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">My Proposals</h2>
          <a href="#" class="text-sm text-blue-600 hover:underline">View all</a>
        </div>

        @php $proposals = $this->proposals; @endphp
        @if(!empty($proposals))
          <div class="space-y-4">
            @foreach($proposals as $proposal)
              <div
                class="bg-white border {{ $proposal['status'] === 'approved' ? 'border-green-200' : ($proposal['status'] === 'revision' ? 'border-orange-200' : 'border-gray-200') }} rounded-lg p-5">
                <div class="flex items-start justify-between mb-2">
                  <h3 class="font-semibold text-gray-900">{{ $proposal['title'] }}</h3>
                  <span class="text-xs {{ $this->getStatusBadgeClass($proposal['status']) }} px-2 py-1 rounded">
                    {{ $this->getStatusLabel($proposal['status']) }}
                  </span>
                </div>

                <p class="text-sm text-gray-600 mb-3">{{ $proposal['description'] }}</p>

                @if($proposal['feedback'])
                  <div class="bg-orange-50 border border-orange-100 rounded p-3 mb-3">
                    <p class="text-xs text-orange-800 font-medium">Feedback:</p>
                    <p class="text-xs text-orange-700 mt-1">"{{ $proposal['feedback'] }}"</p>
                  </div>
                @endif

                <div class="grid grid-cols-2 gap-3 text-xs mb-3">
                  <div>
                    <span class="text-gray-500">Group ID:</span>
                    <span class="text-gray-900 font-medium ml-1">{{ $proposal['group_id'] }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Submitted by:</span>
                    <span class="text-gray-900 font-medium ml-1">User #{{ $proposal['submitted_by'] }}</span>
                  </div>
                </div>

                <div class="flex gap-2 mt-4">
                  @if($proposal['status'] === 'approved')
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded">View
                      Details</button>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded">Download
                      PDF</button>
                  @elseif($proposal['status'] === 'revision')
                    <button class="text-xs bg-orange-600 hover:bg-orange-700 text-white px-3 py-1.5 rounded">Submit
                      Revision</button>
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded">View
                      Feedback</button>
                  @else
                    <button class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded">View
                      Details</button>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500">
            <p>No proposals yet</p>
          </div>
        @endif
      </div>

      <!-- Consultation Schedule -->
      <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Consultation Schedule</h2>

        <div class="bg-white border border-gray-200 rounded-lg p-5">
          @php $consultations = $this->consultations; @endphp
          @if(!empty($consultations))
            <div class="space-y-3">
              @foreach($consultations as $consultation)
                @php
                  $isCompleted = $consultation['status'] === ProposalStatus::APPROVED->value
                    || $consultation['status'] === 'completed';
                @endphp
                <div
                  class="border {{ $consultation['type'] === 'upcoming' && !$isCompleted ? 'border-blue-100 bg-blue-50' : ($consultation['type'] === 'request' ? 'border-dashed border-gray-300' : 'border-gray-200') }} rounded p-3">
                  <div class="flex gap-3">
                    <div class="text-center {{ $isCompleted ? 'opacity-60' : '' }}">
                      @if($consultation['scheduled_at'])
                        <p
                          class="text-xs {{ $consultation['type'] === 'upcoming' && !$isCompleted ? 'text-blue-600' : 'text-gray-500' }}">
                          {{ \Carbon\Carbon::parse($consultation['scheduled_at'])->format('M') }}
                        </p>
                        <p
                          class="text-lg font-bold {{ $consultation['type'] === 'upcoming' && !$isCompleted ? 'text-blue-700' : 'text-gray-700' }}">
                          {{ \Carbon\Carbon::parse($consultation['scheduled_at'])->format('d') }}
                        </p>
                      @else
                        <p class="text-xs text-gray-500">TBD</p>
                        <p class="text-lg font-bold text-gray-700">--</p>
                      @endif
                    </div>
                    <div class="flex-1">
                      <p class="text-sm font-semibold text-gray-900">Instructor #{{ $consultation['instructor_id'] }}</p>
                      @if($consultation['scheduled_at'])
                        <p class="text-xs text-gray-600">{{ $this->formatTime($consultation['scheduled_at']) }}</p>
                      @endif
                      @if($consultation['remarks'])
                        <p class="text-xs text-gray-700 mt-1">{{ $consultation['remarks'] }}</p>
                      @endif
                      <p class="text-xs {{ $isCompleted ? 'text-green-600' : 'text-yellow-600' }} mt-1">
                        {{ $this->getStatusLabel($consultation['status']) }}
                      </p>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="text-center text-gray-500 py-4">
              <p>No consultations scheduled</p>
            </div>
          @endif
        </div>
      </div>
    </div>
  @else
    <div class="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-500">
      <p>No active section found. Please contact your administrator.</p>
    </div>
  @endif
</div>
