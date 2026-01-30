<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->date('production_date');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->index(['client_id', 'production_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_lists');
    }
};
