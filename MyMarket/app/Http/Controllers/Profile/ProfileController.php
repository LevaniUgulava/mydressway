<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ProductResource;
use App\Jobs\SendPasswordVerificationEmail;
use App\Models\Product;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use App\Notifications\ForgetPassword;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfileController extends Controller
{


    public function Updateprofile(Request $request)
    {
        try {
            $user = Auth::user();
            $request->validate([
                'email' => 'required|email|max:255',
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',

            ]);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'surname' => $request->surname,
            ]);
            return response()->json([
                'message' => "ინფორმაცია წარმატებით განახლდა",
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                "message" => $e->getMessage()
            ], 404);
        }
    }
    public function Updatepassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([], 404);
        }

        $hasPassword = $user->password !== null;

        $request->validate([
            'oldpassword' => $hasPassword ? 'required|string' : 'nullable|string',
            'newpassword' => 'required|string|min:8',
        ]);

        if (!$hasPassword) {
            $user->password = Hash::make($request->newpassword);
            $user->save();

            return response()->json('პაროლი წარმატებით დაყენდა', 200);
        }

        if (!Hash::check($request->oldpassword, $user->password)) {
            return response()->json('მიმდინარე პაროლი არასწორია', 403);
        }

        $user->password = Hash::make($request->newpassword);
        $user->save();

        return response()->json('პაროლი წარმატებით განახლდა', 200);
    }



    public function getprofile()
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user,
        ]);
    }
    public function likeproduct()
    {
        $user = Auth::user();
        $products = $user->manyproducts()->withAvg('rateproduct', 'rate')->get();
        $userStatus = optional(optional($user)->userstatusinfo)->userstatus;

        $likedProductIds = $user ? $user->manyproducts()->pluck('product_id')->toArray() : [];
        $products->transform(function ($product) use ($likedProductIds, $user, $userStatus) {
            $product->image_urls = $product->getMedia('default')->map(fn($media) => $media->getUrl());
            $product->isLiked = in_array($product->id, $likedProductIds);

            if ($user) {
                $isEligible =  $product->eligibleStatuses()->wherePivot('userstatus_id', $userStatus->id)->withPivot('discount')->first();
                if ($isEligible && $userStatus->isActive($user)) {
                    $product->discountstatus = [
                        'userstatus' => $user->userstatus,
                        'discount' => $isEligible->pivot->discount
                    ];
                    $product->discountstatusprice = $user->getPriceByStatus($product, $product->price, $product->price);
                } else {
                    $product->discountstatus = null;
                    $product->discountstatusprice = $product->discountprice;
                }
            } else {
                $product->discountstatus = null;
                $product->discountstatusprice = $product->discountprice;
            }

            return $product;
        });
        return ProductResource::collection($products);
    }


    public function ResetPassword(Request $request)
    {
        $request->validate([
            "email" => [
                "required",
                "email",
                "max:255",
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ]
        ]);

        $user = User::where("email", $request->email)->first();

        if ($user) {
            $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $otp,
                    'created_at' => now()
                ]
            );
            // $user->notify(new ForgetPassword($otp));
            dispatch(new SendPasswordVerificationEmail($otp, $user));
            return response()->json([
                "message" => [
                    "success" => true,
                    'messagekey' => "MailSented"
                ]
            ], 200);
        }
        return response()->json([
            "message" => [
                "success" => false,
                'messagekey' => "UsernotExist"
            ]
        ], 500);
    }
    public function checkOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'messagekey' => 'UserNotFound',
            ], 404);
        }
        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->where('token', $request->otp)
            ->first();

        if (!$record) {
            return response()->json([
                'success' => false,
                'messagekey' => 'InvalidToken',
            ], 400);
        }

        if (Carbon::parse($record->created_at)->addMinutes(10)->isPast()) {
            return response()->json([
                'success' => false,
                'messagekey' => 'TokenExpired',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'messagekey' => 'TokenValid',
        ]);
    }


    public function ForgotPassword(Request $request)
    {
        $request->validate([
            "password" => "required|string|min:8|max:255",
            "email" => [
                "required",
                "email",
                "max:255",
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ]
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "User with this email not found."
            ], 404);
        }

        $check = $user->update([
            "password" => Hash::make($request->password)
        ]);

        if ($check) {
            return response()->json([
                "success" => true,
                "message" => "Password updated successfully."
            ], 200);
        }

        return response()->json([
            "success" => false,
            "message" => "Failed to update password."
        ], 500);
    }
}
