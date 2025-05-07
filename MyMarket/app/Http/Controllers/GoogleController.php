<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Google_Client;


class GoogleController extends Controller
{


    public function callback(Request $request)
    {
        try {
            $googleToken = $request->input('token');

            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

            $payload = $client->verifyIdToken($googleToken);

            if ($payload) {
                $googleId = $payload['sub'];
                $email = $payload['email'];
                $name = $payload['name'];

                Log::info('Google User: ' . json_encode($payload));

                $user = User::firstOrCreate(
                    ['google_id' => $googleId],
                    ['name' => $name, 'email' => $email]
                );
                $user->assignRole('default');
                $roles = $user->getRoleNames();

                $authToken = $user->createToken('myapp')->plainTextToken;

                return response()->json(['token' => $authToken, 'name' => $name, 'email' => $email, 'roles' => $roles], 200);
            } else {
                return response()->json(['error' => 'Invalid token'], 401);
            }
        } catch (\Exception $e) {
            Log::error('Error during Google OAuth: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to authenticate.'], 401);
        }
    }
}
