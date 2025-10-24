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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Inserir configurações padrão
        DB::table('payment_settings')->insert([
            [
                'key' => 'mercadopago_access_token',
                'value' => '',
                'description' => 'Token de acesso do MercadoPago',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mercadopago_public_key',
                'value' => '',
                'description' => 'Chave pública do MercadoPago',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'mercadopago_environment',
                'value' => 'sandbox',
                'description' => 'Ambiente do MercadoPago (sandbox/production)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'test_mode_enabled',
                'value' => 'false',
                'description' => 'Modo de teste ativado (valores entre 1-10 centavos)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'pix_expiration_minutes',
                'value' => '30',
                'description' => 'Tempo de expiração do PIX em minutos',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
