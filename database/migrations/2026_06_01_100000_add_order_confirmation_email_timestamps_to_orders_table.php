<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderConfirmationEmailTimestampsToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('customer_confirmation_sent_at')->nullable()->after('delivered_at');
            $table->timestamp('admin_notification_sent_at')->nullable()->after('customer_confirmation_sent_at');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['customer_confirmation_sent_at', 'admin_notification_sent_at']);
        });
    }
}
