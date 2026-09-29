<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('free_shipping_amount', 12, 2)->default(500);
            $table->decimal('shipping_charge', 12, 2)->default(50);
            $table->timestamps();
        });

        $legacyCharge = DB::table('settings')->where('key', 'shipping_flat')->value('value');

        DB::table('shipping_settings')->insert([
            'free_shipping_amount' => 500,
            'shipping_charge' => is_numeric($legacyCharge) ? (float) $legacyCharge : 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
