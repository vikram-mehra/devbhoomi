<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->string('eyebrow')->nullable()->after('title');
            $table->text('subtitle')->nullable()->after('eyebrow');
            $table->string('button_label')->nullable()->after('link');
            $table->string('secondary_button_label')->nullable()->after('button_label');
            $table->string('secondary_link')->nullable()->after('secondary_button_label');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn([
                'eyebrow',
                'subtitle',
                'button_label',
                'secondary_button_label',
                'secondary_link',
            ]);
        });
    }
};
