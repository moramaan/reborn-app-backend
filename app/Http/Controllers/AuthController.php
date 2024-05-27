<?php

namespace App\Http\Controllers;

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function decodeToken(Request $request)
    {
        $token = $request->bearerToken();

        $config = new SdkConfiguration(
            strategy: SdkConfiguration::STRATEGY_API,
            domain: config('auth0.domain'),
            audience: config('auth0.api_identifier')
        );

        $auth0 = new Auth0($config);

        try {
            $decodedToken = $auth0->decode($token);
            // Now you can use $decodedToken for further processing
            return response()->json($decodedToken);
        } catch (\Throwable $e) {
            // Handle token decoding errors
            return response()->json(['error' => 'Token decoding failed'], 401);
        }
    }
}
