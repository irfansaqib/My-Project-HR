<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Find all the parameters from the route (e.g., the {employee} part)
        foreach ($request->route()->parameters() as $parameter) {
            // Check if the parameter is a model and has a 'business_id'
            if ($parameter instanceof \Illuminate\Database\Eloquent\Model && isset($parameter->business_id)) {
                // If the model's business_id does not match the logged-in user's business_id,
                // deny access immediately.
                if ($parameter->business_id !== Auth::user()->business_id) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        return $next($request);
    }
}