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
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('visibility', ['public', 'private', 'targeted'])->default('public')->after('is_active');
            $table->foreignId('target_customer_id')->nullable()->constrained('customers')->onDelete('cascade')->after('visibility');
            $table->text('private_description')->nullable()->after('target_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['target_customer_id']);
            $table->dropColumn(['visibility', 'target_customer_id', 'private_description']);
        });
    }
};
