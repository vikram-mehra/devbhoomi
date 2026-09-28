<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pincode_serviceabilities', function (Blueprint $table) {
            $table->id();
            $table->string('pincode', 6)->unique();
            $table->string('city', 120);
            $table->string('state', 120);
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('day_offset')->default(3);
            $table->string('courier_name', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pincode_serviceabilities');
    }
};
