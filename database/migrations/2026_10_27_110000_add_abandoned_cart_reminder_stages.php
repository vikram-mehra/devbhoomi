<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbandonedCartReminderStages extends Migration
{
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->timestamp('abandoned_cart_reminder_2_sent_at')->nullable();
            $table->timestamp('abandoned_cart_reminder_3_sent_at')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('abandoned_cart_reminder_2_sent_at')->nullable();
            $table->timestamp('abandoned_cart_reminder_3_sent_at')->nullable();
        });

    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn([
                'abandoned_cart_reminder_2_sent_at',
                'abandoned_cart_reminder_3_sent_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'abandoned_cart_reminder_2_sent_at',
                'abandoned_cart_reminder_3_sent_at',
            ]);
        });

    }
}
