<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckReport
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Insurer, AllCars, Surveyor, Workshop Admin | Insurer, AllCars Support Staff
        $user = Auth::user();
        if ($user->role == 'admin' || ($user->category == 'all_cars' && $user->role == 'support_staff') || ($user->category == 'insurer' && $user->role == 'support_staff')) {
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }
}
