<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;

class Auth0Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $method = $request->method();
        $path = $request->path();
        if ($method === 'OPTIONS' || $path === 'items' && $method === 'POST' && isset($request->filters)) {
            return $next($request);
        }
        $authController = new AuthController();
        try {
            $token = $request->bearerToken();
            $token = $authController->DecodeRawJWT($token);


            //TODO: fix this part due to unprocessable content error 422 if
            //front sends data as array instead of only one serialized object...
            // if (is_object($token)) {
            //     $token = (array) $token;
            // }
            // $data = $request->all();
            // $auth0_data = null;

            // // Check if 'data' is an array and has more than one element
            // if (is_array($data) && count($data) > 1) {
            //     // Remove the first element from the array
            //     $auth0_data = Json::decode(array_shift($data));

            //     // Replace the request data with only the remaining elements (with expected normal data)
            //     $request->replace($request->except(['0']));
            //     dump($request->all());
            // } else {
            //     dump('data', $data);
            // }
            // try {
            //     //find first by auth0_id
            //     $user = User::where('auth0_id', $token['sub'])->first();
            //     if (!$user) {
            //         // find by name and email->unique
            //         User::firstOrCreate(
            //             [
            //                 'name' => $auth0_data['given_name'],
            //                 'email' => $auth0_data['email']
            //             ],
            //             [
            //                 'auth0_id' => $token['sub'],
            //                 'name' => $auth0_data['given_name'],
            //                 'lastName' => $auth0_data['family_name'],
            //                 'email' => $auth0_data['email'],
            //                 'phone' => "",
            //             ],
            //         );
            //     }
            // } catch (\Throwable $e) {
            //     return response()->json(['error' => 'Internal error', 'error message' => $e->getMessage()], 500);
            // }

            return $next($request);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Unauthorized, by general catch', 'error message' => $e->getMessage()], 401);
        }
    }
}
