<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckWSAdminAllCarsStaff
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
        $user = Auth::user();
        if (($user->category == 'workshop') || ($user->category == 'all_cars' && $user->role == 'support_staff')) {
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }
}
