<?php

namespace App\Http\Controllers;

use App\Models\RateProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RateContorller extends Controller
{
    public function SendRate($id, Request $request)
    {
        $request->validate([
            'rate' => 'required|numeric|between:0.5,5.0',
        ]);
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'User not authenticated.'], 401);
            }
            RateProduct::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $id,
                ],
                [
                    'rate' => $request->rate
                ]
            );
            return response()->json(['message' => 'Rating submitted successfully.'], 200);
        } catch (\Exception $e) {

            return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500);
        }
    }
}
