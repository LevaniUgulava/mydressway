<?php

namespace App\Repository\UserStatus;

use App\Models\Userstatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserStatusRepository implements UserStatusRepositoryInterface
{
    public function display()
    {
        try {
            $status = Userstatus::all();
            return response()->json([
                'statuses' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => "Error to display",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create(array $data)
    {
        try {
            $status = Userstatus::create($data);

            return response()->json([
                'message' => 'Status created successfully',
                'status' => $status,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $status = Userstatus::findorfail($id);
            $status->delete();
            return response()->json([
                'message' => 'Status deleted successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function StatuswithUser($id)
    {
        try {
            $status = Userstatus::with('Users')->findorfail($id);
            return response()->json([
                'status' => $status,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function displayStatus()
    {
        $user = Auth::user();
        $status = $user->userstatus()->select('name', "time")->first();
        return $status;
    }
}
