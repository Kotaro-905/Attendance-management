<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $cols = [];
        if (Schema::hasColumn('correction_requests', 'requested_break_start_time')) {
            $cols[] = 'requested_break_start_time';
        }
        if (Schema::hasColumn('correction_requests', 'requested_break_end_time')) {
            $cols[] = 'requested_break_end_time';
        }

        if (!empty($cols)) {
            Schema::table('correction_requests', function (Blueprint $table) use ($cols) {
                foreach ($cols as $col) {
                    if (Schema::hasColumn('correction_requests', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('correction_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('correction_requests', 'requested_break_start_time')) {
                $table->time('requested_break_start_time')->nullable();
            }
            if (!Schema::hasColumn('correction_requests', 'requested_break_end_time')) {
                $table->time('requested_break_end_time')->nullable();
            }
        });
    }
};
