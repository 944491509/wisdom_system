<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class Simpleacl
{
    public function handle($request, Closure $next)
    {   
        return $next($request);
        $currentRoute = Route::currentRouteName();
        if (!in_array($currentRoute, Auth::user()->aclPermissions())) {
            Log::info('simpleacl:', ['route' => $currentRoute, 'user' => Auth::user()->id]);
            if ( $request->isJson() || $request->wantsJson() ) {
                return response()->json([
                    'error' => [
                        'status_code' => 401,
                        'code'        => '权限不足',
                        'description' => '权限不足'
                    ],
                ], 401);
            }

            return abort(401, '权限不足');
        }

        return $next($request);
    }
}
