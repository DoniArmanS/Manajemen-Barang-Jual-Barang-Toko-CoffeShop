<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('daily_notes', function (Blueprint $t) {
            $t->id();
            $t->date('note_date')->unique(); // satu record per hari
            $t->longText('content')->nullable();
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('daily_notes');
    }
};
