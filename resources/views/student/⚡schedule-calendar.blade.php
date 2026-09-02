<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
    #[Layout('layouts::student.app')]
    #[Title('Department Schedule')]
    class extends Component {};
?>

<div class="min-h-screen" style="background: #F8FAFC">
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
        {{-- Header --}}
        <div class="mb-8">
            <div class="mb-4 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full" style="background: #0052FF"></span>
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em]"
                    style="font-family: 'JetBrains Mono', monospace; color: #0052FF">
                    Student Portal
                </span>
            </div>
            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                Schedule Calendar<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                All presentation schedules within your department, at a glance.
            </p>
        </div>

        <livewire:department-calendar />
    </div>
</div>
