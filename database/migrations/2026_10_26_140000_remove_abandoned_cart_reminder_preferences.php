<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RemoveAbandonedCartReminderPreferences extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE users DROP COLUMN abandoned_cart_reminders_enabled');
        DB::statement('ALTER TABLE users DROP COLUMN abandoned_cart_reminders_consent_at');
    }

    public function down()
    {
        Schema::table('users', function ($table) {
            $table->boolean('abandoned_cart_reminders_enabled')->default(false);
            $table->timestamp('abandoned_cart_reminders_consent_at')->nullable();
        });
    }
}
