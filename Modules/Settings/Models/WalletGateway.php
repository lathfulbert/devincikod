<?php

namespace Modules\Settings\Models;

use App\Core\Database\Model;

class WalletGateway extends Model
{
    protected static string $table = 'wallet_gateways';

    protected array $fillable = [
        'name',
        'provider_code',
        'api_url',
        'api_key',
        'api_secret',
        'merchant_id',
        'is_active',
        'is_default',
        'currency',
        'transaction_fee',
        'configuration'
    ];

    protected array $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'transaction_fee' => 'float',
        'configuration' => 'json'
    ];

    // Encryption key from environment
    private static function getEncryptionKey(): string
    {
        return env('APP_KEY', 'default-encryption-key-change-me');
    }

    /**
     * Encrypt sensitive data before saving
     */
    public function setApiKeyAttribute($value): void
    {
        if ($value) {
            $this->attributes['api_key'] = $this->encrypt($value);
        }
    }

    public function setApiSecretAttribute($value): void
    {
        if ($value) {
            $this->attributes['api_secret'] = $this->encrypt($value);
        }
    }

    public function setMerchantIdAttribute($value): void
    {
        if ($value) {
            $this->attributes['merchant_id'] = $this->encrypt($value);
        }
    }

    /**
     * Decrypt sensitive data when retrieving
     */
    public function getApiKeyAttribute($value): ?string
    {
        return $value ? $this->decrypt($value) : null;
    }

    public function getApiSecretAttribute($value): ?string
    {
        return $value ? $this->decrypt($value) : null;
    }

    public function getMerchantIdAttribute($value): ?string
    {
        return $value ? $this->decrypt($value) : null;
    }

    /**
     * Encrypt a value
     */
    private function encrypt(string $value): string
    {
        $key = self::getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt a value
     */
    private function decrypt(string $value): string
    {
        $key = self::getEncryptionKey();
        list($encrypted, $iv) = explode('::', base64_decode($value), 2);
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }

    /**
     * Get all active gateways
     */
    public static function getActive(): array
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Get the default gateway
     */
    public static function getDefault(): ?self
    {
        return static::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Set this gateway as default (and unset others)
     */
    public function setAsDefault(): bool
    {
        // Unset all other defaults
        static::where('is_default', true)->update(['is_default' => false]);

        // Set this one as default
        $this->is_default = true;
        return $this->save();
    }
}
