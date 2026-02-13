<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/admin/login')->with('error', 'Please login to access the admin panel.');
        }

        // Allow both 'admin' and 'staff' roles to access the panel generally.
        // Specific controller methods can refine access if needed.
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'staff'])) {
            Auth::logout();
            return redirect('/admin/login')->with('error', 'Unauthorized access.');
        }

        return $next($request);
    }
}
