<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;

class LogoutResponse implements \Filament\Auth\Http\Responses\Contracts\LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        // Change '/login' or 'home' to whatever route/URL you need
        return redirect()->route('login');
    }
}
