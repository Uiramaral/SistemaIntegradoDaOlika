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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('provider'); // 'pix', 'mercadopago', etc
            $table->string('provider_id')->nullable();
            $table->string('status'); // 'pending', 'paid', 'failed', 'refunded'
            $table->json('payload')->nullable();
            $table->text('pix_qr_base64')->nullable();
            $table->text('pix_copia_cola')->nullable();
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('provider');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

