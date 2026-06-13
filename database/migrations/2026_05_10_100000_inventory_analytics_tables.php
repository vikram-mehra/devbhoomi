<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(0);
            $table->unsignedInteger('damaged_qty')->default(0);
            $table->timestamps();
            $table->unique(['warehouse_id', 'product_variant_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('type', 48);
            $table->integer('qty_delta');
            $table->unsignedInteger('balance_after')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('return_id')->nullable();
            $table->foreign('return_id')->references('id')->on('returns')->nullOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['product_variant_id', 'created_at']);
            $table->index(['order_id', 'type']);
        });

        Schema::create('inventory_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 96);
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });

        Schema::create('sales_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();
            $table->unsignedInteger('orders_count')->default(0);
            $table->unsignedInteger('items_qty')->default(0);
            $table->decimal('gross_revenue', 14, 2)->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('units_sold')->default(0);
            $table->decimal('revenue', 14, 2)->default(0);
            $table->unsignedInteger('units_returned')->default(0);
            $table->timestamp('last_sale_at')->nullable();
            $table->timestamp('refreshed_at')->nullable();
            $table->timestamps();
            $table->unique('product_id');
        });

        $wid = DB::table('warehouses')->insertGetId([
            'name' => 'Main warehouse',
            'slug' => 'main',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variants = DB::table('product_variants')->select('id', 'stock_qty')->get();
        foreach ($variants as $v) {
            DB::table('warehouse_stocks')->insert([
                'warehouse_id' => $wid,
                'product_variant_id' => $v->id,
                'qty' => (int) $v->stock_qty,
                'damaged_qty' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_analytics');
        Schema::dropIfExists('sales_reports');
        Schema::dropIfExists('inventory_logs');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('warehouse_stocks');
        Schema::dropIfExists('warehouses');
    }
};
