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
        Schema::table('plans', function (Blueprint $table) {
            // Adicionar coluna 'active' (renomear de 'is_active' se existir)
            if (Schema::hasColumn('plans', 'is_active')) {
                // Se existe is_active, renomear para active
                $table->renameColumn('is_active', 'active');
            } else {
                // Se não existe, criar nova coluna
                $table->boolean('active')->default(true)->after('features');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Reverter: renomear active para is_active
            if (Schema::hasColumn('plans', 'active')) {
                $table->renameColumn('active', 'is_active');
            }
        });
    }
};
