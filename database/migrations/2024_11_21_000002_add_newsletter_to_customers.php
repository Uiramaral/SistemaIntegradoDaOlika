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
        Schema::table('customers', function (Blueprint $table) {
            // Adiciona o campo newsletter, padrão true (recebe notificações)
            if (!Schema::hasColumn('customers', 'newsletter')) {
                $table->boolean('newsletter')->default(true)->after('email')->comment('Receber notificações/newsletter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'newsletter')) {
                $table->dropColumn('newsletter');
            }
        });
    }
};

