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
        Schema::table('whatsapp_campaigns', function (Blueprint $table) {
            // Filtros combinados
            if (!Schema::hasColumn('whatsapp_campaigns', 'filter_newsletter')) {
                $table->boolean('filter_newsletter')->default(false)->after('target_audience');
            }
            if (!Schema::hasColumn('whatsapp_campaigns', 'filter_customer_type')) {
                $table->enum('filter_customer_type', ['all', 'new_customers', 'existing_customers'])->default('all')->after('filter_newsletter');
            }
            
            // Cliente único para testes
            if (!Schema::hasColumn('whatsapp_campaigns', 'test_customer_id')) {
                $table->foreignId('test_customer_id')->nullable()->constrained('customers')->onDelete('set null')->after('filter_customer_type');
            }
            
            // Agendamento
            if (!Schema::hasColumn('whatsapp_campaigns', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('test_customer_id');
            }
            if (!Schema::hasColumn('whatsapp_campaigns', 'scheduled_time')) {
                $table->time('scheduled_time')->nullable()->after('scheduled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('whatsapp_campaigns', 'filter_newsletter')) {
                $table->dropColumn('filter_newsletter');
            }
            if (Schema::hasColumn('whatsapp_campaigns', 'filter_customer_type')) {
                $table->dropColumn('filter_customer_type');
            }
            if (Schema::hasColumn('whatsapp_campaigns', 'test_customer_id')) {
                $table->dropForeign(['test_customer_id']);
                $table->dropColumn('test_customer_id');
            }
            if (Schema::hasColumn('whatsapp_campaigns', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
            if (Schema::hasColumn('whatsapp_campaigns', 'scheduled_time')) {
                $table->dropColumn('scheduled_time');
            }
        });
    }
};

