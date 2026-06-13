<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatMessagesTable extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sender_role', 16);
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'vendor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
}
