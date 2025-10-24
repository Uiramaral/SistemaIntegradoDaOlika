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
        Schema::create('delivery_fees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('base_fee', 8, 2);
            $table->decimal('fee_per_km', 8, 2);
            $table->decimal('minimum_order_value', 8, 2)->default(0.00);
            $table->decimal('free_delivery_threshold', 8, 2)->nullable();
            $table->decimal('max_distance_km', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('delivery_time_minutes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_fees');
    }
};
