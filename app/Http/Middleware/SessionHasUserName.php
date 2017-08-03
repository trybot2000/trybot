<?php

namespace App\Http\Middleware;

use Closure;

class SessionHasUserName
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $username = session('who', null);
        if (!$username) {
            return redirect('who');
        }
        return $next($request);
    }
}
