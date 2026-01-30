<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->string('recipe_name')->comment('Nome da receita no momento da produção');
            $table->integer('quantity')->default(1)->comment('Quantidade produzida');
            $table->decimal('weight', 10, 2)->comment('Peso unitário em gramas');
            $table->decimal('total_produced', 10, 2)->comment('Total produzido (quantity * weight)');
            $table->date('production_date');
            $table->text('observation')->nullable();
            $table->decimal('cost', 10, 2)->default(0)->comment('Custo total da produção');
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->index(['client_id', 'production_date']);
            $table->index('recipe_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_records');
    }
};
