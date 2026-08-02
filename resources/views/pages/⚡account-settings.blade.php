<?php

use App\Enums\InstructorRole;
use App\Models\Instructor;
use App\Models\Student;
use App\Service\UserSettings;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

new #[Title('Account Settings')]
class extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use WithPagination;

    private UserSettings $userSettings;

    public function boot(UserSettings $userSettings): void
    {
        $this->userSettings = $userSettings;
    }

    public function changePasswordAction(): Action
    {
        return Action::make('changePassword')
            ->modalCloseButton(false)
            ->color('info')
            ->modalWidth('md')
            ->modalHeading('Change Password')
            ->modalDescription('Enter your current password and a new password.')
            ->modalIcon(Heroicon::LockClosed)
            ->schema([
                TextInput::make('current_password')
                    ->label('Current Password')
                    ->password()
                    ->required()
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail) {
                        if (! \Hash::check($value, auth()->user()->password)) {
                            $fail('The current password is incorrect.');
                        }
                    }),
                TextInput::make('new_password')
                    ->label('New Password')
                    ->password()
                    ->required()
                    ->minLength(8),
                TextInput::make('new_password_confirmation')
                    ->label('Confirm New Password')
                    ->dehydrated(false)
                    ->same('new_password')
                    ->password()
                    ->required(),
            ])
            ->action(function (array $data): void {
                $this->userSettings->changePassword(auth()->id(), $data['new_password']);

                Notification::make()
                    ->title('Password changed successfully')
                    ->success()
                    ->send();
            });
    }

    public function toggleEmailNotifications(): void
    {
        $enabled = $this->userSettings->toggleEmailNotifications(auth()->id());

        Notification::make()
            ->title($enabled ? 'Email notifications enabled' : 'Email notifications disabled')
            ->success()
            ->send();
    }

    public function activityLogs()
    {
        return Activity::where('causer_id', auth()->id())->latest()->paginate(5, pageName: 'activityPage');
    }

    public function render()
    {
        $layout = match (true) {
            auth()->user()?->profileable_type === Student::class => 'layouts::student.app',
            auth()->user()?->profileable_type === Instructor::class && in_array(auth()->user()?->profileable?->role, [InstructorRole::RDO, InstructorRole::Staff]) => 'layouts::rdo.app',
            auth()->user()?->profileable_type === Instructor::class => 'layouts::instructor.app',
            default => 'layouts::app',
        };

        return $this->view()->layout($layout);
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

<div class="relative min-h-screen" style="background: #F8FAFC"
    x-data="{ activeTab: new URL(window.location).searchParams.get('page') || 'account' }">

    {{-- Ambient background glows --}}
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -right-32 -top-32 h-[500px] w-[500px] rounded-full"
            style="background: radial-gradient(circle, rgba(0,82,255,0.07), transparent 70%); filter: blur(60px)"></div>
        <div class="absolute -left-24 bottom-1/3 h-[400px] w-[400px] rounded-full"
            style="background: radial-gradient(circle, rgba(77,124,255,0.05), transparent 70%); filter: blur(80px)">
        </div>
    </div>

    <div class="relative mx-auto max-w-5xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8 lg:py-12">

        {{-- ── Page Header ────────────────────────────── --}}
        <div class="mb-8">
            <div class="mb-5 inline-flex items-center gap-2 rounded-full border px-4 py-1.5"
                style="border-color: rgba(0,82,255,0.2); background: rgba(0,82,255,0.05)">
                <span class="h-1.5 w-1.5 rounded-full" style="background: #0052FF"></span>
                <span
                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; letter-spacing: 0.14em; color: #0052FF; text-transform: uppercase">
                    Settings
                </span>
            </div>

            <h1 class="leading-tight"
                style="font-family: 'Calistoga', Georgia, serif; font-size: clamp(1.85rem, 4vw, 2.75rem); letter-spacing: -0.015em; color: #0F172A">
                Account Settings<span
                    style="background: linear-gradient(to right, #0052FF, #4D7CFF); -webkit-background-clip: text; background-clip: text; color: transparent">.</span>
            </h1>
            <p class="mt-2 text-sm" style="color: #64748B">
                Manage your account preferences, security, and notifications.
            </p>
        </div>

        {{-- ── Layout: Aside + Content ─────────────────── --}}
        <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">

            {{-- ── Aside Navigation ─────────────────────── --}}
            <aside class="shrink-0 lg:w-56 xl:w-64">
                <nav class="flex flex-row gap-1 overflow-x-auto rounded-2xl border p-1.5 lg:flex-col lg:gap-0.5 lg:border-0 lg:p-0"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">

                    {{-- Account --}}
                    <button @click="activeTab = 'account'; window.history.replaceState({}, '', '?page=account')" type="button"
                        :class="activeTab === 'account' ? 'text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap text-left"
                        :style="activeTab === 'account' ? 'background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 2px 8px rgba(0,82,255,0.25)' : ''">
                        <x-heroicon-o-user class="h-5 w-5 shrink-0" />
                        <span>Account</span>
                    </button>

                    {{-- Security --}}
                    <button @click="activeTab = 'security'; window.history.replaceState({}, '', '?page=security')" type="button"
                        :class="activeTab === 'security' ? 'text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap text-left"
                        :style="activeTab === 'security' ? 'background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 2px 8px rgba(0,82,255,0.25)' : ''">
                        <x-heroicon-o-lock-closed class="h-5 w-5 shrink-0" />
                        <span>Security</span>
                    </button>

                    {{-- Notifications --}}
                    <button @click="activeTab = 'notifications'; window.history.replaceState({}, '', '?page=notifications')" type="button"
                        :class="activeTab === 'notifications' ? 'text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap text-left"
                        :style="activeTab === 'notifications' ? 'background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 2px 8px rgba(0,82,255,0.25)' : ''">
                        <x-heroicon-o-bell class="h-5 w-5 shrink-0" />
                        <span>Notifications</span>
                    </button>

                    {{-- Activity Logs --}}
                    <button @click="activeTab = 'activity'; window.history.replaceState({}, '', '?page=activity')" type="button"
                        :class="activeTab === 'activity' ? 'text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                        class="flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-medium transition-all whitespace-nowrap text-left"
                        :style="activeTab === 'activity' ? 'background: linear-gradient(135deg, #0052FF, #4D7CFF); box-shadow: 0 2px 8px rgba(0,82,255,0.25)' : ''">
                        <x-heroicon-o-clock class="h-5 w-5 shrink-0" />
                        <span>Activity Logs</span>
                    </button>

                </nav>
            </aside>

            {{-- ── Dynamic Content ─────────────────────── --}}
            <div class="min-w-0 flex-1">

                {{-- Account --}}
                <div x-show="activeTab === 'account'" x-cloak
                    class="overflow-hidden rounded-2xl border"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold" style="color: #0F172A">Account Information</h2>
                        <p class="mt-0.5 text-xs" style="color: #94A3B8">Your basic profile details.</p>
                    </div>
                    <div class="divide-y px-6 py-4" style="border-color: #F1F5F9">
                        <div class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:gap-0">
                            <div class="w-36 shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Name
                                </p>
                            </div>
                            <p class="text-sm font-medium" style="color: #0F172A">{{ auth()->user()->name }}</p>
                        </div>

                        <div class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:gap-0">
                            <div class="w-36 shrink-0">
                                <p class="text-xs font-medium uppercase tracking-wide"
                                    style="color: #94A3B8; font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.1em">
                                    Email
                                </p>
                            </div>
                            <p class="text-sm" style="color: #334155">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Security --}}
                <div x-show="activeTab === 'security'" x-cloak
                    class="overflow-hidden rounded-2xl border"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold" style="color: #0F172A">Security</h2>
                        <p class="mt-0.5 text-xs" style="color: #94A3B8">Manage your password and security settings.</p>
                    </div>
                    <div class="divide-y px-6 py-4" style="border-color: #F1F5F9">
                        <div class="flex flex-col gap-1 py-4 sm:flex-row sm:items-center sm:justify-between sm:gap-0">
                            <div>
                                <p class="text-sm font-medium" style="color: #0F172A">Password</p>
                                <p class="text-xs" style="color: #94A3B8">Last changed {{ auth()->user()->password_changed_at ? auth()->user()->password_changed_at->diffForHumans() : 'Never' }}</p>
                            </div>
                            <button type="button" wire:click="mountAction('changePasswordAction')"
                                class="mt-2 inline-flex items-center gap-1.5 rounded-xl border px-4 py-2 text-sm font-medium transition-all hover:-translate-y-px hover:shadow-md sm:mt-0"
                                style="border-color: #E2E8F0; color: #0052FF; background: white; box-shadow: 0 1px 2px rgba(0,0,0,0.04)">
                                <x-heroicon-o-pencil class="h-4 w-4" />
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Activity Logs --}}
                @php $logs = $this->activityLogs(); @endphp
                <div x-show="activeTab === 'activity'" x-cloak
                    class="overflow-hidden rounded-2xl border"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold" style="color: #0F172A">Activity Logs</h2>
                        <p class="mt-0.5 text-xs" style="color: #94A3B8">Recent actions performed on your account.</p>
                    </div>
                    <div class="divide-y px-6 py-4" style="border-color: #F1F5F9">
                        @forelse ($logs as $log)
                            <div class="flex items-start gap-3 py-3">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                    style="background: rgba(0,82,255,0.06)">
                                    <x-heroicon-o-arrow-path class="h-4 w-4" style="color: #0052FF" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium" style="color: #0F172A">{{ $log->log_name }}</p>
                                    <p class="text-sm" style="color: #0F172A">{{ $log->description }}</p>
                                    <p class="mt-0.5 text-xs" style="color: #94A3B8">
                                        {{ $log->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center">
                                <p class="text-sm" style="color: #94A3B8">No activity logs yet.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($logs->hasPages())
                        <div class="border-t px-6 py-3" style="border-color: #F1F5F9">
                            {{ $logs->links(data: ['pageName' => 'activityPage']) }}
                        </div>
                    @endif
                </div>

                {{-- Notifications --}}
                <div x-show="activeTab === 'notifications'" x-cloak
                    class="overflow-hidden rounded-2xl border"
                    style="border-color: #E2E8F0; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.05)">
                    <div class="border-b border-slate-100 px-6 py-4">
                        <h2 class="text-base font-semibold" style="color: #0F172A">Notifications</h2>
                        <p class="mt-0.5 text-xs" style="color: #94A3B8">Choose what notifications you receive.</p>
                    </div>
                    <div class="divide-y px-6 py-4" style="border-color: #F1F5F9">
                        <div class="flex items-center justify-between py-4">
                            <div>
                                <p class="text-sm font-medium" style="color: #0F172A">Email Notifications</p>
                                <p class="text-xs" style="color: #94A3B8">Receive email updates for important events like proposal approvals, schedule changes, and announcements.</p>
                            </div>
                            <label class="relative inline-flex cursor-pointer items-center">
                                <input type="checkbox" role="switch" aria-label="Toggle email notifications"
                                    @checked(auth()->user()->notify_email)
                                    wire:click="toggleEmailNotifications"
                                    class="peer sr-only">
                                <div
                                    class="h-6 w-11 rounded-full bg-slate-300 after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-slate-300 after:bg-white after:transition-all peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300">
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <x-filament-actions::modals />
</div>


