<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceBreaksTable extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->time('start_at');
            $table->time('end_at');
            $table->unsignedTinyInteger('order')->default(1); // 第何回目の休憩か
            $table->timestamps();

            $table->foreign('attendance_id')
                ->references('id')->on('attendances')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
    }
};