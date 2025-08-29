<?php

namespace App\Services;

use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use RuntimeException;
use Throwable;

class JwtService
{
    protected $algo;
    protected $accessTtl;
    protected $refreshTtl;
    protected $privateKey;
    protected $publicKey;
    protected $secret;


    public function __construct()
    {
        $this->algo   = config('jwt.algo');

        $this->accessTtl  = config('jwt.access_ttl');
        $this->refreshTtl = config('jwt.refresh_ttl');

        if ($this->algo === 'RS256') {
            $this->privateKey = file_get_contents(config('jwt.private_key_path'));
            $this->publicKey  = file_get_contents(config('jwt.public_key_path'));
        } else {
            $this->secret = config('jwt.secret');
        }
    }

    public function accessToken(int $userId): string
    {
        $now = time();

        $iss = config('jwt.iss', config('app.url'));
        $aud = config('jwt.aud', config('app.url'));

        $payload = [
            'sub' => $userId,
            'iat' => $now,
            'exp' => $now + $this->accessTtl,
            'iss' => $iss,
            'aud' => $aud,
        ];

        $signKey = $this->algo === 'RS256' ? $this->privateKey : $this->secret;
        return JWT::encode($payload, $signKey, $this->algo);
    }

    public function verifyToken(string $jwt): object
    {

        $verifyKey = $this->algo === 'RS256' ? $this->publicKey : $this->secret;
        $decoded   = JWT::decode($jwt, new Key($verifyKey, $this->algo));

        $iss = config('jwt.iss', config('app.url'));
        $aud = config('jwt.aud', config('app.url'));

        if (($decoded->iss ?? null) !== $iss || ($decoded->aud ?? null) !== $aud) {
            throw new RuntimeException('Invalid iss/aud');
        }

        return $decoded;
    }
}
