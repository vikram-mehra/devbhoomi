<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EnhanceCouponUsageTracking extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            if (! Schema::hasColumn('coupons', 'per_user_limit')) {
                $table->unsignedInteger('per_user_limit')->nullable()->after('usage_limit');
            }
            if (! Schema::hasColumn('coupons', 'first_order_only')) {
                $table->boolean('first_order_only')->default(false)->after('is_active');
            }
        });

        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('order_id')->nullable()->constrained('orders')->cascadeOnDelete();
                $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
                $table->string('guest_key', 191)->nullable();
                $table->string('status', 16)->default('reserved');
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamp('released_at')->nullable();
                $table->timestamps();

                $table->unique(['coupon_id', 'order_id']);
                $table->unique(['coupon_id', 'sale_id']);
                $table->index(['coupon_id', 'status']);
                $table->index(['coupon_id', 'user_id', 'status']);
                $table->index(['coupon_id', 'guest_key', 'status']);
                $table->index(['sale_id', 'status']);
            });
        }

        if (! Schema::hasTable('coupon_product')) {
            Schema::create('coupon_product', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->unique(['coupon_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('coupon_menu_item')) {
            Schema::create('coupon_menu_item', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->cascadeOnDelete();
                $table->foreignId('menu_item_id')->constrained('menu_items')->cascadeOnDelete();
                $table->unique(['coupon_id', 'menu_item_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('coupon_menu_item');
        Schema::dropIfExists('coupon_product');
        Schema::dropIfExists('coupon_usages');

        Schema::table('coupons', function (Blueprint $table) {
            if (Schema::hasColumn('coupons', 'first_order_only')) {
                $table->dropColumn('first_order_only');
            }
            if (Schema::hasColumn('coupons', 'per_user_limit')) {
                $table->dropColumn('per_user_limit');
            }
        });
    }
}
