<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RenameVerificationColumnsOnUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'verification_code')) {
                $table->string('verification_code', 64)->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('users', 'verification_expires_at')) {
                $table->timestamp('verification_expires_at')->nullable()->after('verification_code');
            }
        });

        if (Schema::hasColumn('users', 'verification_token')) {
            DB::statement(
                'UPDATE users SET verification_code = verification_token, verification_expires_at = verification_expire_at WHERE verification_token IS NOT NULL'
            );
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'verification_token')) {
                $table->dropColumn('verification_token');
            }
            if (Schema::hasColumn('users', 'verification_expire_at')) {
                $table->dropColumn('verification_expire_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->after('email_verified_at');
            $table->timestamp('verification_expire_at')->nullable()->after('verification_token');
        });

        DB::table('users')
            ->whereNotNull('verification_code')
            ->update([
                'verification_token' => DB::raw('verification_code'),
                'verification_expire_at' => DB::raw('verification_expires_at'),
            ]);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verification_code', 'verification_expires_at']);
        });
    }
}
