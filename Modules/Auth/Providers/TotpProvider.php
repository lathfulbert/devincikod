<?php

namespace Modules\Auth\Providers;

use Modules\Users\Models\User;
use Modules\Auth\Models\UserMfaSetup;
use Modules\Auth\Models\AuthLog;

/**
 * TOTP Provider (Google Authenticator / Authy)
 * Implements Time-based One-Time Password authentication
 */
class TotpProvider implements AuthProviderInterface
{
    public function getName(): string
    {
        return 'totp';
    }

    /**
     * Verify TOTP code
     * 
     * @param mixed $identifier User ID
     * @param mixed $credential 6-digit TOTP code
     * @return array|false
     */
    public function verify($identifier, $credential)
    {
        $userId = is_numeric($identifier) ? (int)$identifier : null;

        if (!$userId) {
            return false;
        }

        // Get user's TOTP setup
        $setup = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', 'totp')
            ->where('is_verified', 1)
            ->first();

        if (!$setup) {
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'totp',
                'reason' => 'not_setup'
            ]);
            return false;
        }

        // Verify TOTP code
        $secret = $setup->getSecret();
        $isValid = $this->verifyTotpCode($secret, $credential);

        if (!$isValid) {
            AuthLog::logEvent($userId, AuthLog::EVENT_MFA_FAILED, null, null, [
                'method' => 'totp',
                'reason' => 'invalid_code'
            ]);
            return false;
        }

        // Log success
        AuthLog::logEvent($userId, AuthLog::EVENT_MFA_SUCCESS, null, null, [
            'method' => 'totp'
        ]);

        // Mark as used
        $setup->markAsUsed();

        $user = User::find($userId);
        return $user ? $user->toArray() : false;
    }

    /**
     * Setup TOTP for a user
     * Generates secret and returns QR code data
     * 
     * @param int $userId
     * @param array $data
     * @return array ['secret' => '...', 'qr_code_url' => '...', 'backup_codes' => [...]]
     */
    public function setup(int $userId, array $data): array
    {
        $user = User::find($userId);

        if (!$user) {
            throw new \Exception("User not found");
        }

        // Generate secret (base32 encoded)
        $secret = $this->generateSecret();

        // Check if already setup
        $existing = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', 'totp')
            ->first();

        if ($existing) {
            // Update existing
            $existing->update([
                'secret' => $secret,
                'is_verified' => 0 // Needs re-verification
            ]);
        } else {
            // Create new
            $setup = new UserMfaSetup();
            $setup->user_id = $userId;
            $setup->method_type = 'totp';
            $setup->secret = $secret;
            $setup->is_verified = 0;
            $setup->save();
        }

        // Generate QR code URL
        $appName = $_ENV['APP_NAME'] ?? 'LathDevinci';
        $accountName = $user->email;
        $qrCodeUrl = $this->getQrCodeUrl($appName, $accountName, $secret);

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();

        return [
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'backup_codes' => $backupCodes,
            'message' => 'Scan the QR code with your authenticator app'
        ];
    }

    /**
     * Verify the setup with a test code
     */
    public function verifySetup(int $userId, string $code): bool
    {
        $setup = UserMfaSetup::query()
            ->where('user_id', $userId)
            ->where('method_type', 'totp')
            ->first();

        if (!$setup) {
            return false;
        }

        $isValid = $this->verifyTotpCode($setup->getSecret(), $code);

        if ($isValid) {
            $setup->update(['is_verified' => 1]);
        }

        return $isValid;
    }

    public function isAvailable(): bool
    {
        return true; // TOTP is always available
    }

    /**
     * Generate a random base32 secret
     */
    private function generateSecret(int $length = 32): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= $base32Chars[random_int(0, 31)];
        }

        return $secret;
    }

    /**
     * Verify TOTP code against secret
     */
    private function verifyTotpCode(string $secret, string $code, int $window = 1): bool
    {
        $timeSlice = floor(time() / 30);

        // Check current time slice and ±window
        for ($i = -$window; $i <= $window; $i++) {
            $calculatedCode = $this->getTotpCode($secret, $timeSlice + $i);

            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate TOTP code for a given time slice
     */
    private function getTotpCode(string $secret, int $timeSlice): string
    {
        $key = $this->base32Decode($secret);
        $time = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $time, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Decode base32 string
     */
    private function base32Decode(string $secret): string
    {
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32CharsFlipped = array_flip(str_split($base32Chars));

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = [6, 4, 3, 1, 0];

        if (!in_array($paddingCharCount, $allowedValues)) {
            return '';
        }

        for ($i = 0; $i < 4; $i++) {
            if (
                $paddingCharCount == $allowedValues[$i] &&
                substr($secret, - ($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])
            ) {
                return '';
            }
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = '';

        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = '';
            if (!in_array($secret[$i], $base32CharsFlipped)) {
                return '';
            }
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32CharsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : '';
            }
        }

        return $binaryString;
    }

    /**
     * Get QR code URL for Google Authenticator
     */
    private function getQrCodeUrl(string $appName, string $accountName, string $secret): string
    {
        $otpauthUrl = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            rawurlencode($appName),
            rawurlencode($accountName),
            $secret,
            rawurlencode($appName)
        );

        // Use Google Charts API for QR code generation
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($otpauthUrl);
    }

    /**
     * Generate backup codes
     */
    private function generateBackupCodes(int $count = 10): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $codes[] = substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(6))), 0, 8);
        }

        return $codes;
    }
}
