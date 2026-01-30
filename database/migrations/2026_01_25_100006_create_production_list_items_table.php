<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_list_id')->constrained('production_lists')->onDelete('cascade');
            $table->foreignId('recipe_id')->constrained('recipes')->onDelete('cascade');
            $table->string('recipe_name')->comment('Nome da receita no momento da adição');
            $table->integer('quantity')->default(1);
            $table->decimal('weight', 10, 2)->comment('Peso unitário em gramas');
            $table->boolean('is_produced')->default(false);
            $table->timestamp('produced_at')->nullable();
            $table->text('observation')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['production_list_id', 'sort_order']);
            $table->index('recipe_id');
            $table->index('is_produced');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_list_items');
    }
};
