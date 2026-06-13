<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeGstToPercentOnProductsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('products', 'gst')) {
            return;
        }

        DB::statement('ALTER TABLE `products` MODIFY `gst` DECIMAL(5, 2) NULL');
    }

    public function down()
    {
        if (! Schema::hasColumn('products', 'gst')) {
            return;
        }

        DB::statement('ALTER TABLE `products` MODIFY `gst` VARCHAR(32) NULL');
    }
}
