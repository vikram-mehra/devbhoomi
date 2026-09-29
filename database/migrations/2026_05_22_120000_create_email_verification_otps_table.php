<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailVerificationOtpsTable extends Migration
{
    public function up()
    {
        Schema::create('email_verification_otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('otp_hash', 64);
            $table->timestamp('expires_at');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'consumed_at', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_verification_otps');
    }
}
