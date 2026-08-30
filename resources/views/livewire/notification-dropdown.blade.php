<div x-data="{ open: false }" class="relative" wire:poll.3s="refresh">
    <button @click="open = !open"
            @click.away="open = false"
            class="relative p-2 rounded-xl transition hover:bg-blue-700/50 text-blue-200 hover:text-white">
        <x-heroicon-o-bell class="w-6 h-6" />
        @if($unreadCount > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center ring-2 ring-blue-800">
                {{ min($unreadCount, 9) }}{{ $unreadCount > 9 ? '+' : '' }}
            </span>
        @endif
    </button>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="fixed left-3 right-3 top-[4.5rem] bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 max-h-[calc(100vh-6rem)] overflow-hidden sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-96 sm:max-h-[32rem]">

        <div class="p-3 border-b border-slate-100 flex justify-between items-center bg-slate-50/80">
            <h3 class="font-semibold text-slate-900 text-sm">Notifications</h3>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs text-cyan-600 hover:text-cyan-800 font-medium transition">
                    Mark all read
                </button>
            @endif
        </div>

        <div class="overflow-y-auto max-h-80">
            @forelse($notifications as $notif)
                <div class="flex items-start gap-3 p-3 border-b border-slate-50 transition hover:bg-slate-50 {{ is_null($notif['read_at']) ? 'bg-cyan-50/50' : '' }}">
                    <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-cyan-100 flex items-center justify-center text-cyan-600">
                        <x-dynamic-component :component="$notif['data']['icon'] ?? 'heroicon-o-bell'" class="w-5 h-5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium {{ is_null($notif['read_at']) ? 'text-slate-900' : 'text-slate-600' }}">
                            {{ $notif['data']['title'] ?? 'Notification' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                            {{ $notif['data']['message'] ?? '' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-1">
                            {{ \Carbon\Carbon::parse($notif['created_at'])->diffForHumans() }}
                        </p>
                    </div>

                    <div class="flex-shrink-0 flex items-start gap-1">
                        @if(isset($notif['data']['action_url']))
                            <a href="{{ $notif['data']['action_url'] }}"
                               wire:navigate
                               class="text-xs text-cyan-600 hover:text-cyan-800 font-medium px-2 py-1 rounded-lg hover:bg-cyan-50 transition">
                                {{ $notif['data']['action_text'] ?? 'View' }}
                            </a>
                        @endif
                        @if(is_null($notif['read_at']))
                            <button wire:click="markAsRead('{{ $notif['id'] }}')"
                                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition"
                                    title="Mark as read">
                                <x-heroicon-o-check class="w-4 h-4" />
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-slate-400">
                    <x-heroicon-o-bell-slash class="w-10 h-10 mb-2" />
                    <p class="text-sm font-medium">No notifications</p>
                    <p class="text-xs mt-1">You're all caught up!</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
