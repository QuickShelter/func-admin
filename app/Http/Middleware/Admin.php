<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = User::find($request->header('User-ID'));
        if(!$admin || !in_array('admin', $admin->roles)) {
            $response = [
                'success' => false,
                'message' => 'Unauthorised',
                'data' => []
            ];
            return Response($response, 401);
        }
        
        return $next($request);
    }
}
