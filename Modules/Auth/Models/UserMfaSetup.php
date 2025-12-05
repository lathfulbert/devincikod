<?php

namespace Modules\Auth\Models;

use App\Core\Database\Model;
use Modules\Users\Models\User;

class UserMfaSetup extends Model
{
    protected static string $table = 'user_mfa_setup';

    protected array $fillable = [
        'user_id',
        'method_type',
        'secret',
        'is_verified',
        'last_used_at'
    ];

    protected array $casts = [
        'is_verified' => 'boolean'
    ];

    /**
     * Get the user who owns this MFA setup
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the MFA method
     */
    public function method()
    {
        return $this->belongsTo(MfaMethod::class, 'method_type', 'type');
    }

    /**
     * Mark this method as used
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Check if this setup is verified
     */
    public function isVerified(): bool
    {
        return (bool) $this->is_verified;
    }

    /**
     * Get encrypted secret
     */
    public function getSecret(): ?string
    {
        // TODO: Implement encryption/decryption
        return $this->secret;
    }

    /**
     * Set encrypted secret
     */
    public function setSecret(string $secret): void
    {
        // TODO: Implement encryption
        $this->secret = $secret;
    }
}
