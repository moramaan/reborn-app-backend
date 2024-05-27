<?php

namespace App\Http\Middleware;

use App\Models\User;
use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Closure;
use Illuminate\Http\Request;

class Auth0Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $config = new SdkConfiguration(
            strategy: SdkConfiguration::STRATEGY_API,
            domain: config('auth0.domain'),
            audience: config('auth0.api_identifier')
        );

        $auth0 = new Auth0($config);

        try {
            $decodedToken = $auth0->decode($token);
            // Attach the decoded token to the request for further usage if needed
            $request->merge(['auth0Token' => $decodedToken]);

            return $next($request);
            // $decodedToken = $auth0->decode($token);
            // $request->merge(['auth0Token' => $decodedToken]);

            // // Extract user information from the token
            // $auth0UserId = $decodedToken->sub;
            // $auth0UserEmail = $decodedToken->email ?? null;

            // // Find or create the user in the database
            // $user = User::firstOrCreate(
            //     ['auth0_id' => $auth0UserId],
            //     ['email' => $auth0UserEmail]
            // );

            // // Attach the user to the request
            // $request->setUserResolver(function () use ($user) {
            //     return $user;
            // });

            // return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
