<?php

use App\Models\Group;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Research Repository')] class extends Component {
    public string $search = '';
    public string $filterYear = '';
    public string $filterProgram = '';

    public function groups(): array
    {
        return [
            [
                'title' => 'Smart Irrigation System Using IoT and ML',
                'leader' => 'Maria Santos',
                'members' => ['Juan dela Cruz', 'Ana Reyes', 'Carlo Mendoza'],
                'program' => 'BSCS',
                'section' => '4-A',
                'year' => '2024',
                'advisor' => 'Dr. Ramon Torres',
                'abstract' => 'An automated irrigation system that leverages IoT sensors and machine learning algorithms to optimize water usage in agricultural fields based on real-time soil moisture, weather data, and crop requirements.',
                'keywords' => ['IoT', 'Machine Learning', 'Agriculture', 'Automation'],
            ],
            [
                'title' => 'AI-Powered Early Detection of Diabetic Retinopathy',
                'leader' => 'Jose Bautista',
                'members' => ['Liza Fernandez', 'Miguel Ramos', 'Trisha Villanueva'],
                'program' => 'BSIT',
                'section' => '4-B',
                'year' => '2024',
                'advisor' => 'Prof. Elena Castillo',
                'abstract' => 'A deep learning-based diagnostic tool that analyzes retinal fundus images to detect early signs of diabetic retinopathy, enabling timely medical intervention and reducing the risk of blindness in diabetic patients.',
                'keywords' => ['Deep Learning', 'Medical Imaging', 'Healthcare', 'CNN'],
            ],
            [
                'title' => 'Blockchain-Based Academic Credential Verification',
                'leader' => 'Rachel Gomez',
                'members' => ['Paolo Aquino', 'Nina Coronel'],
                'program' => 'BSCS',
                'section' => '4-C',
                'year' => '2023',
                'advisor' => 'Dr. Ramon Torres',
                'abstract' => 'A decentralized platform using blockchain technology to issue, store, and verify academic credentials, eliminating document fraud and enabling instant verification by employers and institutions worldwide.',
                'keywords' => ['Blockchain', 'Credential Verification', 'Decentralization', 'Security'],
            ],
            [
                'title' => 'Sentiment Analysis of Student Feedback Using NLP',
                'leader' => 'Kevin Lim',
                'members' => ['Sofia Tan', 'Dennis Abad', 'Pearl Navarro', 'Roy Sy'],
                'program' => 'BSIT',
                'section' => '3-A',
                'year' => '2023',
                'advisor' => 'Prof. Elena Castillo',
                'abstract' => 'A natural language processing system that automatically analyzes and categorizes student feedback from course evaluations, providing instructors and administrators with actionable insights to improve teaching quality.',
                'keywords' => ['NLP', 'Sentiment Analysis', 'Education', 'Text Mining'],
            ],
            [
                'title' => 'Real-Time Sign Language Recognition via Computer Vision',
                'leader' => 'Grace Lacson',
                'members' => ['Nico Valdez', 'Camille Ocampo'],
                'program' => 'BSCS',
                'section' => '4-D',
                'year' => '2024',
                'advisor' => 'Dr. Ramon Torres',
                'abstract' => 'A computer vision application that performs real-time recognition of Filipino Sign Language (FSL) hand gestures using convolutional neural networks, bridging communication barriers for the deaf and hard-of-hearing community.',
                'keywords' => ['Computer Vision', 'Sign Language', 'Accessibility', 'CNN'],
            ],
            [
                'title' => 'Predictive Analytics for Student Academic Performance',
                'leader' => 'Andrei Cruz',
                'members' => ['Bianca Flores', 'Lance Ong', 'Mia Salazar'],
                'program' => 'BSIT',
                'section' => '3-B',
                'year' => '2023',
                'advisor' => 'Prof. Elena Castillo',
                'abstract' => 'A predictive modeling system that identifies at-risk students early in the semester by analyzing academic history, attendance, and behavioral patterns, enabling targeted intervention before grades deteriorate.',
                'keywords' => ['Predictive Analytics', 'Education', 'Data Mining', 'Early Intervention'],
            ],
        ];
    }

    public function filteredGroups(): array
    {
        return array_values(
            array_filter($this->groups(), function ($group) {
                $matchesSearch = empty($this->search) || str_contains(strtolower($group['title']), strtolower($this->search)) || str_contains(strtolower($group['leader']), strtolower($this->search)) || str_contains(strtolower(implode(' ', $group['keywords'])), strtolower($this->search));

                $matchesYear = empty($this->filterYear) || $group['year'] === $this->filterYear;
                $matchesProgram = empty($this->filterProgram) || $group['program'] === $this->filterProgram;

                return $matchesSearch && $matchesYear && $matchesProgram;
            }),
        );
    }

    public function group()
    {
        return Group::passed()
            ->when($this->search, function ($query) {
                $query
                    ->where('title', 'like', '%' . $this->search . '%')
                    ->orWhere('leader', 'like', '%' . $this->search . '%')
                    ->orWhereHas('keywords', function ($q) {
                        $q->where('keyword', 'like', '%' . $this->search . '%');
                    });
            })
            ->get();
    }

    public function mount()
    {
        // dd($this->group());
    }
};
?>

@assets
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
@endassets

