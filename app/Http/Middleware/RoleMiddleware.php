<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware to restrict route access by user role.
 *
 * Usage: Route::middleware(['auth', 'role:teacher,student'])->group(...)
 *
 * Supports multiple roles per route (comma-separated).
 * Handles inactive users, inactive schools, and role-based redirects.
 */
class RoleMiddleware
{
    /**
     * Handle the incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  ...$roles  Allowed role slugs (e.g., 'super_admin', 'teacher')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check if the user's account is active
        if ($user->status !== 'active') {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        // Check if user's school is suspended (skip for super_admin)
        if (!$user->isSuperAdmin() && $user->school && $user->school->status === 'suspended') {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your school account has been suspended.']);
        }

        // Check if the user's role is in the allowed roles list
        if (!in_array($user->role?->slug, $roles)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}