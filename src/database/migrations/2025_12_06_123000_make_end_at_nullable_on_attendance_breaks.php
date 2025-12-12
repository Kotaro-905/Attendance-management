<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL to modify column to nullable to avoid requiring doctrine/dbal
        DB::statement('ALTER TABLE attendance_breaks MODIFY end_at TIME NULL');
    }

    public function down(): void
    {
        // Revert to NOT NULL (set default to '00:00:00' for existing nulls first)
        DB::statement("UPDATE attendance_breaks SET end_at = '00:00:00' WHERE end_at IS NULL");
        DB::statement('ALTER TABLE attendance_breaks MODIFY end_at TIME NOT NULL');
    }
};
