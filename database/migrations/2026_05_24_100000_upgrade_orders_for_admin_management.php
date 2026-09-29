<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpgradeOrdersForAdminManagement extends Migration
{
    public function up()
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 32);
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city', 120);
            $table->string('state', 120);
            $table->string('pincode', 16);
            $table->timestamps();
            $table->index(['phone', 'pincode']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('address_id');
            $table->string('customer_phone', 32)->nullable()->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->foreignId('shipping_address_id')->nullable()->after('address_id')->constrained('shipping_addresses')->nullOnDelete();
            $table->decimal('tax_amount', 12, 2)->default(0)->after('discount');
            $table->string('transaction_id')->nullable()->after('payment_ref');
            $table->string('razorpay_payment_id')->nullable()->after('transaction_id');
            $table->string('courier_name')->nullable()->after('notes');
            $table->string('tracking_id')->nullable()->after('courier_name');
            $table->date('delivery_date')->nullable()->after('tracking_id');
            $table->timestamp('confirmed_at')->nullable()->after('delivery_date');
            $table->timestamp('shipped_at')->nullable()->after('confirmed_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->index(['status', 'payment_status']);
            $table->index('customer_phone');
            $table->index('created_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('sku', 128)->nullable()->after('variant_label');
            $table->decimal('weight_kg', 10, 3)->nullable()->after('sku');
            $table->string('product_image')->nullable()->after('weight_kg');
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['sku', 'weight_kg', 'product_image']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'payment_status']);
            $table->dropIndex(['customer_phone']);
            $table->dropIndex(['created_at']);
            $table->dropConstrainedForeignId('shipping_address_id');
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'customer_email',
                'tax_amount',
                'transaction_id',
                'razorpay_payment_id',
                'courier_name',
                'tracking_id',
                'delivery_date',
                'confirmed_at',
                'shipped_at',
                'delivered_at',
            ]);
        });

        Schema::dropIfExists('shipping_addresses');
    }
}
