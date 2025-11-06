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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('print_requested_at')->nullable()->after('scheduled_delivery_at');
            $table->timestamp('printed_at')->nullable()->after('print_requested_at');
            
            $table->index('print_requested_at');
            $table->index('printed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['print_requested_at']);
            $table->dropIndex(['printed_at']);
            $table->dropColumn(['print_requested_at', 'printed_at']);
        });
    }
};


