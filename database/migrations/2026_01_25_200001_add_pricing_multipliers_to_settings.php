<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('sales_multiplier', 5, 2)->default(3.5)->after('cashback_percentage')->comment('Multiplicador para cálculo de preço de venda');
            $table->decimal('resale_multiplier', 5, 2)->default(2.5)->after('sales_multiplier')->comment('Multiplicador para cálculo de preço de revenda');
            $table->decimal('fixed_cost', 10, 2)->default(0)->after('resale_multiplier')->comment('Custo fixo mensal');
            $table->decimal('tax_percentage', 5, 2)->default(0)->after('fixed_cost')->comment('Percentual de imposto');
            $table->decimal('card_fee_percentage', 5, 2)->default(6.0)->after('tax_percentage')->comment('Percentual de taxa de cartão');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'sales_multiplier',
                'resale_multiplier',
                'fixed_cost',
                'tax_percentage',
                'card_fee_percentage',
            ]);
        });
    }
};
