<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminOrStaff
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has admin or staff role
        $user = auth()->user();
        
        if ($user->hasRole(['admin', 'staff'])) {
            return $next($request);
        }

        // If user doesn't have admin/staff role, redirect to home with message
        return redirect()->route('home')->with('error', 'You do not have permission to access the dashboard. This area is for staff only.');
    }
}
