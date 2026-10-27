<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbandonedCartReminderTracking extends Migration
{
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('abandoned_cart_reminder_claim', 36)->nullable()->index();
            $table->timestamp('abandoned_cart_reminder_claimed_at')->nullable();
            $table->timestamp('abandoned_cart_reminder_sent_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('abandoned_cart_reminders_enabled')->default(false);
            $table->timestamp('abandoned_cart_reminders_consent_at')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('abandoned_cart_reminder_claim', 36)->nullable()->index();
            $table->timestamp('abandoned_cart_reminder_claimed_at')->nullable();
            $table->timestamp('abandoned_cart_reminder_sent_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['abandoned_cart_reminder_claim']);
            $table->dropColumn([
                'abandoned_cart_reminder_claim',
                'abandoned_cart_reminder_claimed_at',
                'abandoned_cart_reminder_sent_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'abandoned_cart_reminders_enabled',
                'abandoned_cart_reminders_consent_at',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['abandoned_cart_reminder_claim']);
            $table->dropColumn([
                'abandoned_cart_reminder_claim',
                'abandoned_cart_reminder_claimed_at',
                'abandoned_cart_reminder_sent_at',
            ]);
        });
    }
}
