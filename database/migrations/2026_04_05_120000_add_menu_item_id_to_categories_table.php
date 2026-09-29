<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMenuItemIdToCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('menu_item_id')->nullable()->after('parent_id')->constrained('menu_items')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['menu_item_id']);
        });
    }
}
