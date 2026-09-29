<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('slug');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->string('meta_keywords', 500)->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_image', 2048)->nullable()->after('canonical_url');
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('slug');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->string('meta_keywords', 500)->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_image', 2048)->nullable()->after('canonical_url');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('slug');
            $table->string('meta_description', 500)->nullable()->after('meta_title');
            $table->string('meta_keywords', 500)->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_image', 2048)->nullable()->after('canonical_url');
        });

        Schema::table('about_pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('hero_title');
            $table->string('meta_keywords', 500)->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_image', 2048)->nullable()->after('canonical_url');
        });

        Schema::table('contact_pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('hero_title');
            $table->string('meta_keywords', 500)->nullable()->after('meta_description');
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_image', 2048)->nullable()->after('canonical_url');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('canonical_url', 2048)->nullable()->after('meta_keywords');
            $table->string('og_image', 2048)->nullable()->after('canonical_url');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image']);
        });

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image']);
        });

        Schema::table('about_pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_keywords', 'canonical_url', 'og_image']);
        });

        Schema::table('contact_pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_keywords', 'canonical_url', 'og_image']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['canonical_url', 'og_image']);
        });
    }
};
