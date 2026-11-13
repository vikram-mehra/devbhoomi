<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE pincode_serviceabilities MODIFY day_offset SMALLINT UNSIGNED NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE pincode_serviceabilities MODIFY day_offset SMALLINT UNSIGNED NOT NULL DEFAULT 3');
    }
};
