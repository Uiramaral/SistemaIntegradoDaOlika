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
        Schema::create('ai_exceptions', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index(); // Número de telefone (apenas dígitos)
            $table->string('reason', 100)->nullable(); // Motivo da exceção (ex: 'image_received', 'video_received', 'manual_override')
            $table->boolean('active')->default(true)->index();
            $table->timestamp('expires_at')->nullable()->index(); // Expiração automática (ex: 5 minutos)
            $table->timestamps();
            
            // Índice composto para busca rápida
            $table->index(['phone', 'active', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_exceptions');
    }
};

