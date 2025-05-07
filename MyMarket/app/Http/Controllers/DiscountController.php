<?php

namespace App\Http\Controllers;

use App\Models\Userstatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscountController extends Controller
{
    public function updateStatus($user, $totalspent)
    {
        $status = Userstatus::where('toachieve', '<=', $totalspent)
            ->orderBy('toachieve', 'desc')
            ->first();


        if ($status && $status->id !== $user->userstatus_id) {
            $user->update([
                'userstatus_id' => $status->id
            ]);
        }
    }
}
