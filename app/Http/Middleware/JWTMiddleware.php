<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,  $guard = 'api')
    {
        // if ($guard != null) {
        //     \Config::set('auth.defaults.guard',$guard);
        //     auth()->shouldUse($guard);
        // }

        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated, Please login'
            ], 401);
        }

        return $next($request);
    }
}



