<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEmailVerificationFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->after('email_verified_at');
            $table->timestamp('verification_expire_at')->nullable()->after('verification_token');
            $table->string('account_status', 20)->default('inactive')->after('verification_expire_at');
        });

        DB::table('users')->update(['account_status' => 'active']);

        DB::table('users')
            ->whereNull('email_verified_at')
            ->where('email', 'not like', '%@otp.local')
            ->update(['email_verified_at' => now()]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_token', 'verification_expire_at', 'account_status']);
        });
    }
}
