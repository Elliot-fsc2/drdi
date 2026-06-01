<?php

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Post $post;

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->color('danger')
            ->modalCloseButton(false)
            ->requiresConfirmation()
            ->successNotificationTitle('Announcement deleted')
            ->action(fn () => $this->dispatch('delete-post', post: $this->post));
    }
};
?>

<div
  class="w-full rounded-2xl border border-slate-200/80 bg-white px-5 pt-5 pb-4 shadow-xs transition-all duration-200 hover:border-slate-300 hover:shadow-sm space-y-4">

  <div class="flex items-center gap-3">
    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
      <img src="{{ $post->author->avatar_url ?? asset('images/default-avatar.png') }}" alt="{{ $post->author->name }}"
        class="block h-full w-full object-cover">
    </div>

    <div class="flex-1 min-w-0">
      <h4 class="text-sm font-bold text-slate-900 truncate leading-tight">
        {{ $post->author->name ?? 'System Administrator' }}
      </h4>
      <p class="text-xs text-slate-500 truncate mt-0.5">
        {{ $post->author->profileable?->role ?? 'DRDI Department Staff' }}
      </p>
      <div class="mt-1 inline-flex items-center gap-1.5 text-xs text-slate-400">
        <x-heroicon-o-clock class="h-3.5 w-3.5 text-slate-350" />
        <time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>
      </div>
    </div>

    <div class="shrink-0 flex items-center gap-1.5 self-start">
      @if($post->target_type)
      <span
        class="inline-flex items-center rounded-lg bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 border border-slate-200/60 uppercase tracking-wider">
        {{ Str::headline($post->target_type instanceof \BackedEnum ? $post->target_type->value : $post->target_type) }}
      </span>
      @endif

      <div x-data="{ open: false }" class="relative">
        <button @click="open = !open" class="p-1 rounded-lg text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition">
          <x-heroicon-o-ellipsis-horizontal class="h-5 w-5" />
        </button>

        <div x-show="open" x-cloak @click.outside="open = false"
             class="absolute right-0 mt-2 w-40 rounded-lg border bg-white shadow-lg py-1 z-50">
          @if(\Illuminate\Support\Facades\Route::has('rdo.announcements.edit'))
            <a href="{{ route('rdo.announcements.edit', $post) }}" wire:navigate
               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
              <x-heroicon-o-pencil-square class="h-4 w-4" />
              Edit
            </a>
          @else
            <a href="/rdo/announcements/{{ $post->id }}/edit"
               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
              <x-heroicon-o-pencil-square class="h-4 w-4" />
              Edit
            </a>
          @endif

          <button type="button"
                  x-on:click="$dispatch('delete-post', { post: {{ $post }} })"
                  class="flex items-center gap-2 px-3 py-2 text-sm w-full text-left text-red-600 hover:bg-slate-50">
            <x-heroicon-o-trash class="h-4 w-4" />
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="space-y-2">
    @if($post->title)
      <h3 class="text-base font-bold tracking-tight text-slate-900 leading-snug">
        {{ $post->title }}
      </h3>
    @endif

    @php
      $content = $post->content ?? '';

      $contentHtml = class_exists(\Filament\Forms\Components\RichEditor\RichContentRenderer::class)
          ? \Filament\Forms\Components\RichEditor\RichContentRenderer::make($content)->toHtml()
          : (string) Str::of((string) $content)->sanitizeHtml();

      $contentPlain = (string) Str::of($contentHtml)->stripTags()->squish();
      $shouldToggleContent = Str::length($contentPlain) > 240;

      $images = $post->images_path;
      if (! is_array($images)) {
          $images = [];
      }

      $images = array_values(array_filter($images, fn ($path) => filled($path)));

      /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
      $publicDisk = Storage::disk('public');

        $imageUrls = array_values(array_filter(array_map(function ($path) use ($publicDisk): ?string {
          $path = (string) $path;

          if (blank($path)) {
              return null;
          }

          if (Str::startsWith($path, ['http://', 'https://'])) {
              return $path;
          }

          if (Str::startsWith($path, ['/storage/', 'storage/'])) {
              return asset(ltrim($path, '/'));
          }

            return $publicDisk->url($path);
      }, $images)));

      $visibleImageUrls = array_slice($imageUrls, 0, 5);
      $extraImageCount = max(0, count($imageUrls) - 5);

      $imageCount = count($visibleImageUrls);
      $gridClass = match (true) {
          $imageCount === 1 => 'grid-cols-1',
          $imageCount === 2 => 'grid-cols-2',
          $imageCount === 3 => 'grid-cols-2 grid-rows-2',
          $imageCount === 4 => 'grid-cols-2 grid-rows-2',
          $imageCount >= 5 => 'grid-cols-6 grid-rows-2',
          default => '',
      };
    @endphp

    @if(filled($contentPlain))
      <div x-data="{ expanded: false }" class="space-y-2">
        <div
          class="prose prose-sm prose-slate max-w-none text-slate-600 leading-relaxed
                 [&_p]:mb-2 [&_ul]:list-disc [&_ul]:pl-4 [&_ol]:list-decimal [&_ol]:pl-4 {{ $shouldToggleContent ? 'max-h-28 overflow-hidden' : '' }}"
          :class="expanded ? 'max-h-none overflow-visible' : ''"
        >
          {!! $contentHtml !!}
        </div>

        @if($shouldToggleContent)
          <button
            type="button"
            x-cloak
            x-show="!expanded"
            @click="expanded = true"
            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition"
          >
            See more...
          </button>

          <button
            type="button"
            x-cloak
            x-show="expanded"
            @click="expanded = false"
            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition"
          >
            Show less
          </button>
        @endif
      </div>
    @endif

    @if($imageCount > 0)
      <div
        x-data="{
          open: false,
          index: 0,
          images: @js($imageUrls),
          openAt(i) {
            this.index = i;
            this.open = true;
          },
          close() {
            this.open = false;
          },
          prev() {
            if (this.index > 0) {
              this.index -= 1;
            }
          },
          next() {
            if (this.index < this.images.length - 1) {
              this.index += 1;
            }
          },
        }"
        class="space-y-2"
      >
        <div class="grid {{ $gridClass }} gap-1 overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
          @foreach($visibleImageUrls as $index => $url)
            @php
              $isFivePlusLayout = $imageCount >= 5;

              $tileClass = match (true) {
                  $imageCount === 1 => 'aspect-video',
                  $imageCount === 2 => 'aspect-square',
                  $imageCount === 3 && $index === 0 => 'row-span-2',
                  $imageCount === 3 => 'aspect-square',
                  $imageCount === 4 => 'aspect-square',
                  $isFivePlusLayout && $index < 2 => 'col-span-3 aspect-square',
                  $isFivePlusLayout => 'col-span-2 aspect-square',
                  default => 'aspect-square',
              };
            @endphp

            <button
              type="button"
              @click="openAt({{ $index }})"
              class="relative overflow-hidden bg-slate-100 {{ $tileClass }}"
            >
              <img
                src="{{ $url }}"
                alt="Post photo {{ $index + 1 }}"
                class="block h-full w-full object-cover"
                loading="lazy"
              />

              @if($index === 4 && $extraImageCount > 0)
                <div class="absolute inset-0 bg-slate-900/55 flex items-center justify-center">
                  <span class="text-white text-2xl font-bold tracking-tight">+{{ $extraImageCount }}</span>
                </div>
              @endif
            </button>
          @endforeach
        </div>

        <div
          x-cloak
          x-show="open"
          class="fixed inset-0 z-50"
          aria-modal="true"
          role="dialog"
        >
          <div class="absolute inset-0 bg-slate-900/70" @click.self="close()"></div>

          <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
            <div class="w-full max-w-5xl">
              <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                  <div class="text-xs text-slate-600 font-semibold" x-text="(index + 1) + ' of ' + images.length"></div>

                  <button
                    type="button"
                    @click="close()"
                    class="text-xs font-semibold text-slate-600 hover:text-slate-900"
                  >
                    CLOSE
                  </button>
                </div>

                <div class="relative bg-slate-950/5">
                  <div class="flex items-center justify-center px-4 py-4">
                    <img
                      :src="images[index]"
                      alt="Full size photo"
                      class="block max-h-[70vh] w-auto object-contain"
                    />
                  </div>

                  <button
                    type="button"
                    @click="prev()"
                    :disabled="index === 0"
                    class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 border border-slate-200 p-2 text-slate-700 shadow-sm disabled:opacity-40"
                    aria-label="Previous"
                  >
                    <x-heroicon-o-chevron-left class="h-5 w-5" />
                  </button>

                  <button
                    type="button"
                    @click="next()"
                    :disabled="index === images.length - 1"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/90 border border-slate-200 p-2 text-slate-700 shadow-sm disabled:opacity-40"
                    aria-label="Next"
                  >
                    <x-heroicon-o-chevron-right class="h-5 w-5" />
                  </button>
                </div>

                <div class="border-t border-slate-100 bg-slate-50 px-3 py-2">
                  <div class="flex gap-2 overflow-x-auto">
                    <template x-for="(img, i) in images" :key="img + ':' + i">
                      <button
                        type="button"
                        class="shrink-0 h-14 w-20 rounded-md overflow-hidden border"
                        :class="i === index ? 'border-slate-900' : 'border-slate-200'"
                        @click="index = i"
                      >
                        <img :src="img" alt="Thumbnail" class="block h-full w-full object-cover" />
                      </button>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>

  @if($post->section)
    <div
      class="rounded-xl border border-slate-100 bg-slate-50/50 px-4 py-2.5 flex items-center justify-between text-xs text-slate-600">
      <span class="flex items-center gap-1.5 font-medium text-slate-700">
        <x-heroicon-o-folder class="h-4 w-4 text-slate-400" />
        Target Group Section:
      </span>
      <span class="font-semibold bg-white border px-2 py-0.5 rounded-md text-slate-800 shadow-2xs">
        {{ $post->section->name ?? 'Section ' . $post->section_id }}
      </span>
    </div>
  @endif
<x-filament-actions::modals />
</div>
