<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products') && !Schema::hasColumn('products', 'weight_grams')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('weight_grams')->nullable()->after('price')->comment('Peso aproximado em gramas');
            });
        }

        if (Schema::hasTable('product_variants') && !Schema::hasColumn('product_variants', 'weight_grams')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->integer('weight_grams')->nullable()->after('price')->comment('Peso da variação em gramas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'weight_grams')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('weight_grams');
            });
        }

        if (Schema::hasTable('product_variants') && Schema::hasColumn('product_variants', 'weight_grams')) {
            Schema::table('product_variants', function (Blueprint $table) {
                $table->dropColumn('weight_grams');
            });
        }
    }
};


