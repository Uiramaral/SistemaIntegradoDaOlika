<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Atualizar TODOS os registros do Gemini para ter max_tokens = 5000
        // Isso garante que mesmo registros com outros valores sejam atualizados
        DB::statement("
            UPDATE api_integrations
            SET settings = JSON_SET(
                COALESCE(settings, '{}'),
                '$.max_tokens', 5000
            )
            WHERE provider = 'gemini'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverter para 2048 (se necessário)
        DB::statement("
            UPDATE api_integrations
            SET settings = JSON_SET(
                COALESCE(settings, '{}'),
                '$.max_tokens', 2048
            )
            WHERE provider = 'gemini'
              AND JSON_EXTRACT(settings, '$.max_tokens') = 5000
        ");
    }
};
