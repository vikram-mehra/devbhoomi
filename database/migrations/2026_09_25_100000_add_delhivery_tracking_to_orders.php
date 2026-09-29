<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'delhivery_status')) {
                $table->string('delhivery_status')->nullable()->after('tracking_id');
            }
            if (! Schema::hasColumn('orders', 'delhivery_status_type')) {
                $table->string('delhivery_status_type', 16)->nullable()->after('delhivery_status');
            }
            if (! Schema::hasColumn('orders', 'delhivery_location')) {
                $table->string('delhivery_location')->nullable()->after('delhivery_status_type');
            }
            if (! Schema::hasColumn('orders', 'delhivery_origin')) {
                $table->string('delhivery_origin')->nullable()->after('delhivery_location');
            }
            if (! Schema::hasColumn('orders', 'delhivery_destination')) {
                $table->string('delhivery_destination')->nullable()->after('delhivery_origin');
            }
            if (! Schema::hasColumn('orders', 'delhivery_expected_delivery')) {
                $table->timestamp('delhivery_expected_delivery')->nullable()->after('delhivery_destination');
            }
            if (! Schema::hasColumn('orders', 'delhivery_last_synced_at')) {
                $table->timestamp('delhivery_last_synced_at')->nullable()->after('delhivery_expected_delivery');
            }
            if (! Schema::hasColumn('orders', 'delhivery_last_attempt_at')) {
                $table->timestamp('delhivery_last_attempt_at')->nullable()->after('delhivery_last_synced_at');
            }
        });

        Schema::create('order_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('awb')->nullable();
            $table->string('status');
            $table->string('status_type', 16)->nullable();
            $table->string('location')->nullable();
            $table->text('instructions')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->string('source', 32)->default('poll');
            $table->string('fingerprint', 40);
            $table->timestamps();

            $table->unique(['order_id', 'fingerprint']);
            $table->index(['order_id', 'scanned_at']);
            $table->index('awb');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_tracking_events');

        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'delhivery_status',
                'delhivery_status_type',
                'delhivery_location',
                'delhivery_origin',
                'delhivery_destination',
                'delhivery_expected_delivery',
                'delhivery_last_synced_at',
                'delhivery_last_attempt_at',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('orders', $column)));
            if ($existing !== []) {
                $table->dropColumn($existing);
            }
        });
    }
};
