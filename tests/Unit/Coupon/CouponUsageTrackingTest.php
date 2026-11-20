<?php

namespace Tests\Unit\Coupon;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Services\CouponService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CouponUsageTrackingTest extends TestCase
{
    protected CouponService $coupons;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.driver', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.prefix', '');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);

        DB::purge();
        DB::reconnect();

        $this->createSchema();
        $this->coupons = new CouponService();
    }

    public function test_resolve_does_not_create_usage_or_increment_count(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 5]);
        $user = $this->makeUser();

        $this->coupons->resolveForCart($coupon->code, 200, ['user' => $user]);

        $this->assertSame(0, CouponUsage::count());
        $this->assertSame(0, (int) $coupon->fresh()->used_count);
    }

    public function test_reserve_then_redeem_increments_used_count_once(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 5]);
        $user = $this->makeUser();
        $order = $this->makeOrder($user, $coupon, 'unpaid', 'pending');

        $this->coupons->reserveForOrder($coupon, $order, 20, [
            'user' => $user,
            'cart_subtotal' => 200,
            'items' => [],
        ]);

        $this->assertSame(0, (int) $coupon->fresh()->used_count);
        $this->assertSame(CouponUsage::STATUS_RESERVED, CouponUsage::first()->status);

        $order->update(['payment_status' => 'paid', 'status' => 'confirmed']);
        $this->coupons->redeemForOrder($order->fresh());
        $this->coupons->redeemForOrder($order->fresh());

        $this->assertSame(1, (int) $coupon->fresh()->used_count);
        $this->assertSame(1, CouponUsage::where('status', CouponUsage::STATUS_REDEEMED)->count());
    }

    public function test_global_limit_counts_reserved_slots(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);
        $userA = $this->makeUser('a@example.com');
        $userB = $this->makeUser('b@example.com');
        $orderA = $this->makeOrder($userA, $coupon, 'unpaid', 'pending');

        $this->coupons->reserveForOrder($coupon, $orderA, 10, [
            'user' => $userA,
            'cart_subtotal' => 200,
            'items' => [],
        ]);

        $this->expectException(ValidationException::class);
        $this->coupons->resolveForCart($coupon->code, 200, ['user' => $userB]);
    }

    public function test_per_user_limit_is_enforced(): void
    {
        $coupon = $this->makeCoupon(['per_user_limit' => 1, 'usage_limit' => 10]);
        $user = $this->makeUser();
        $order = $this->makeOrder($user, $coupon, 'paid', 'confirmed');

        $this->coupons->redeemForOrder($order);

        try {
            $this->coupons->resolveForCart($coupon->code, 200, ['user' => $user]);
            $this->fail('Expected per-user limit to fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('maximum number of times', strtolower(collect($e->errors())->flatten()->first()));
        }
    }

    public function test_failed_payment_releases_reservation(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);
        $user = $this->makeUser();
        $order = $this->makeOrder($user, $coupon, 'unpaid', 'pending');

        $this->coupons->reserveForOrder($coupon, $order, 15, [
            'user' => $user,
            'cart_subtotal' => 200,
            'items' => [],
        ]);

        $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
        $this->coupons->syncUsageWithOrder($order->fresh());

        $this->assertSame(CouponUsage::STATUS_RELEASED, CouponUsage::first()->status);
        $this->assertSame(0, (int) $coupon->fresh()->used_count);

        $other = $this->makeUser('other@example.com');
        $resolved = $this->coupons->resolveForCart($coupon->code, 200, ['user' => $other]);
        $this->assertSame($coupon->code, $resolved['coupon']->code);
    }

    public function test_cancellation_and_refund_release_redeemed_usage(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);
        $user = $this->makeUser();
        $order = $this->makeOrder($user, $coupon, 'paid', 'confirmed');

        $this->coupons->redeemForOrder($order);
        $this->assertSame(1, (int) $coupon->fresh()->used_count);

        $order->update(['status' => 'cancelled']);
        $this->coupons->syncUsageWithOrder($order->fresh());
        $this->assertSame(0, (int) $coupon->fresh()->used_count);
        $this->assertSame(CouponUsage::STATUS_RELEASED, CouponUsage::first()->status);

        $order2 = $this->makeOrder($this->makeUser('c@example.com'), $coupon, 'paid', 'confirmed', 'ORD-2');
        $this->coupons->redeemForOrder($order2);
        $this->assertSame(1, (int) $coupon->fresh()->used_count);

        $order2->update(['payment_status' => 'refunded']);
        $this->coupons->syncUsageWithOrder($order2->fresh());
        $this->assertSame(0, (int) $coupon->fresh()->used_count);
    }

    public function test_first_order_only_rejects_customers_with_prior_paid_order(): void
    {
        $coupon = $this->makeCoupon(['first_order_only' => true]);
        $user = $this->makeUser();
        $this->makeOrder($user, null, 'paid', 'confirmed', 'PRIOR-1');

        $this->expectException(ValidationException::class);
        $this->coupons->resolveForCart($coupon->code, 200, ['user' => $user]);
    }

    public function test_product_restriction_rejects_non_matching_cart(): void
    {
        $coupon = $this->makeCoupon();
        DB::table('products')->insert(['id' => 11, 'name' => 'Honey', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('products')->insert(['id' => 12, 'name' => 'Tea', 'created_at' => now(), 'updated_at' => now()]);
        $coupon->products()->attach(11);

        try {
            $this->coupons->resolveForCart($coupon->code, 200, [
                'items' => [[
                    'product_id' => 12,
                    'line_total' => 200,
                ]],
            ]);
            $this->fail('Expected product restriction to fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('not valid for the products', strtolower(collect($e->errors())->flatten()->first()));
        }
    }

    public function test_second_reservation_cannot_exceed_global_limit(): void
    {
        $coupon = $this->makeCoupon(['usage_limit' => 1]);
        $userA = $this->makeUser('one@example.com');
        $userB = $this->makeUser('two@example.com');
        $orderA = $this->makeOrder($userA, $coupon, 'unpaid', 'pending', 'ORD-A');
        $orderB = $this->makeOrder($userB, $coupon, 'unpaid', 'pending', 'ORD-B');

        $this->coupons->reserveForOrder($coupon, $orderA, 10, ['user' => $userA, 'cart_subtotal' => 200, 'items' => []]);

        $this->expectException(ValidationException::class);
        $this->coupons->reserveForOrder($coupon, $orderB, 10, ['user' => $userB, 'cart_subtotal' => 200, 'items' => []]);
    }

    protected function makeCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'SAVE'.uniqid(),
            'coupon_type' => 'public',
            'type' => 'fixed',
            'value' => 20,
            'min_cart' => 0,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'usage_limit' => null,
            'per_user_limit' => null,
            'used_count' => 0,
            'is_active' => true,
            'first_order_only' => false,
        ], $overrides));
    }

    protected function makeUser(string $email = 'user@example.com'): User
    {
        return User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => bcrypt('secret'),
        ]);
    }

    protected function makeOrder(User $user, ?Coupon $coupon, string $payment, string $status, string $number = 'ORD-1'): Order
    {
        return Order::create([
            'order_number' => $number,
            'user_id' => $user->id,
            'status' => $status,
            'payment_status' => $payment,
            'payment_method' => 'razorpay',
            'subtotal' => 180,
            'tax_amount' => 20,
            'discount' => $coupon ? 20 : 0,
            'total' => $coupon ? 180 : 200,
            'coupon_code' => $coupon?->code,
            'customer_email' => $user->email,
            'customer_name' => $user->name,
            'customer_phone' => '9999999999',
        ]);
    }

    protected function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('role')->nullable();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('coupon_type')->default('public');
            $table->string('type');
            $table->decimal('value', 12, 2);
            $table->decimal('min_cart', 12, 2)->nullable();
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('per_user_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('first_order_only')->default(false);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->string('guest_key')->nullable();
            $table->string('status')->default('reserved');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->unique(['coupon_id', 'order_id']);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('menu_item_id')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('coupon_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('product_id');
        });

        Schema::create('coupon_menu_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('menu_item_id');
        });
    }
}
