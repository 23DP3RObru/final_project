<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class CheckAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized - No token provided'], 401);
        }

        try {
            $parts = explode(':', base64_decode($token));
            $userId = (int) $parts[0];
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['message' => 'Unauthorized - User not found'], 401);
            }

            $request->user = $user;

            return $next($request);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Unauthorized - Invalid token'], 401);
        }
    }
}
