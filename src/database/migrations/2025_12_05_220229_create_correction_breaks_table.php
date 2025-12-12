<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('correction_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('correction_request_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->unsignedTinyInteger('break_no');
            $table->time('requested_break_start');
            $table->time('requested_break_end');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_breaks');
    }
};
