<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredients', 'client_id')) {
                $table->unsignedBigInteger('client_id')->nullable()->after('id');
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('ingredients', 'weight')) {
                $table->decimal('weight', 10, 2)->nullable()->default(0)->after('slug')->comment('Peso padrão em gramas');
            }
            
            if (!Schema::hasColumn('ingredients', 'percentage')) {
                $table->decimal('percentage', 8, 2)->nullable()->after('weight')->comment('Porcentagem padrão em receitas');
            }
            
            if (!Schema::hasColumn('ingredients', 'is_flour')) {
                $table->boolean('is_flour')->default(false)->after('percentage')->comment('Se é farinha');
            }
            
            if (!Schema::hasColumn('ingredients', 'has_hydration')) {
                $table->boolean('has_hydration')->default(false)->after('is_flour')->comment('Se tem hidratação');
            }
            
            if (!Schema::hasColumn('ingredients', 'hydration_percentage')) {
                $table->decimal('hydration_percentage', 5, 2)->nullable()->default(0)->after('has_hydration')->comment('Porcentagem de hidratação');
            }
            
            if (!Schema::hasColumn('ingredients', 'category')) {
                $table->string('category', 50)->nullable()->after('hydration_percentage')->comment('Categoria: farinha, outro, etc');
            }
            
            if (!Schema::hasColumn('ingredients', 'package_weight')) {
                $table->decimal('package_weight', 10, 2)->nullable()->after('category')->comment('Peso da embalagem em gramas');
            }
            
            if (!Schema::hasColumn('ingredients', 'cost')) {
                $table->decimal('cost', 10, 2)->nullable()->default(0)->after('package_weight')->comment('Custo por unidade/embalagem');
            }
            
            if (!Schema::hasColumn('ingredients', 'cost_history')) {
                $table->json('cost_history')->nullable()->after('cost')->comment('Histórico de custos');
            }
            
            if (!Schema::hasColumn('ingredients', 'unit')) {
                $table->string('unit', 20)->default('g')->after('cost_history')->comment('Unidade: g, kg, ml, l, un');
            }
            
            if (!Schema::hasColumn('ingredients', 'stock')) {
                $table->decimal('stock', 10, 2)->nullable()->default(0)->after('unit')->comment('Estoque atual');
            }
            
            if (!Schema::hasColumn('ingredients', 'min_stock')) {
                $table->decimal('min_stock', 10, 2)->nullable()->default(0)->after('stock')->comment('Estoque mínimo');
            }
            
            if (!Schema::hasColumn('ingredients', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('min_stock');
            }
            
            if (!Schema::hasColumn('ingredients', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $columns = [
                'client_id', 'weight', 'percentage', 'is_flour', 'has_hydration',
                'hydration_percentage', 'category', 'package_weight', 'cost',
                'cost_history', 'unit', 'stock', 'min_stock', 'is_active',
                'created_at', 'updated_at'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('ingredients', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
