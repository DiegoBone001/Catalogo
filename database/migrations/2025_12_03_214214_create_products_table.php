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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Clave primaria autoincremental
            $table->string('nombre', 100); // Nombre del producto
            $table->text('descripcion'); // Descripción del producto
            $table->decimal('precio', 10, 2); // Precio del producto
            $table->string('url_imagen', 500); // URL de la imagen del producto
            $table->timestamps(); // Fecha y hora de creación y modificación
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
