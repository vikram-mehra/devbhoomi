<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Orders were stored while APP_TIMEZONE was UTC. Shift existing rows to IST (+5:30).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (config('app.timezone') !== 'Asia/Kolkata') {
            return;
        }

        DB::statement('UPDATE orders SET created_at = DATE_ADD(created_at, INTERVAL 330 MINUTE), updated_at = DATE_ADD(updated_at, INTERVAL 330 MINUTE)');
    }

    public function down(): void
    {
        DB::statement('UPDATE orders SET created_at = DATE_SUB(created_at, INTERVAL 330 MINUTE), updated_at = DATE_SUB(updated_at, INTERVAL 330 MINUTE)');
    }
};
