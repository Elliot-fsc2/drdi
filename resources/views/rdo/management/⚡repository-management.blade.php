<?php

use App\Models\ResearchLibrary;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::rdo.app')] #[Title('Repository Management')] class extends Component {
  #[Computed]
  public function pendingRepositories()
  {
    return ResearchLibrary::query()
      ->whereHas('group.section', function ($query) {
        $query->active();
      })
      ->with(['group.section.program', 'group.leader'])
      ->latest()
      ->get()
      ->map(function (ResearchLibrary $repository): array {
        return [
          'id' => $repository->id,
          'title' => $repository->title,
          'group' => $repository->group?->name ?? 'N/A',
          'program' => $repository->group?->section?->program?->name ?? 'N/A',
          'section' => $repository->group?->section?->name ?? 'N/A',
          'academic_year' => $repository->academic_year ?? 'N/A',
          'has_file' => filled($repository->file_path),
        ];
      })
      ->values()
      ->all();
  }

  public function render()
  {
    return $this->view()
      ->title('Repository Management');
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
  <div class="relative mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12" x-data="{
    repositories: @js($this->pendingRepositories),
    get pendingCount() { return this.repositories.length; },
    get withFileCount() { return this.repositories.filter((repository) => repository.has_file).length; },
    get missingFileCount() { return this.pendingCount - this.withFileCount; },
  }">
    <div class="mb-8 sm:mb-10">
      <h1 class="leading-tight"
        style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
        Repository Management<span
          style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
      </h1>
      <p class="mt-2 text-sm" style="color: #64748B">
        Review unpublished research repositories from active sections.
      </p>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
        <p
          style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
          Pending Repositories</p>
        <p class="text-2xl font-bold" style="color: #0F172A" x-text="pendingCount"></p>
      </div>

      <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
        <p
          style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
          With Uploaded File</p>
        <p class="text-2xl font-bold" style="color: #0F172A" x-text="withFileCount"></p>
      </div>

      <div class="rounded-2xl border p-5 transition-all duration-200 hover:-translate-y-px hover:shadow-lg"
        style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
        <p
          style="font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em; color: #94A3B8; text-transform: uppercase">
          Missing File</p>
        <p class="text-2xl font-bold" style="color: #0F172A" x-text="missingFileCount"></p>
      </div>
    </div>

    <div class="overflow-hidden rounded-2xl border"
      style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
      <div class="border-b px-5 py-4" style="border-color: #F1F5F9; background: #FAFAFA">
        <p class="text-sm font-semibold" style="color: #0F172A">Pending Repository List</p>
      </div>

      <div class="hidden overflow-x-auto md:block">
        <table class="min-w-full text-left">
          <thead>
            <tr style="border-bottom: 1px solid #F1F5F9">
              <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider" style="color: #94A3B8">Title</th>
              <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider" style="color: #94A3B8">Group</th>
              <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider" style="color: #94A3B8">Program / Section</th>
              <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider" style="color: #94A3B8">Academic Year</th>
              <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wider" style="color: #94A3B8">File</th>
            </tr>
          </thead>
          <tbody>
            <template x-if="repositories.length === 0">
              <tr>
                <td colspan="5" class="px-5 py-10 text-center text-sm" style="color: #94A3B8">
                  No pending repositories found for active sections.
                </td>
              </tr>
            </template>

            <template x-for="repository in repositories" :key="repository.id">
              <tr style="border-bottom: 1px solid #F8FAFC">
                <td class="px-5 py-4 align-top">
                  <p class="text-sm font-semibold" style="color: #0F172A" x-text="repository.title"></p>
                </td>
                <td class="px-5 py-4 align-top text-sm" style="color: #475569" x-text="repository.group"></td>
                <td class="px-5 py-4 align-top text-sm" style="color: #475569">
                  <span x-text="repository.program"></span>
                  <span style="color: #94A3B8">/</span>
                  <span x-text="repository.section"></span>
                </td>
                <td class="px-5 py-4 align-top text-sm" style="color: #475569" x-text="repository.academic_year"></td>
                <td class="px-5 py-4 align-top">
                  <span x-show="repository.has_file" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0">Uploaded</span>
                  <span x-show="!repository.has_file" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                    style="background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA">Missing</span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <div class="space-y-3 p-4 md:hidden">
        <template x-show="repositories.length === 0">
          <p class="rounded-xl border p-6 text-center text-sm" style="border-color: #E2E8F0; color: #94A3B8">
            No pending repositories found for active sections.
          </p>
        </template>

        <template x-for="repository in repositories" :key="`mobile-${repository.id}`">
          <div class="rounded-xl border p-4" style="border-color: #E2E8F0; background: #FFFFFF">
            <p class="text-sm font-semibold" style="color: #0F172A" x-text="repository.title"></p>
            <p class="mt-1 text-xs" style="color: #64748B">
              Group: <span x-text="repository.group"></span>
            </p>
            <p class="text-xs" style="color: #64748B">
              <span x-text="repository.program"></span>
              <span style="color: #94A3B8">/</span>
              <span x-text="repository.section"></span>
            </p>
            <div class="mt-3 flex items-center justify-between text-xs">
              <span style="color: #475569">A.Y. <span x-text="repository.academic_year"></span></span>
              <span x-show="repository.has_file" class="inline-flex items-center rounded-full px-2.5 py-0.5 font-medium"
                style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0">Uploaded</span>
              <span x-show="!repository.has_file" class="inline-flex items-center rounded-full px-2.5 py-0.5 font-medium"
                style="background: #FFF7ED; color: #EA580C; border: 1px solid #FED7AA">Missing</span>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>
