<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use phpseclib3\Crypt\RSA;
use phpseclib3\Math\BigInteger;

class AuthController extends Controller
{

    public function DecodeRawJWT($jwt)
    {
        // URL to fetch the JWKS
        $jwksUrl = env('JWKS_URI');

        // Fetch the JWKS
        $jwks = json_decode(file_get_contents($jwksUrl), true);

        // Extract the JWT header
        $tokenParts = explode('.', $jwt);
        $header = json_decode(base64_decode($tokenParts[0]), true);
        $kid = $header['kid'];

        // Find the key with the matching kid
        $publicKey = null;
        foreach ($jwks['keys'] as $key) {
            if ($key['kid'] === $kid) {
                $publicKey = $this->convertJWKToPEM($key);
                break;
            }
        }

        if (!$publicKey) {
            throw new Exception('Public key not found.');
        }

        try {
            $token = JWT::decode($jwt, new Key($publicKey, 'RS256'));
            $now = new DateTimeImmutable();
        } catch (Exception $e) {
            throw new Exception('Unauthorized decoding: ' . $e->getMessage());
        }

        return $token;
    }

    private function convertJWKToPEM($jwk)
    {
        $modulus = new BigInteger($this->base64UrlDecode($jwk['n']), 256);
        $exponent = new BigInteger($this->base64UrlDecode($jwk['e']), 256);

        $rsa = RSA::load([
            'n' => $modulus,
            'e' => $exponent
        ]);

        $publicKey = $rsa->toString('PKCS8');

        return $publicKey;
    }

    private function base64UrlDecode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
