<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use Laravel\Sanctum\PersonalAccessToken;

class CheckTokenExpiry
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
         $token = $request->bearerToken();
        
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            
            if ($accessToken && $accessToken->expires_at && $accessToken->expires_at->isPast()) {
                return $this->sendError('Token has expired', [], 401);
            }
        }
        
        return $next($request);
    }
}
