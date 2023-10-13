<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (Auth::guard('sanctum')->check()) {
            if (Auth::guard('sanctum')->user()->hasAnyRole($roles) || count($roles) === 0) {
                return $next($request);
            }
        } elseif (in_array('Guest', $roles) || count($roles) === 0) {
            return $next($request);
        } 

        return response()->json(['message' => 'Отказано в доступе'], 403);
    }

}
