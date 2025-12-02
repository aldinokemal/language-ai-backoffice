<?php

namespace App\Http\Middleware;

use App\Models\DB1\SysUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CachedAuth
{
    /**
     * Handle an incoming request.
     *
     * Cache authenticated user to avoid database queries on every request.
     * User is cached for 5 minutes and automatically invalidated on logout.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id');

        if ($userId) {
            $cacheKey = "auth_user_{$userId}";

            // Try to get cached user first
            $user = Cache::remember(
                $cacheKey,
                now()->addMinutes(5),
                function () use ($userId) {
                    \Log::info('CachedAuth: Cache MISS - Loading user from database', ['user_id' => $userId]);
                    return SysUser::with(['roles', 'permissions'])->find($userId);
                }
            );

            if ($user) {
                // Set the authenticated user without additional queries
                auth()->setUser($user);

                // Set permissions team ID if org exists in session
                if (session('org')) {
                    setPermissionsTeamId(session('org')['organization_id']);
                }
            } else {
                // User not found, clear session
                session()->forget('user_id');
                return redirect()->route('login');
            }
        } else {
            // Not authenticated
            return redirect()->route('login');
        }

        return $next($request);
    }
}
