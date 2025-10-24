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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('visitor_id', 128)->nullable();
            $table->string('order_number', 20);
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('coupon_code', 64)->nullable();
            $table->decimal('final_amount', 10, 2);
            $table->string('payment_method', 30);
            $table->string('payment_provider', 30)->nullable();
            $table->string('preference_id', 64)->nullable();
            $table->string('payment_id', 64)->nullable();
            $table->text('payment_link')->nullable();
            $table->text('pix_copy_paste')->nullable();
            $table->mediumText('pix_qr_base64')->nullable();
            $table->datetime('pix_expires_at')->nullable();
            $table->mediumText('payment_raw_response')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('delivery_type', ['pickup', 'delivery'])->default('pickup');
            $table->text('delivery_address')->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->integer('estimated_time')->nullable()->comment('Tempo estimado em minutos');
            $table->text('notes')->nullable();
            $table->string('delivery_complement')->nullable();
            $table->string('delivery_neighborhood')->nullable();
            $table->text('observations')->nullable();
            $table->timestamp('scheduled_delivery_at')->nullable();
            $table->timestamps();
            
            $table->unique('order_number');
            $table->index(['customer_id', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('visitor_id');
            $table->index('payment_id');
            $table->index('preference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
