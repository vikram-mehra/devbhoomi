<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartItemsTable extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id', 120)->nullable()->index();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
            $table->index(['user_id', 'product_variant_id']);
            $table->index(['session_id', 'product_variant_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
