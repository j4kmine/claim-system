<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckCategory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ... $categories)
    {
        $user = Auth::user();
        foreach ($categories as $category) {
            if ($user->category == $category) {
                return $next($request);
            }
        }
        return response('Unauthorized.', 401);
    }
}
