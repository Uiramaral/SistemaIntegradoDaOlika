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
        Schema::create('whatsapp_failed_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->string('recipient_phone', 20);
            $table->text('message');
            $table->string('error_message')->nullable();
            $table->string('error_type')->nullable(); // 'connection', 'api_error', 'invalid_response', etc
            $table->integer('attempt_count')->default(1);
            $table->enum('status', ['pending', 'retrying', 'failed', 'sent'])->default('pending');
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable(); // Armazena dados adicionais como instance_name, etc
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('status');
            $table->index('recipient_phone');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_failed_messages');
    }
};

