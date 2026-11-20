<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MobileOtpLoginTest extends TestCase
{
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

        config()->set('services.msg91', [
            'auth_key' => 'test-auth-key',
            'template_id' => 'test-template',
            'otp_expiry' => 5,
            'otp_length' => 6,
            'enabled' => true,
            'dummy' => false,
            'dummy_code' => '123456',
            'country_code' => '91',
            'base_url' => 'https://control.msg91.com/api/v5',
            'timeout' => 5,
            'resend_cooldown' => 45,
            'send_hourly_limit' => 5,
            'verify_max_attempts' => 5,
        ]);

        Cache::flush();
    }

    public function test_existing_customer_is_logged_in_after_otp_verify(): void
    {
        $user = User::query()->create([
            'name' => 'Existing Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('secret-password'),
            'role' => User::ROLE_USER,
            'phone' => '9876543210',
            'account_status' => User::ACCOUNT_ACTIVE,
        ]);

        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'success', 'message' => 'OTP verified success'], 200),
        ]);

        $this->post(route('login.phone.send'), ['phone' => '9876543210'])->assertSessionHas('status');
        $this->post(route('login.phone.verify'), ['phone' => '9876543210', 'code' => '123456'])
            ->assertRedirect();

        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->phone_verified_at);
        $this->assertSame(User::ROLE_USER, $user->fresh()->role);
    }

    public function test_new_otp_user_is_created_as_customer_only(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'success', 'message' => 'OTP verified success'], 200),
        ]);

        $this->post(route('login.phone.send'), ['phone' => '9123456789']);
        $this->post(route('login.phone.verify'), ['phone' => '9123456789', 'code' => '123456'])
            ->assertRedirect();

        $user = User::query()->where('phone', '9123456789')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::ROLE_USER, $user->role);
        $this->assertFalse($user->isAdmin());
        $this->assertNotNull($user->phone_verified_at);
        $this->assertNull($user->email_verified_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_json_send_returns_verify_payload(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
        ]);

        $this->postJson(route('login.phone.send'), ['phone' => '9876543210'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('phone', '9876543210')
            ->assertJsonStructure(['message', 'cooldown', 'display_phone']);
    }

    public function test_json_verify_returns_redirect_and_signs_in(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'success', 'message' => 'OTP verified success'], 200),
        ]);

        $this->postJson(route('login.phone.send'), ['phone' => '9876543210'])->assertOk();
        $this->postJson(route('login.phone.verify'), ['phone' => '9876543210', 'code' => '123456'])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['message', 'redirect']);

        $this->assertAuthenticated();
    }

    public function test_json_invalid_otp_stays_on_verify_window(): void
    {
        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'error', 'message' => 'OTP not match'], 200),
        ]);

        $this->postJson(route('login.phone.send'), ['phone' => '9876543210'])->assertOk();
        $this->postJson(route('login.phone.verify'), ['phone' => '9876543210', 'code' => '000000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');

        $this->assertGuest();
    }

    public function test_admin_accounts_cannot_use_customer_otp_login(): void
    {
        User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret'),
            'role' => User::ROLE_ADMIN,
            'phone' => '9000000001',
            'account_status' => User::ACCOUNT_ACTIVE,
        ]);

        Http::fake([
            'https://control.msg91.com/api/v5/otp' => Http::response(['type' => 'success', 'message' => 'OTP sent successfully'], 200),
            'https://control.msg91.com/api/v5/otp/verify*' => Http::response(['type' => 'success', 'message' => 'OTP verified success'], 200),
        ]);

        $this->post(route('login.phone.send'), ['phone' => '9000000001']);
        $this->from(route('login'))
            ->post(route('login.phone.verify'), ['phone' => '9000000001', 'code' => '123456'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    protected function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('role', 20)->default('user');
            $table->string('phone', 32)->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('account_status')->nullable();
            $table->string('google_id')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamp('verification_expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('product_variant_id')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });
    }
}
