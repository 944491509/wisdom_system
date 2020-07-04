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
        $currentRoute = Route::currentRouteName();
        $needcheckRoute = Auth::user()->allPermissions();
        $onlyAcl = $request->get('onlyacl', '');
        if (!in_array($currentRoute, $needcheckRoute)) {
            if ($onlyAcl) {
                return response()->json('ok');
            }else {
                return $next($request);
            }
        }
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

            return abort(401);
        }

        if ($onlyAcl) {
            return response()->json('ok');
        }else {
            return $next($request);
        }
    }
}
