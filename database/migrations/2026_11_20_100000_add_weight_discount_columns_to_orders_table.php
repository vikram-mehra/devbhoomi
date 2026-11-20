<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_original')) {
                $table->decimal('shipping_original', 10, 2)->nullable()->after('shipping');
            }
            if (! Schema::hasColumn('orders', 'shipping_discount_percent')) {
                $table->decimal('shipping_discount_percent', 5, 2)->default(0)->after('shipping_original');
            }
            if (! Schema::hasColumn('orders', 'shipping_discount_amount')) {
                $table->decimal('shipping_discount_amount', 10, 2)->default(0)->after('shipping_discount_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'shipping_original',
                'shipping_discount_percent',
                'shipping_discount_amount',
            ];

            $existing = array_values(array_filter($columns, fn ($col) => Schema::hasColumn('orders', $col)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
