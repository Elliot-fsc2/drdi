<?php

namespace App\Http\Middleware;

use App\Enums\InstructorRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RDOMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Unauthorized');
        }

        $allowedRoles = [InstructorRole::RDO, InstructorRole::Staff];

        if (! in_array($user->profileable?->role, $allowedRoles, true)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
