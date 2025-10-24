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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('Olika');
            $table->text('business_description')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_full_address')->nullable();
            $table->decimal('business_latitude', 10, 8)->nullable();
            $table->decimal('business_longitude', 11, 8)->nullable();
            $table->boolean('is_open')->default(true);
            $table->string('primary_color')->default('#FF6B35');
            $table->string('logo_url')->nullable();
            $table->string('header_image_url')->nullable();
            $table->decimal('min_delivery_value', 8, 2)->default(0.00);
            $table->decimal('free_delivery_threshold', 8, 2)->default(50.00);
            $table->decimal('delivery_fee_per_km', 8, 2)->default(2.50);
            $table->decimal('max_delivery_distance', 8, 2)->default(15.00);
            $table->string('mercadopago_access_token')->nullable();
            $table->string('mercadopago_public_key')->nullable();
            $table->enum('mercadopago_env', ['sandbox', 'production'])->default('sandbox');
            $table->string('google_maps_api_key')->nullable();
            $table->string('openai_api_key')->nullable();
            $table->string('whatsapp_api_url')->nullable();
            $table->string('whatsapp_api_key')->nullable();
            $table->boolean('loyalty_enabled')->default(false);
            $table->decimal('loyalty_points_per_real', 8, 2)->default(1.00);
            $table->decimal('cashback_percentage', 5, 2)->default(5.00);
            $table->time('order_cutoff_time')->nullable();
            $table->integer('advance_order_days')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
