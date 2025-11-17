<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->index(); // 'mercadopago', 'whatsapp', etc.
            $table->string('event_type', 100)->nullable()->index(); // 'payment', 'notification', etc.
            $table->string('status', 20)->default('pending')->index(); // 'success', 'error', 'rejected', 'pending'
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('request_id', 255)->nullable()->index();
            $table->boolean('signature_valid')->default(false)->index();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Índices compostos para consultas frequentes
            $table->index(['provider', 'status', 'created_at']);
            $table->index(['provider', 'signature_valid', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};

