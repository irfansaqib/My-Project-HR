<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessDetails
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Check if the user is authenticated
        if ($user) {
            // SCENARIO 1: The user does NOT have a business.
            if (!$user->business) {
                // If they are not already trying to create a business, force them to that page.
                if (!$request->routeIs('business.create') && !$request->routeIs('business.store')) {
                    return redirect()->route('business.create');
                }
            } 
            // SCENARIO 2: The user ALREADY HAS a business.
            else {
                // If they try to visit the create page again, redirect them away to their dashboard.
                if ($request->routeIs('business.create')) {
                    return redirect()->route('dashboard');
                }
            }
        }

        // Otherwise, allow the request to continue.
        return $next($request);
    }
}