<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user->subscribedStripe()) {
            // Redirect user to billing page and ask them to subscribe...
            return redirect('/profile');
        }

        return $next($request);
    }
}
