<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('shipping_settings', 'base_weight_grams')) {
                $table->unsignedInteger('base_weight_grams')->default(500)->after('shipping_charge');
            }
            if (! Schema::hasColumn('shipping_settings', 'additional_500g_charge')) {
                $table->decimal('additional_500g_charge', 12, 2)->nullable()->after('base_weight_grams');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_settings', function (Blueprint $table) {
            if (Schema::hasColumn('shipping_settings', 'additional_500g_charge')) {
                $table->dropColumn('additional_500g_charge');
            }
            if (Schema::hasColumn('shipping_settings', 'base_weight_grams')) {
                $table->dropColumn('base_weight_grams');
            }
        });
    }
};
