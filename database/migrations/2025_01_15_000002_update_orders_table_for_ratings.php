<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adicionar relacionamento com order_ratings se necessário
        // A tabela order_ratings já tem foreign key para orders, então não precisa alterar orders
        // Mas vamos garantir que a migration de order_ratings está correta
    }

    public function down(): void
    {
        //
    }
};

