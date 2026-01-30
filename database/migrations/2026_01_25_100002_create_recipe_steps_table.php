<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->string('name')->default('Etapa 1');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['recipe_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_steps');
    }
};
