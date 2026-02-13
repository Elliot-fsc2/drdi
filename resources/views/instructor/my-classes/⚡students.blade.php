<?php

use Livewire\Component;

new class extends Component {
  public $students = [
    [
      'id' => 1,
      'name' => 'John Doe',
      'student_number' => '2021-00001',
      'group' => 'Group 1',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 2,
      'name' => 'Jane Smith',
      'student_number' => '2021-00002',
      'group' => 'Group 2',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 3,
      'name' => 'Mike Johnson',
      'student_number' => '2021-00003',
      'group' => 'Group 3',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 4,
      'name' => 'Sarah Williams',
      'student_number' => '2021-00004',
      'group' => 'Group 4',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 5,
      'name' => 'David Brown',
      'student_number' => '2021-00005',
      'group' => 'Group 5',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 6,
      'name' => 'Emily Davis',
      'student_number' => '2021-00006',
      'group' => 'Group 6',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 7,
      'name' => 'Robert Miller',
      'student_number' => '2021-00007',
      'group' => 'Group 7',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 8,
      'name' => 'Lisa Anderson',
      'student_number' => '2021-00008',
      'group' => 'Group 8',
      'role' => 'Leader',
      'status' => 'Active'
    ],
    [
      'id' => 9,
      'name' => 'James Wilson',
      'student_number' => '2021-00009',
      'group' => 'Group 1',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 10,
      'name' => 'Patricia Moore',
      'student_number' => '2021-00010',
      'group' => 'Group 1',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 11,
      'name' => 'Michael Taylor',
      'student_number' => '2021-00011',
      'group' => 'Group 1',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 12,
      'name' => 'Jennifer Thomas',
      'student_number' => '2021-00012',
      'group' => 'Group 2',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 13,
      'name' => 'Christopher Jackson',
      'student_number' => '2021-00013',
      'group' => 'Group 2',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 14,
      'name' => 'Linda White',
      'student_number' => '2021-00014',
      'group' => 'Group 2',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 15,
      'name' => 'Daniel Harris',
      'student_number' => '2021-00015',
      'group' => 'Group 3',
      'role' => 'Member',
      'status' => 'Active'
    ],
    [
      'id' => 16,
      'name' => 'Barbara Martin',
      'student_number' => '2021-00016',
      'group' => 'Group 3',
      'role' => 'Member',
      'status' => 'Active'
    ],
  ];

  public function viewStudent($studentId)
  {
    // Handle view student details
  }

  public function changeGroup($studentId)
  {
    // Handle change group
  }

  public function removeFromGroup($studentId)
  {
    // Handle remove from group
  }
};
?>

@vite('resources/css/filament.css')

<div class="p-4">
  <div class="mb-4 flex items-center justify-between">
    <div class="relative flex-1 max-w-md">
      <input type="text" placeholder="Search students..."
        class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-md text-sm">
      <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>
    <div class="flex gap-2">
      <button
        class="px-4 py-2 bg-white border border-slate-300 text-slate-700 text-sm font-medium rounded-md hover:bg-slate-50">
        Export List
      </button>
      <button class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
        Add Student
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($students as $student)
      <div class="bg-white border border-slate-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-sm transition-all">
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <h3 class="font-semibold text-slate-900">{{ $student['name'] }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ $student['student_number'] }}</p>
          </div>
          <div class="flex items-center gap-2">
            <span
              class="px-2 py-0.5 text-xs font-medium rounded {{ $student['role'] === 'Leader' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' }}">
              {{ $student['role'] }}
            </span>

            <x-filament::dropdown placement="bottom-end">
              <x-slot name="trigger">
                <button class="p-1 hover:bg-slate-100 rounded transition-colors">
                  <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
              </x-slot>

              <x-filament::dropdown.list>
                <x-filament::dropdown.list.item icon="heroicon-o-eye" wire:click="viewStudent({{ $student['id'] }})">
                  View Details
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-o-arrows-right-left"
                  wire:click="changeGroup({{ $student['id'] }})">
                  Change Group
                </x-filament::dropdown.list.item>

                <x-filament::dropdown.list.item icon="heroicon-o-trash" wire:click="removeFromGroup({{ $student['id'] }})"
                  class="text-danger-600">
                  Remove from Group
                </x-filament::dropdown.list.item>
              </x-filament::dropdown.list>
            </x-filament::dropdown>
          </div>
        </div>

        <div class="flex items-center gap-2 text-sm text-slate-600">
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
          <span>{{ $student['group'] }}</span>
        </div>
      </div>
    @endforeach
  </div>
</div>
