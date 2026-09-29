<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('status', 16)->default('active')->after('stock_qty');
            $table->string('image')->nullable()->after('status');
        });

        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['product_variant_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_images');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['status', 'image']);
        });
    }
};
