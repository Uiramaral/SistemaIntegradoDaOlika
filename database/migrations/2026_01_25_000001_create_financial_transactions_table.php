<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->index();
            $table->enum('type', ['revenue', 'expense']); // receita | despesa
            $table->decimal('amount', 12, 2);
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->string('category', 64)->nullable();
            $table->timestamps();

            $table->index(['client_id', 'type']);
            $table->index(['client_id', 'transaction_date']);
        });

        if (Schema::hasTable('clients')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
