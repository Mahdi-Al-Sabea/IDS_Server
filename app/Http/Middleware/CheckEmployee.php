<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class CheckEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'Employee') {
                return $next($request); 
            } else {
                return $this->sendError('Forbidden', [], Response::HTTP_FORBIDDEN); // 403
            }
        } else {
            return $this->sendError('Unauthorized', [], Response::HTTP_UNAUTHORIZED); // 401
        }
    }
}
