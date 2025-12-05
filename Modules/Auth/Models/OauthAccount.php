<?php

namespace Modules\Auth\Models;

use App\Core\Database\Model;
use Modules\Users\Models\User;

class OauthAccount extends Model
{
    protected static string $table = 'oauth_accounts';

    protected array $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at'
    ];

    protected array $hidden = [
        'access_token',
        'refresh_token'
    ];

    /**
     * Get the user who owns this OAuth account
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Check if the access token is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return strtotime($this->expires_at) < time();
    }

    /**
     * Update tokens
     */
    public function updateTokens(string $accessToken, ?string $refreshToken = null, ?int $expiresIn = null): void
    {
        $data = [
            'access_token' => $accessToken
        ];

        if ($refreshToken) {
            $data['refresh_token'] = $refreshToken;
        }

        if ($expiresIn) {
            $data['expires_at'] = date('Y-m-d H:i:s', time() + $expiresIn);
        }

        $this->update($data);
    }
}
