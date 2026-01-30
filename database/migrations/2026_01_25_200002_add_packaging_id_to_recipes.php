<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->unsignedBigInteger('packaging_id')->nullable()->after('packaging_cost');
            $table->foreign('packaging_id')->references('id')->on('packagings')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['packaging_id']);
            $table->dropColumn('packaging_id');
        });
    }
};
