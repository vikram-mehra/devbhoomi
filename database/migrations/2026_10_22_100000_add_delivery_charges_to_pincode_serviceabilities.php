<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pincode_serviceabilities', function (Blueprint $table) {
            if (! Schema::hasColumn('pincode_serviceabilities', 'delivery_charge')) {
                $table->decimal('delivery_charge', 12, 2)->nullable()->after('courier_name');
            }
            if (! Schema::hasColumn('pincode_serviceabilities', 'additional_500g_charge')) {
                $table->decimal('additional_500g_charge', 12, 2)->nullable()->after('delivery_charge');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pincode_serviceabilities', function (Blueprint $table) {
            if (Schema::hasColumn('pincode_serviceabilities', 'additional_500g_charge')) {
                $table->dropColumn('additional_500g_charge');
            }
            if (Schema::hasColumn('pincode_serviceabilities', 'delivery_charge')) {
                $table->dropColumn('delivery_charge');
            }
        });
    }
};
