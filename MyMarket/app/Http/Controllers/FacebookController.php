<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use App\Services\RefreshToken;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function authenticate(Request $request, JwtService $jwt, RefreshToken $refresh)
    {
        $request->validate([
            'accessToken' => 'required|string',
        ]);

        $accessToken = $request->input('accessToken');

        try {
            $fbUser = Socialite::driver('facebook')->fields(['first_name', 'last_name', 'email', 'name'])
                ->userFromToken($accessToken);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return response()->json(['error' => 'Unauthorized. Invalid state.'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized. Invalid access token.'], 401);
        }


        $user = User::where('facebook_id', $fbUser->id)->first();

        if (!$user) {
            $user = new User();
            $user->name = $fbUser->user['first_name'];
            $user->surname = $fbUser->user['last_name'];
            $user->email = $fbUser->user['email'];
            $user->facebook_id = $fbUser->id;
            $user->email_verified_at = now();
            $user->save();
            $user->assignRole('default');
        }
        $client_type = $request->client_type;
        $access = $jwt->accessToken($user->id);
        $newRt = $refresh->issue($user, $client_type);

        $expire_at = $client_type === 'web' ? config('jwt.web_refresh_ttl') : config('jwt.native_refresh_ttl');
        return response()->json([
            'refresh_token' => $newRt,
            'access_token'  => $access,
            'token_type'    => 'Bearer',
            'expires_in'    => config('jwt.access_ttl'),
        ])->cookie(
            'refresh_token',
            $newRt,
            (int)($expire_at / 60),
            '/',
            null,
            false,
            true,
            false,
            'lax'
        );
    }
}
