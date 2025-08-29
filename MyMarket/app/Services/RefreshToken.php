<?php

namespace App\Services;

use App\Models\Refreshtoken as ModelsRefreshtoken;
use App\Models\User;
use Illuminate\Support\Str;

class RefreshToken
{
    public function issue(User $user, $client_type): string
    {
        $plain = Str::uuid()->toString() . '.' . Str::random(64);

        $expire_at = $client_type === 'web' ? (int) config('jwt.web_refresh_ttl') : (int) config('jwt.native_refresh_ttl');

        ModelsRefreshtoken::create([
            'user_id'    => $user->id,
            'client_type' => $client_type,
            'token_hash' =>  hash('sha256', $plain),
            'expires_at' => now()->addSeconds($expire_at),
        ]);
        return $plain;
    }
    public function updateIssue($token)
    {
        $plain = Str::uuid()->toString() . '.' . Str::random(64);
        $hash =  hash('sha256', $token);

        $row = ModelsRefreshtoken::where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->first();

        if ($row) {
            $row->update([
                'token_hash' => hash('sha256', $plain),
            ]);
        }
        return $plain;
    }
    public function ValidateRefresh(string $plain): ?User
    {
        $hash = hash('sha256', $plain);

        $row = ModelsRefreshtoken::where('token_hash', $hash)
            ->whereNull('revoked_at')
            ->first();

        if (!$row) return null;

        if ($row->expires_at < now()) {
            $row->update(['revoked_at' => now()]);
            return null;
        }

        return User::find($row->user_id);
    }

    public function revokeAll(User $user): void
    {
        $q = ModelsRefreshtoken::where('user_id', $user->id)->whereNull('revoked_at');
        $q->update(['revoked_at' => now()]);
    }
}
