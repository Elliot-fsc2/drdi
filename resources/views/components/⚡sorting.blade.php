<?php

use App\Models\Group;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('test page')]
    class extends Component {
    public array $groups = [
        ['id' => 1, 'name' => 'Group 1'],
        ['id' => 2, 'name' => 'Group 2'],
        ['id' => 3, 'name' => 'Group 3'],
    ];

    public function handleSort($item, $position)
    {
        dd($item, $position);
    }
};
?>

<div>
    <ul wire:sort="handleSort">
        @foreach ($groups as $group)
            <li wire:key="{{ $group['id'] }}" wire:sort:item="{{ $group['id'] }}">
                {{ $group['name'] }}
            </li>
        @endforeach
    </ul>
</div>