<div class="relative min-h-screen" style="background: #F8FAFC">

    {{-- Ambient background glows --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute bottom-1/3 -left-24 h-[400px] w-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Page Header ────────────────────────────── --}}
        <div class="mb-8 sm:mb-10">
            <div class="mb-5 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Research Repository
                </span>
            </div>

            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                Approved Research<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                Browse all completed and approved research group projects.
            </p>
        </div>

        {{-- ── Filters ─────────────────────────────────── --}}
        <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-center">

            {{-- Search --}}
            <div class="relative flex-1">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by title, leader, or keyword…"
                    class="w-full rounded-xl border py-2.5 pl-4 pr-4 text-sm outline-none transition-all focus:ring-2"
                    style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04); --tw-ring-color: rgba(0,82,255,0.15)">
            </div>

            {{-- Year filter --}}
            <select wire:model.live="filterYear"
                class="rounded-xl border py-2.5 pl-3.5 pr-8 text-sm outline-none transition-all focus:ring-2"
                style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04); --tw-ring-color: rgba(0,82,255,0.15)">
                <option value="">All Years</option>
                <option value="2024">2024</option>
                <option value="2023">2023</option>
            </select>

            {{-- Program filter --}}
            <select wire:model.live="filterProgram"
                class="rounded-xl border py-2.5 pl-3.5 pr-8 text-sm outline-none transition-all focus:ring-2"
                style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04); --tw-ring-color: rgba(0,82,255,0.15)">
                <option value="">All Programs</option>
                <option value="BSCS">BSCS</option>
                <option value="BSIT">BSIT</option>
            </select>

            {{-- Count badge --}}
            <div class="flex-shrink-0 rounded-xl border px-4 py-2.5 text-sm font-semibold"
                style="border-color: #E2E8F0; background: white; color: #0F172A; box-shadow: 0 1px 2px rgba(0,0,0,0.04)">
                <span style="color: #0052FF">{{ count($this->filteredGroups()) }}</span>
                <span style="color: #94A3B8"> / {{ count($this->groups()) }}</span>
            </div>
        </div>

        {{-- ── Cards Grid ───────────────────────────────── --}}
        @if (count($this->filteredGroups()) === 0)
            <div class="rounded-2xl border py-16 text-center"
                style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                <p class="mb-1 font-semibold" style="color: #0F172A">No results found</p>
                <p class="text-sm" style="color: #94A3B8">Try adjusting your search or filter criteria.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->filteredGroups() as $index => $group)
                    <a href="{{ route('repository.details', ['index' => array_search($group, $this->groups())]) }}"
                        wire:navigate
                        class="group relative flex flex-col overflow-hidden rounded-2xl border transition-all duration-200 hover:-translate-y-px hover:shadow-xl"
                        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05); text-decoration: none">

                        {{-- Gradient top stripe --}}
                        <div class="h-[3px] w-full rounded-t-2xl"
                            style="background: linear-gradient(to right, #0052FF, #4D7CFF)"></div>

                        <div class="flex flex-1 flex-col p-5">

                            {{-- Header: program + year badges --}}
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold"
                                    style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.06); color: #0052FF; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em">
                                    {{ $group['program'] }}
                                </span>
                                <span class="rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                    style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                                    {{ $group['section'] }} &bull; {{ $group['year'] }}
                                </span>
                                <span
                                    class="ml-auto inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium"
                                    style="border-color: rgba(16,185,129,0.25); background: rgba(16,185,129,0.07); color: #059669">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background: #10B981"></span>
                                    Approved
                                </span>
                            </div>

                            {{-- Title --}}
                            <h3 class="mb-2 text-[0.9375rem] font-semibold leading-snug" style="color: #0F172A">
                                {{ $group['title'] }}
                            </h3>

                            {{-- Abstract --}}
                            <p class="mb-4 line-clamp-3 flex-1 text-sm leading-relaxed" style="color: #64748B">
                                {{ $group['abstract'] }}
                            </p>

                            {{-- Keywords --}}
                            <div class="mb-4 flex flex-wrap gap-1.5">
                                @foreach ($group['keywords'] as $keyword)
                                    <span class="rounded-lg border px-2 py-0.5 text-xs"
                                        style="border-color: #E2E8F0; background: #F8FAFC; color: #64748B">
                                        {{ $keyword }}
                                    </span>
                                @endforeach
                            </div>

                            {{-- Divider --}}
                            <div class="mb-4 h-px" style="background: #F1F5F9"></div>

                            {{-- Members --}}
                            <div class="mb-3">
                                <div class="mb-1.5 flex items-center gap-1.5">
                                    <span class="text-xs font-semibold"
                                        style="color: #94A3B8; letter-spacing: 0.05em; text-transform: uppercase; font-family: 'JetBrains Mono', monospace; font-size: 10px">Leader</span>
                                </div>
                                <p class="text-sm font-medium" style="color: #0F172A">{{ $group['leader'] }}</p>
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center gap-1.5">
                                    <span class="text-xs font-semibold"
                                        style="color: #94A3B8; letter-spacing: 0.05em; text-transform: uppercase; font-family: 'JetBrains Mono', monospace; font-size: 10px">Members</span>
                                </div>
                                <p class="text-sm" style="color: #64748B">
                                    {{ implode(', ', $group['members']) }}
                                </p>
                            </div>

                        </div>

                        {{-- Footer: advisor --}}
                        <div class="flex items-center gap-2.5 border-t px-5 py-3"
                            style="border-color: #F1F5F9; background: #FAFBFF">
                            <span class="text-xs" style="color: #64748B">
                                Advised by <span class="font-semibold"
                                    style="color: #0F172A">{{ $group['advisor'] }}</span>
                            </span>
                        </div>

                    </a>
                @endforeach
            </div>
        @endif

    </div>
</div>
