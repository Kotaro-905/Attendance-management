<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCorrectionRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('correction_requests', function (Blueprint $table) {
            $table->id(); // unsigned bigint, PK

            // 対象の勤怠レコード
            $table->foreignId('attendance_id')
                ->constrained('attendances')
                ->onDelete('cascade');

            // 申請した一般ユーザー
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // 対応した管理者（未対応なら NULL）
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // 修正後の勤務日（必要なら）
            $table->date('requested_work_date')->nullable();

            // 修正希望の各時刻（NULL 可）
            $table->time('requested_clock_in_time')->nullable();
            $table->time('requested_break_start_time')->nullable();
            $table->time('requested_break_end_time')->nullable();
            $table->time('requested_clock_out_time')->nullable();

            // 修正理由
            $table->string('reason', 255);

            // 0:承認待ち,1:承認,2:却下
            $table->tinyInteger('status')->default(0);

            // 承認／却下を行った日時
            $table->timestamp('decided_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('correction_requests');
    }
}
