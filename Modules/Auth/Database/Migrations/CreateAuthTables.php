<?php

namespace Modules\Auth\Database\Migrations;

use App\Core\Database\Migration;
use App\Core\Database\Blueprint;
use App\Core\Database\Schema;

class CreateAuthTables extends Migration
{
    public function up(): void
    {
        $db = \App\Core\Database\Database::getInstance();

        // Update users table with raw SQL
        try {
            $db->query("ALTER TABLE users 
                ADD COLUMN status VARCHAR(255) DEFAULT 'active' AFTER password,
                ADD COLUMN last_login_ip VARCHAR(255) NULL,
                ADD COLUMN last_login_at TIMESTAMP NULL,
                ADD COLUMN device_fingerprint VARCHAR(255) NULL
            ");
        } catch (\PDOException $e) {
            // Ignore if columns already exist
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
        }

        // Create mfa_methods table
        Schema::create('mfa_methods', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique(); // sms, email, totp, oauth, ldap, webauthn
            $table->string('provider_class');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create user_mfa_setup table
        Schema::create('user_mfa_setup', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('method_type'); // references mfa_methods.type
            $table->text('secret')->nullable(); // Encrypted secret or identifier
            $table->boolean('is_verified')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'method_type']);
        });

        // Create oauth_accounts table
        Schema::create('oauth_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider'); // google, microsoft, etc.
            $table->string('provider_user_id');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_user_id']);
        });

        // Create auth_logs table
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type'); // login, mfa_success, mfa_failed, lockout
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Seed default MFA methods
        $this->seedMfaMethods();
    }

    public function down(): void
    {
        $db = \App\Core\Database\Database::getInstance();

        Schema::dropIfExists('auth_logs');
        Schema::dropIfExists('oauth_accounts');
        Schema::dropIfExists('user_mfa_setup');
        Schema::dropIfExists('mfa_methods');

        // Drop columns from users table
        try {
            $db->query("ALTER TABLE users 
                DROP COLUMN status,
                DROP COLUMN last_login_ip,
                DROP COLUMN last_login_at,
                DROP COLUMN device_fingerprint
            ");
        } catch (\PDOException $e) {
            // Ignore if columns don't exist
        }
    }

    private function seedMfaMethods()
    {
        $methods = [
            ['type' => 'password', 'provider_class' => 'Modules\\Auth\\Providers\\PasswordProvider', 'is_active' => 1],
            ['type' => 'sms', 'provider_class' => 'Modules\\Auth\\Providers\\SmsOtpProvider', 'is_active' => 1],
            ['type' => 'email', 'provider_class' => 'Modules\\Auth\\Providers\\EmailOtpProvider', 'is_active' => 1],
            ['type' => 'totp', 'provider_class' => 'Modules\\Auth\\Providers\\TotpProvider', 'is_active' => 1],
            ['type' => 'oauth', 'provider_class' => 'Modules\\Auth\\Providers\\OauthProvider', 'is_active' => 1],
            ['type' => 'ldap', 'provider_class' => 'Modules\\Auth\\Providers\\LdapProvider', 'is_active' => 1],
            ['type' => 'webauthn', 'provider_class' => 'Modules\\Auth\\Providers\\WebAuthnProvider', 'is_active' => 1],
        ];

        $db = \App\Core\Database\Database::getInstance();
        foreach ($methods as $method) {
            $db->query("INSERT INTO mfa_methods (type, provider_class, is_active, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [
                $method['type'],
                $method['provider_class'],
                $method['is_active']
            ]);
        }
    }
}
