<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('courier_partner', 40);
            $table->string('courier_service', 80)->nullable();
            $table->string('awb', 64)->nullable();
            $table->string('tracking_number', 64)->nullable();
            $table->string('shipment_id', 80)->nullable();
            $table->string('status', 40)->default('created');
            $table->string('label_url')->nullable();
            $table->string('manifest_url')->nullable();
            $table->timestamp('pickup_scheduled_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('shipping_cost', 12, 2)->nullable();
            $table->decimal('cod_amount', 12, 2)->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->string('request_reference', 80);
            $table->string('api_request_reference', 80)->nullable();
            $table->string('api_response_reference', 80)->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('awb');
            $table->index('courier_partner');
            $table->index('status');
            $table->unique('request_reference');
            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
}
