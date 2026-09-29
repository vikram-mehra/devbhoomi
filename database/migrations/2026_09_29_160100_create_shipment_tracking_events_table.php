<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentTrackingEventsTable extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_tracking_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('status', 40);
            $table->string('courier_status')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('event_time')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['shipment_id', 'event_time']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_tracking_events');
    }
}
