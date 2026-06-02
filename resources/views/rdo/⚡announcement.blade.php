<?php

use App\Models\Post;
use App\Services\PostService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.rdo.app')]
#[Title('Announcements')]
class extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function posts()
    {
        return Post::query()
            ->with('author', 'section')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('content', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[Renderless]
    #[On('delete-post')]
    public function deletePost(Post $post, PostService $postService)
    {
        $postService->deletePost($post);
    }
};
?>

@assets
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Calistoga&family=JetBrains+Mono:wght@400;500&display=swap"
  rel="stylesheet">
<link rel="stylesheet" href="{{ Vite::asset('resources/css/filament.css') }}">
@endassets

{{-- FIXED: Changed logic to track deleted IDs cleanly, which keeps Live-Search operational --}}
<div x-data="{
    deletedIds: [],
    deletePost(post) {
      if (!confirm('Are you sure you want to delete this announcement? This action cannot be undone.')) {
        return;
      }
      // Optimistic UI: Instantly hide the element from the browser viewport
      this.deletedIds.push(post.id);
    },
    init(){
      // Reset tracking array whenever a new search query is typed
      $watch('$wire.search', () => this.deletedIds = []);
    }
  }" @delete-post.window="deletePost($event.detail.post)">
  {{-- Badge --}}
  <div class="inline-flex items-center gap-2 rounded-full border px-4 py-1.5 mb-5"
    style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
    <span class="w-1.5 h-1.5 rounded-full animate-pulse" style="background: #0052FF"></span>
    <span
      style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
      Announcements
    </span>
  </div>

  {{-- Header Panel --}}
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5">
    <div>
      <h1 class="leading-tight"
        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
        Announcements
        <span
          style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">
          .
        </span>
      </h1>
      <p class="mt-2 text-sm" style="color: #64748B">
        Manage your announcements to keep everyone informed and engaged.
      </p>
    </div>

    {{-- Action bar --}}
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
      <a href="{{ route('rdo.announcements.create') }}" wire:navigate
        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98] group shrink-0"
        style="background: linear-gradient(to right, #0052FF, #4D7CFF); box-shadow: 0 4px 12px rgba(0,82,255,0.3)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd" />
        </svg>
        Create
        <svg xmlns="http://www.w3.org/2000/svg"
          class="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 20 20"
          fill="currentColor">
          <path fill-rule="evenodd"
            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
            clip-rule="evenodd" />
        </svg>
      </a>

      <div class="relative">
        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search announcements..."
          class="pl-10 pr-4 h-10 rounded-xl text-sm outline-none transition-all duration-200 w-full sm:w-60"
          style="background: white; border: 1px solid #E2E8F0; color: #0F172A"
          onfocus="this.style.borderColor='#0052FF'; this.style.boxShadow='0 0 0 3px rgba(0,82,255,0.12)'"
          onblur="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'" />
        <div class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" style="color: #94A3B8">
          <x-heroicon-o-magnifying-glass class="h-4 w-4" />
        </div>
      </div>
    </div>
  </div>

  {{-- Post Cards Feed Container --}}
  <div class="mt-10">
    @if($this->posts()->isEmpty())
    {{-- Centered Empty State Layout --}}
    <div
      class="max-w-2xl mx-auto flex flex-col items-center justify-center text-center p-12 border border-dashed border-slate-200 rounded-2xl bg-white/50">
      <div class="inline-flex p-4 rounded-xl bg-slate-50 text-slate-400 mb-4">
        <x-heroicon-o-newspaper class="h-8 w-8" />
      </div>
      <h3 class="text-base font-semibold text-slate-900">No announcements found</h3>
      <p class="mt-1 text-sm text-slate-500 max-w-sm">
        @if($search)
        We couldn't find matches for "{{ $search }}". Try refining your keywords.
        @else
        There aren't any active system broadcasts right now. Check back shortly.
        @endif
      </p>
    </div>
    @else
    {{-- Centered Single Column Layout Stack --}}
    {{-- FIXED: Replaced x-for with server loop managed by Alpine visibility masks to preserve your Livewire tags --}}
    <div class="max-w-4xl mx-auto flex flex-col gap-6">
      @foreach($this->posts() as $post)
      <div x-show="!deletedIds.includes({{ $post->id }})" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
        wire:key="post-wrapper-{{ $post->id }}">
        <livewire:post :post="$post" :key="$post->id" />
      </div>
      @endforeach
    </div>

    <div class="mt-8">
      {{ $this->posts()->links() }}
    </div>
    @endif
  </div>
</div>
