<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Deactivate;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Userstatus;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\DeleteAccountNotification;
use App\Notifications\RegisterNotification;
use App\Services\JwtService;
use App\Services\RefreshToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,

            ], 409);
        }

        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'userstatus_id' => 1,
        ]);

        $user->assignRole('default');
        $roles = $user->getRoleNames();

        return response()->json([
            'message' => 'Registered',
            'id' => $user->id,
            'roles' => $roles
        ], 200);
    }
    public function ResendVerification(Request $request)
    {

        $user = User::where("email", $request->email)->first();
        if (!$user) {
            return response()->json(["success" => false], 401);
        }
        $token = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        $check = $user->updateOrCreate(
            ['id' => $user->id],
            ['confirmation_token' => $token]
        );
        if ($check) {
            $user->notify(new CustomVerifyEmail($token));
            return response()->json(["success" => true], 200);
        }
        return response()->json(["success" => false], 500);
    }
    public function login(LoginRequest $request, JwtService $jwt, RefreshToken $refresh)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
            ], 404);
        }
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'action' => 'verify_email',
            ], 403);
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
    public function userInfo()
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();

        $hasPassword = $user->password !== null;

        if ($user->facebook_id) {
            $platform = 'Facebook';
        } elseif ($user->google_id) {
            $platform = 'Google';
        } else {
            $platform = null;
        }

        return response()->json([
            'user' => $user,
            'hasPassword' => [
                'password' => $hasPassword,
                'platform' => $platform
            ],
            'role' => $role
        ]);
    }

    public function refresh(Request $request, JwtService $jwt, RefreshToken $refresh)
    {
        $client_type = $request->client_type;
        $token = $request->cookie('refresh_token') ?? $request->input('refresh_token');
        if (!$token) {
            return response()->json([
                'message' => 'The refresh token is missing.'
            ], 422);
        }
        $user = $refresh->ValidateRefresh($token);
        if (!$user) return response()->json(['message' => 'Invalid refresh'], 403);
        $access  = $jwt->accessToken($user->id);
        $newRt   = $refresh->updateIssue($token);

        $expire_at = $client_type === 'web' ? config('jwt.web_refresh_ttl') : config('jwt.native_refresh_ttl');

        return response()->json([
            'access_token'  => $access,
            'refresh_token' => $newRt,
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

    public function adminlogin(Request $request, JwtService $jwt, RefreshToken $refresh)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }
        if ($user->hasRole('default')) {
            return response()->json([
                'message' => 'havenot got access',
            ], 403);
        }
        $access = $jwt->accessToken($user->id);

        $newRt = $refresh->issue($user, 'web');
        $roles = $user->getRoleNames();
        return response()->json([
            'refresh_token' => $newRt,
            'access_token'  => $access,
            'token_type'    => 'Bearer',
            'expires_in'    => config('jwt.access_ttl'),
            'roles' => $roles
        ])->cookie(
            'refresh_token',
            $newRt,
            (int)(config('jwt.web_refresh_ttl') / 60),
            '/',
            null,
            false,
            true,
            false,
            'lax'
        );
    }

    public function logout(RefreshToken $rt)
    {
        $user = Auth::user();
        $rt->revokeAll($user);
        return response()->json(['message' => true], 200)
            ->cookie('refresh_token', '', -1, '/auth/refresh', null, true, true, false, 'Strict');
    }


    public function verify(Request $request)
    {
        $user = User::where("email", $request->email)->where("confirmation_token", $request->otp)->first();

        if ($user) {
            $user->markEmailAsVerified();
            return response()->json(["success" => true], 200);
        }
        return response()->json(["success" => false], 500);
    }

    public function getuserstatus()
    {
        $user = Auth::user();
        $userstatus = $user->userstatus;
        $statuses = Userstatus::orderby('toachieve')->get();

        return response()->json([
            'status' => $userstatus,
            'user' => $user->total_spent,
            'statuses' => $statuses

        ]);
    }

    public function deleteAcc(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);
        $user = Auth::user();
        if ($user && Hash::check($request->password, $user->password)) {
            $result = $user->delete();
            if ($result) {
                return response()->json(["success" => true], 200);
            }
        }
        return response()->json(["success" => false], 500);
    }

    public function SendDeactivationCode()
    {
        $user = Auth::user();

        $token = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

        while (Deactivate::where('token', $token)->exists()) {
            $token = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        }

        $deactivate = Deactivate::updateOrCreate(
            ['user_id' => $user->id],
            [
                'token' => $token,
                'expires_at' => now()->addHours(1),
            ]
        );


        if ($deactivate) {
            $user->notify(new DeleteAccountNotification($token));

            return response()->json(['success' => true, 'message' => 'Deactivation email sent.'], 200);
        }

        return response()->json(['success' => false, 'message' => 'Failed to send deactivation email.'], 500);
    }

    public function verifyDeactivationCode(Request $request)
    {
        $user = Auth::user();
        $token = $request->input('code');
        $deactivationToken = Deactivate::where('token', $token)
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->first();

        if ($deactivationToken) {
            $deactivationToken->used = true;
            $deactivationToken->save();

            $user->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email'
        ]);

        Subscriber::create([
            'email' => $request->email,
            'verified' => false,
        ]);


        return response()->json(['message' => 'თქვენ წარმატებით გამოიწერეთ სიახლეები!']);
    }
}
