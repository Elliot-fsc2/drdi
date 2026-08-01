<?php

namespace App\Livewire;

use Livewire\Component;

class NotificationDropdown extends Component
{
    public int $unreadCount = 0;

    public array $notifications = [];

    protected $listeners = ['notificationRefresh' => '$refresh'];

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $user = auth()->user();

        $this->unreadCount = $user->unreadNotifications()->count();

        $this->notifications = $user->unreadNotifications()
            ->latest()
            ->take(10)
            ->get()
            ->toArray();
    }

    public function markAsRead(string $notificationId): void
    {
        auth()->user()->notifications()
            ->where('id', $notificationId)
            ->update(['read_at' => now()]);

        $this->refresh();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        $this->refresh();
    }

    public function render()
    {
        return view('livewire.notification-dropdown');
    }
}
