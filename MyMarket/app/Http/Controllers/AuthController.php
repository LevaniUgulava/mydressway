<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Deactivate;
use App\Models\User;
use App\Models\Userstatus;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\DeleteAccountNotification;
use App\Notifications\RegisterNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
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
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
            ], 401);
        }
        $status = $user->userstatus->name;

        $token = $user->createToken('api', [], now()->addDays(3))->plainTextToken;
        $roles = $user->getRoleNames();
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'token' => $token,
            'roles' => $roles,
            'status' => $status,
            'total_spent' => $user->total_spent,
            "number" => $user->phone_number
        ], 200);
    }

    public function adminlogin(Request $request)
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
        $token = $user->createToken('api')->plainTextToken;
        $roles = $user->getRoleNames();
        return response()->json([
            'name' => $user->name,
            'token' => $token,
            'roles' => $roles
        ]);
    }

    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
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
}
