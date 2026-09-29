<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCouponTypeToCouponsTable extends Migration
{
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('coupon_type', ['public', 'internal'])->default('public')->after('code');
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('coupon_type');
        });
    }
}
