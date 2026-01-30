<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'wholesale_only')) {
            return;
        }
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedTinyInteger('wholesale_only')->default(0)->after('is_active')
                ->comment('1 = exclusivo revenda (só is_wholesale=1), 0 = pode varejo');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('wholesale_only');
        });
    }
};
