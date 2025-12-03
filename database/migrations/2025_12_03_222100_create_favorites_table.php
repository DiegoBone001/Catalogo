<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Relación con el usuario
            $table->foreignId('product_id')->constrained()->cascadeOnDelete(); // Relación con el producto
            $table->primary(['user_id', 'product_id']); // Clave primaria compuesta
            $table->timestamps(); // Fecha y hora de creación y modificación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
