<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variant_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('variant_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variant_label_id')->constrained('variant_labels')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();
            $table->unique(['variant_label_id', 'value']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('variant_label')->nullable()->after('compare_price');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('variant_label');
        });

        Schema::dropIfExists('variant_options');
        Schema::dropIfExists('variant_labels');
    }
};
