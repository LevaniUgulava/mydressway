<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommentController extends Controller
{
    public function getcomment(Product $product)
    {
        $comment =  $product->commentusers()->get();


        $comment->map(function ($user) {
            return [
                'name' => $user->name,
                'pivot' => [
                    'comment' => $user->pivot->comment,
                    'created_at' => $user->pivot->created_at,
                ],
            ];
        });
        return response()->json($comment);
    }



    public function comment(Product $product, Request $request)
    {
        $user = Auth::user();
        $user->commentproduct()->attach($product->id, ['comment' => $request->comment]);
        return response()->json(['message' => 'Comment added successfully.']);
    }
}
