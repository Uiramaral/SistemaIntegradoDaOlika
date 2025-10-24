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
        Schema::create('order_delivery_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('delivery_fee_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('calculated_fee', 8, 2)->default(0.00);
            $table->decimal('final_fee', 8, 2)->default(0.00);
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('order_value', 8, 2);
            $table->boolean('is_free_delivery')->default(false);
            $table->boolean('is_manual_adjustment')->default(false);
            $table->text('adjustment_reason')->nullable();
            $table->string('adjusted_by')->nullable(); // Quem fez o ajuste (admin, sistema, etc.)
            $table->timestamps();
            
            $table->index(['order_id']);
            $table->index(['delivery_fee_id']);
            $table->index(['is_manual_adjustment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_delivery_fees');
    }
};
