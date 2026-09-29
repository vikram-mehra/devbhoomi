<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 20)->default('user');
            $table->string('phone', 32)->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->decimal('wallet_balance', 12, 2)->default(0);
            $table->boolean('dark_mode')->default(false);
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
