<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Jika user sudah login, redirect ke dashboard sesuai role.
     * - Admin        → admin panel (dashboard)
     * - Mandor/Staff → user portal SPA
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                if ($user->hasEffectiveRole(User::ROLE_ADMIN)) {
                    return redirect()->route('dashboard');
                }

                if ($user->hasEffectiveRole([User::ROLE_MANDOR, User::ROLE_STAFF_OPERATOR])) {
                    return redirect('/user/#/dashboard');
                }

                return redirect()->route('gateway');
            }
        }

        return $next($request);
    }
}
