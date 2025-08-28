<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessDetails
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check only applies to users with the 'owner' role who haven't created a business yet
        if (Auth::check() && Auth::user()->role === 'owner' && is_null(Auth::user()->business_id)) {

            if (!$request->routeIs('business.create') && !$request->routeIs('business.store')) {
                return redirect()->route('business.create');
            }
        }

        return $next($request);
    }
}