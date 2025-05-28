<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in and has admin role
        if (!session('user') || strtolower(session('user')['role']) !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Admin access required.'
            ], 401);
        }

        // Check for JWT token
        $token = session('jwt_token');
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'No token found'
            ], 401);
        }

        // Add token to request header
        $request->headers->set('Authorization', 'Bearer ' . $token);
        
        return $next($request);
    }
} 