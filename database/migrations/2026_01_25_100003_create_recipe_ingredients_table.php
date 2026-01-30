<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_step_id')->constrained('recipe_steps')->onDelete('cascade');
            $table->unsignedInteger('ingredient_id');
            $table->string('type', 20)->default('ingredient')->comment('ingredient, levain, etc');
            $table->decimal('percentage', 8, 2)->nullable()->comment('Porcentagem em relação à farinha');
            $table->decimal('weight', 10, 2)->nullable()->comment('Peso em gramas (calculado ou fixo)');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
            $table->index(['recipe_step_id', 'sort_order']);
            $table->index('ingredient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
