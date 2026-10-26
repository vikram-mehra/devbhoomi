<?php

namespace Tests\Feature;

use App\Console\Kernel;
use App\Jobs\SendAbandonedCartReminderJob;
use App\Mail\AbandonedCartReminderMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Services\AbandonedCartReminderService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AbandonedCartReminderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.driver', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.prefix', '');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);
        config()->set('app.url', 'http://localhost');
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        config()->set('abandoned_cart.enabled', true);
        config()->set('abandoned_cart.delay_minutes', 60);
        config()->set('abandoned_cart.claim_timeout_minutes', 120);

        DB::purge();
        DB::reconnect();
        $this->createSchema();
        Mail::fake();
    }

    public function test_sends_one_reminder_with_current_cart_and_cart_link(): void
    {
        $user = $this->makeUser();
        $item = $this->makeCartItem($user);
        $claim = app(AbandonedCartReminderService::class)->claim(
            $user->id,
            now()->subHour(),
            now()->subHours(2)
        );

        $this->assertNotNull($claim);
        (new SendAbandonedCartReminderJob($user->id, $claim))->handle(app(AbandonedCartReminderService::class));
        (new SendAbandonedCartReminderJob($user->id, $claim))->handle(app(AbandonedCartReminderService::class));

        Mail::assertSent(AbandonedCartReminderMail::class);
        $mail = Mail::sent(AbandonedCartReminderMail::class)->first();
        $html = view('emails.customer.abandoned-cart-reminder', get_object_vars($mail))->render();
        $this->assertSame('customer@example.com', $mail->to[0]['address']);
        $this->assertNotFalse(strpos($html, 'Organic Tea'));
        $this->assertNotFalse(strpos($html, route('cart.index')));
        $this->assertNotFalse(strpos($html, '200.00'));
        $this->assertFalse(strpos($html, 'Unsubscribe from cart reminders') !== false);
        $this->assertNotNull($item->fresh()->abandoned_cart_reminder_sent_at);
        Mail::assertSent(AbandonedCartReminderMail::class, 1);
    }

    public function test_all_eligible_customers_receive_reminders_without_a_preference(): void
    {
        $user = $this->makeUser();
        $this->makeCartItem($user);
        $claim = app(AbandonedCartReminderService::class)->claim(
            $user->id,
            now()->subHour(),
            now()->subHours(2)
        );
        $this->assertNotNull($claim);
        (new SendAbandonedCartReminderJob($user->id, $claim))
            ->handle(app(AbandonedCartReminderService::class));

        Mail::assertSent(AbandonedCartReminderMail::class);
    }

    public function test_skips_a_cart_when_a_paid_order_was_completed_after_cart_activity(): void
    {
        $user = $this->makeUser();
        $item = $this->makeCartItem($user);
        $claim = app(AbandonedCartReminderService::class)->claim(
            $user->id,
            now()->subHour(),
            now()->subHours(2)
        );
        $this->assertNotNull($claim);

        DB::table('orders')->insert([
            'user_id' => $user->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        (new SendAbandonedCartReminderJob($user->id, $claim))->handle(app(AbandonedCartReminderService::class));

        Mail::assertNothingSent();
        $this->assertNull($item->fresh()->abandoned_cart_reminder_claim);
    }

    public function test_sends_a_reminder_for_an_unpaid_checkout_using_its_payment_link(): void
    {
        $user = $this->makeUser();
        $orderId = $this->makeUnpaidOrder($user);
        $claim = app(AbandonedCartReminderService::class)->claimOrder(
            $orderId,
            now()->subHour(),
            now()->subHours(2)
        );

        $this->assertNotNull($claim);
        (new SendAbandonedCartReminderJob($user->id, $claim, $orderId))
            ->handle(app(AbandonedCartReminderService::class));

        Mail::assertSent(AbandonedCartReminderMail::class);
        $mail = Mail::sent(AbandonedCartReminderMail::class)->first();
        $html = view('emails.customer.abandoned-cart-reminder', get_object_vars($mail))->render();
        $this->assertNotFalse(strpos($html, route('pay.razorpay', $orderId)));
        $this->assertNotNull(Order::query()->find($orderId)->abandoned_cart_reminder_sent_at);
    }

    public function test_skips_an_unpaid_order_if_payment_completes_before_the_job_runs(): void
    {
        $user = $this->makeUser();
        $orderId = $this->makeUnpaidOrder($user);
        $claim = app(AbandonedCartReminderService::class)->claimOrder(
            $orderId,
            now()->subHour(),
            now()->subHours(2)
        );
        $this->assertNotNull($claim);

        DB::table('orders')->where('id', $orderId)->update([
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'updated_at' => now(),
        ]);

        (new SendAbandonedCartReminderJob($user->id, $claim, $orderId))
            ->handle(app(AbandonedCartReminderService::class));

        Mail::assertNothingSent();
    }

    public function test_skips_an_empty_cart(): void
    {
        $user = $this->makeUser();

        (new SendAbandonedCartReminderJob($user->id, 'empty-cart-claim'))
            ->handle(app(AbandonedCartReminderService::class));

        Mail::assertNothingSent();
    }

    public function test_recent_cart_activity_invalidates_an_already_queued_reminder(): void
    {
        $user = $this->makeUser();
        $item = $this->makeCartItem($user);
        $claim = app(AbandonedCartReminderService::class)->claim(
            $user->id,
            now()->subHour(),
            now()->subHours(2)
        );
        $this->assertNotNull($claim);

        app(\App\Services\CartService::class)->recordUserActivity($user->id);
        (new SendAbandonedCartReminderJob($user->id, $claim))->handle(app(AbandonedCartReminderService::class));

        Mail::assertNothingSent();
        $this->assertNull($item->fresh()->abandoned_cart_reminder_claim);
    }

    public function test_scheduler_claim_prevents_duplicate_dispatches(): void
    {
        $user = $this->makeUser();
        $this->makeCartItem($user);
        Bus::fake();

        Artisan::call('app:send-abandoned-cart-reminders');
        Artisan::call('app:send-abandoned-cart-reminders');

        Bus::assertDispatchedTimes(SendAbandonedCartReminderJob::class, 1);
    }

    public function test_command_skips_invalid_customer_email_addresses(): void
    {
        $user = $this->makeUser();
        $this->makeCartItem($user);
        $user->update(['email' => 'not-an-email']);
        Bus::fake();

        Artisan::call('app:send-abandoned-cart-reminders');

        Bus::assertNotDispatched(SendAbandonedCartReminderJob::class);
    }

    public function test_queued_reminder_is_skipped_after_checkout_removes_the_cart_and_payment_completes(): void
    {
        $user = $this->makeUser();
        $item = $this->makeCartItem($user);
        $claim = app(AbandonedCartReminderService::class)->claim(
            $user->id,
            now()->subHour(),
            now()->subHours(2)
        );
        $this->assertNotNull($claim);

        DB::table('orders')->insert([
            'user_id' => $user->id,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $item->delete();

        (new SendAbandonedCartReminderJob($user->id, $claim))->handle(app(AbandonedCartReminderService::class));

        Mail::assertNothingSent();
    }

    public function test_reminder_command_is_scheduled_every_five_minutes(): void
    {
        $schedule = app(Schedule::class);
        $method = new \ReflectionMethod(Kernel::class, 'schedule');
        $method->setAccessible(true);
        $method->invoke(app(Kernel::class), $schedule);

        $this->assertTrue(collect($schedule->events())->contains(function ($event) {
            return strpos($event->command, 'app:send-abandoned-cart-reminders') !== false
                && $event->expression === '*/5 * * * *';
        }));
    }

    public function test_migrations_keep_tracking_columns_and_remove_customer_preferences(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
        });

        require_once base_path('database/migrations/2026_10_26_130000_add_abandoned_cart_reminder_tracking.php');
        (new \AddAbandonedCartReminderTracking())->up();
        require_once base_path('database/migrations/2026_10_26_140000_remove_abandoned_cart_reminder_preferences.php');
        (new \RemoveAbandonedCartReminderPreferences())->up();

        $this->assertTrue(Schema::hasColumns('cart_items', [
            'abandoned_cart_reminder_claim',
            'abandoned_cart_reminder_claimed_at',
            'abandoned_cart_reminder_sent_at',
        ]));
        $this->assertFalse(Schema::hasColumn('users', 'abandoned_cart_reminders_enabled'));
        $this->assertFalse(Schema::hasColumn('users', 'abandoned_cart_reminders_consent_at'));
        $this->assertTrue(Schema::hasColumns('orders', [
            'abandoned_cart_reminder_claim',
            'abandoned_cart_reminder_claimed_at',
            'abandoned_cart_reminder_sent_at',
        ]));
    }

    protected function makeUser(): User
    {
        return User::query()->create([
            'name' => 'Taylor Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    protected function makeCartItem(User $user): CartItem
    {
        $now = now();
        $productId = DB::table('products')->insertGetId([
            'name' => 'Organic Tea',
            'base_price' => 100,
            'gst' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $variantId = DB::table('product_variants')->insertGetId([
            'product_id' => $productId,
            'sku' => 'TEA-001',
            'price' => 100,
            'stock_qty' => 5,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $item = CartItem::query()->create([
            'user_id' => $user->id,
            'product_variant_id' => $variantId,
            'qty' => 2,
        ]);
        DB::table('cart_items')->where('id', $item->id)->update([
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        return $item->fresh();
    }

    protected function makeUnpaidOrder(User $user): int
    {
        $now = now();
        $orderId = DB::table('orders')->insertGetId([
            'user_id' => $user->id,
            'payment_method' => 'razorpay',
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 200,
            'wallet_used' => 0,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'product_name' => 'Organic Tea',
            'variant_label' => 'Default',
            'product_image' => null,
            'unit_price' => 100,
            'qty' => 2,
            'line_total' => 200,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $orderId;
    }

    protected function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role')->default('user');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->decimal('base_price', 12, 2);
            $table->decimal('gst', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock_qty')->default(0);
            $table->string('status')->default('active');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('flash_sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('sale_price', 12, 2);
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('path')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedInteger('qty');
            $table->string('abandoned_cart_reminder_claim', 36)->nullable();
            $table->timestamp('abandoned_cart_reminder_claimed_at')->nullable();
            $table->timestamp('abandoned_cart_reminder_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('payment_method')->nullable();
            $table->string('payment_status');
            $table->string('status');
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('wallet_used', 12, 2)->default(0);
            $table->string('abandoned_cart_reminder_claim', 36)->nullable();
            $table->timestamp('abandoned_cart_reminder_claimed_at')->nullable();
            $table->timestamp('abandoned_cart_reminder_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('product_name');
            $table->string('variant_label')->nullable();
            $table->string('product_image')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->unsignedInteger('qty');
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });
    }
}
