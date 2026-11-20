<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\Courier\CourierManager;
use App\Services\Courier\DTOs\BulkShipmentResult;
use App\Services\Courier\DTOs\ShipmentResult;
use Tests\TestCase;

class CourierShipmentTest extends TestCase
{
    public function test_guest_cannot_create_shipments(): void
    {
        $this->post(route('admin.orders.shipments.store'), [
            'order_ids' => [1],
            'courier' => 'delhivery',
        ])->assertRedirect();
    }

    public function test_non_admin_cannot_create_shipments(): void
    {
        $user = new User(['role' => User::ROLE_USER, 'name' => 'Buyer', 'email' => 'buyer@example.test']);
        $user->id = 9;

        $this->actingAs($user)->post(route('admin.orders.shipments.store'), [
            'order_ids' => [1],
            'courier' => 'delhivery',
        ])->assertForbidden();
    }

    public function test_disabled_courier_is_rejected_for_admin(): void
    {
        config([
            'couriers.partners.delhivery.enabled' => true,
            'couriers.partners.bluedart.enabled' => false,
        ]);

        $admin = new User(['role' => User::ROLE_ADMIN, 'name' => 'Admin', 'email' => 'admin@example.test']);
        $admin->id = 1;

        $this->actingAs($admin)
            ->from('/admin/orders')
            ->post(route('admin.orders.shipments.store'), [
                'order_ids' => [1],
                'courier' => 'bluedart',
            ])
            ->assertSessionHasErrors('courier');
    }

    public function test_admin_can_submit_enabled_courier_and_receives_summary(): void
    {
        config(['couriers.partners.delhivery.enabled' => true]);

        $manager = \Mockery::mock(CourierManager::class);
        $manager->shouldReceive('availableDrivers')->andReturn(['delhivery' => 'Delhivery']);
        $manager->shouldReceive('createShipments')->once()->andReturn(new BulkShipmentResult('delhivery', [
            ShipmentResult::ok(1, '100001', 'AWB1'),
        ]));
        $this->app->instance(CourierManager::class, $manager);

        $admin = new User(['role' => User::ROLE_ADMIN, 'name' => 'Admin', 'email' => 'admin@example.test']);
        $admin->id = 1;

        $this->actingAs($admin)
            ->from('/admin/orders')
            ->post(route('admin.orders.shipments.store'), [
                'order_ids' => [1],
                'courier' => 'delhivery',
            ])
            ->assertRedirect('/admin/orders')
            ->assertSessionHas('courier_shipment_summary');
    }
}
