<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('name');
            $table->string('category', 100)->nullable();
            $table->decimal('total_weight', 10, 2)->default(0)->comment('Peso total em gramas');
            $table->decimal('hydration', 5, 2)->default(70)->comment('Porcentagem de hidratação');
            $table->decimal('levain', 5, 2)->default(30)->comment('Porcentagem de levain');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('use_milk_instead_of_water')->default(false);
            $table->boolean('is_fermented')->default(true);
            $table->boolean('is_bread')->default(true);
            $table->boolean('include_notes_in_print')->default(false);
            $table->decimal('packaging_cost', 10, 2)->default(0.5)->comment('Custo de embalagem');
            $table->decimal('final_price', 10, 2)->nullable()->comment('Preço final de venda');
            $table->decimal('resale_price', 10, 2)->nullable()->comment('Preço de revenda');
            $table->decimal('cost', 10, 2)->default(0)->comment('Custo total calculado');
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->index(['client_id', 'is_active']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
