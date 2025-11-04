<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->date('log_date')->index();               // tanggal harian (reset per hari)
            $t->string('source')->default('inventory');  // sumber modul
            $t->string('action');                        // create/update/delete/adjust
            $t->string('item_name')->nullable();         // nama item terkait
            $t->integer('qty_change')->nullable();       // delta stok (bisa null utk create/delete/edit)
            $t->text('note')->nullable();                // catatan singkat
            $t->json('meta')->nullable();                // data tambahan
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
