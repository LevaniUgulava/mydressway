<?php


return [
    'algo' => env('JWT_ALGO', 'RS256'),

    'private_key_path' => env('JWT_PRIVATE_KEY_PATH', storage_path('keys/jwt_private.pem')),
    'public_key_path'  => env('JWT_PUBLIC_KEY_PATH', storage_path('keys/jwt_public.pem')),

    'secret' => env('JWT_SECRET', null),

    'access_ttl'  => env('JWT_ACCESS_TTL', 900),
    'web_refresh_ttl' => env('JWT_WEB_REFRESH_TTL', 864000),
    'native_refresh_ttl' => env('JWT_NATIVE_REFRESH_TTL', 2592000)
];
