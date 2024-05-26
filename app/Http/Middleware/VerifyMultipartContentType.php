<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMultipartContentType
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request content type is multipart/form-data
        if ($request->is('items*') && $request->is('multipart/*')) {
            return $next($request);
        }

        // Return a response indicating the incorrect content type
        return response()->json(['error' => 'Invalid content type. Only multipart/form-data is allowed.'], 400);
    }
}
