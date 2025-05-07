<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'accessToken' => 'required|string',
        ]);

        $accessToken = $request->input('accessToken');

        try {
            $fbUser = Socialite::driver('facebook')->userFromToken($accessToken);
        } catch (\Laravel\Socialite\Two\InvalidStateException $e) {
            return response()->json(['error' => 'Unauthorized. Invalid state.'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized. Invalid access token.'], 401);
        }

        $existingUser = User::where('email', $fbUser->email)->first();

        if ($existingUser) {
            $token = $existingUser->createToken('mymarket')->plainTextToken;
            $roles = $existingUser->getRoleNames();
            return response()->json(['token' => $token, 'roles' => $roles]);
        } else {
            $newUser = new User();
            $newUser->name = $fbUser->name;
            $newUser->email = $fbUser->email;
            $newUser->save();

            $token = $newUser->createToken('mymarket')->plainTextToken;
            $newUser->assignRole('default');
            $roles = $newUser->getRoleNames();
            return response()->json(['token' => $token, 'roles' => $roles]);
        }
    }
}
