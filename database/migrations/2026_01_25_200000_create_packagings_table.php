<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packagings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('client_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('client_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packagings');
    }
};
