<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id(); // unsigned bigint, PK

            // 打刻したユーザー
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');

            // 勤務日（1ユーザー1日1レコード）
            $table->date('work_date');

            // 各時刻（未打刻のときは NULL）
            $table->time('clock_in_at')->nullable();      // 出勤時刻
            $table->time('break_start_at')->nullable();   // 休憩開始時刻
            $table->time('break_end_at')->nullable();     // 休憩終了時刻
            $table->time('clock_out_at')->nullable();     // 退勤時刻

            // 0:未出勤,1:勤務中,2:休憩中,3:退勤済
            $table->tinyInteger('status');

            // 備考
            $table->string('remarks', 255)->nullable();

            $table->timestamps();

            // 1ユーザーにつき、1日1レコード
            $table->unique(['user_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
