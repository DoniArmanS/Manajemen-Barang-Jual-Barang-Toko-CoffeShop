<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // "Biji Kopi Arabica"
            $table->string('sku')->unique(); // "BEAN-AR"
            $table->string('category');      // "Bahan" | "Perlengkapan" | dst
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('min')->default(0);   // stok minimum
            $table->string('unit', 20)->default('pcs');   // kg, L, pcs
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
