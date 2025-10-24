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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('customers')->onDelete('cascade');
            $table->string('code')->unique();
            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            $table->decimal('reward_amount', 8, 2)->default(0.00);
            $table->enum('reward_type', ['points', 'cashback', 'discount'])->default('points');
            $table->datetime('expires_at')->nullable();
            $table->datetime('used_at')->nullable();
            $table->timestamps();
            
            $table->index(['referrer_id']);
            $table->index(['referred_id']);
            $table->index(['code']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
