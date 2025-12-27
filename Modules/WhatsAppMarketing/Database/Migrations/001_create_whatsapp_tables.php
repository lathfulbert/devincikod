<?php

use App\Core\Database\Migration;
use App\Core\Database\Blueprint;
use App\Core\Database\Schema;

class CreateWhatsappTables extends Migration
{
    public function up(): void
    {
        // Gateways configuration
        Schema::create('whatsapp_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider'); // twilio, wati, 360dialog
            $table->text('credentials'); // JSON encrypted
            $table->string('phone_number')->nullable();
            $table->string('webhook_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Templates (synchronized from provider)
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_id')->constrained('whatsapp_gateways')->onDelete('cascade');
            $table->string('name');
            $table->string('category')->nullable(); // MARKETING, UTILITY, AUTHENTICATION
            $table->string('language')->default('fr');
            $table->text('components'); // JSON structure of header, body, footer, buttons
            $table->string('status'); // APPROVED, REJECTED, PENDING
            $table->string('external_id')->nullable(); // ID from provider
            $table->timestamps();
        });

        // Campaigns
        Schema::create('whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_id')->constrained('whatsapp_templates');
            $table->json('audiences')->nullable(); // Segment filters
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, processing, completed, failed
            $table->integer('total_recipients')->default(0);
            $table->integer('total_sent')->default(0);
            $table->integer('total_delivered')->default(0);
            $table->integer('total_read')->default(0);
            $table->integer('total_failed')->default(0);
            $table->timestamps();
        });

        // Messages log
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gateway_id')->constrained('whatsapp_gateways');
            $table->foreignId('campaign_id')->nullable()->constrained('whatsapp_campaigns')->onDelete('set null');
            $table->foreignId('contact_id')->nullable(); // Link to Contacts module
            $table->string('recipient_phone');
            $table->text('content')->nullable(); // If text message
            $table->json('template_params')->nullable(); // If template message
            $table->string('message_id')->nullable(); // Provider ID
            $table->string('status')->default('pending'); // pending, sent, delivered, read, failed
            $table->string('error_message')->nullable();
            $table->decimal('cost', 10, 4)->default(0);
            $table->integer('segments')->default(1);
            $table->timestamps();
        });

        // WhatsApp specific contact data (opt-in)
        Schema::create('whatsapp_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
            $table->boolean('is_opted_in')->default(false);
            $table->timestamp('opted_in_at')->nullable();
            $table->string('opt_in_source')->nullable();
            $table->timestamp('last_interaction_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_contacts');
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_campaigns');
        Schema::dropIfExists('whatsapp_templates');
        Schema::dropIfExists('whatsapp_gateways');
    }
}
