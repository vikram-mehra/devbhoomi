<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureProductsCategoryIdNullable extends Migration
{
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        $nullable = $this->productsCategoryIdIsNullable();
        if ($nullable === true) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        DB::statement('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });

        DB::statement('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NOT NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });
    }

    /**
     * @return bool|null true = already nullable, false = not nullable, null = unknown
     */
    protected function productsCategoryIdIsNullable(): ?bool
    {
        try {
            $row = DB::selectOne('SHOW COLUMNS FROM products WHERE Field = ?', ['category_id']);
            if (! $row) {
                return null;
            }
            $arr = (array) $row;
            $null = $arr['Null'] ?? $arr['null'] ?? null;

            return strtoupper((string) $null) === 'YES';
        } catch (\Throwable $e) {
            return null;
        }
    }
}
