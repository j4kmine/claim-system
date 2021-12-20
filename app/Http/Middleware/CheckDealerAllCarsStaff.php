<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use Log;

class CheckDealerAllCarsStaff
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
        if ($user->category == 'dealer' || ($user->category == 'all_cars' && $user->role == 'support_staff')) {
            return $next($request);
        }
        return response('Unauthorized.', 401);
    }
}
