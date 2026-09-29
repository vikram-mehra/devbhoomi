<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\ShippingAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderManagementSeeder extends Seeder
{
    public function run()
    {
        $customer = User::where('role', User::ROLE_USER)->first();
        $variant = ProductVariant::with('product.vendor')->first();
        if (! $customer || ! $variant || ! $variant->product || ! $variant->product->vendor) {
            return;
        }

        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
        foreach ($statuses as $status) {
            $order = Order::create([
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'user_id' => $customer->id,
                'status' => $status,
                'payment_method' => 'razorpay',
                'payment_status' => in_array($status, ['cancelled', 'returned']) ? 'refunded' : 'paid',
                'subtotal' => 899,
                'shipping' => 49,
                'discount' => 0,
                'tax_amount' => 45,
                'total' => 993,
                'customer_name' => $customer->name,
                'customer_phone' => $customer->phone ?: '9999999999',
                'customer_email' => $customer->email,
                'transaction_id' => 'TXN'.strtoupper(Str::random(10)),
                'razorpay_payment_id' => 'pay_'.Str::lower(Str::random(12)),
                'courier_name' => 'BlueDart',
                'tracking_id' => 'TRK'.strtoupper(Str::random(10)),
                'delivery_date' => now()->addDays(3),
                'notes' => 'Sample seeded order note',
            ]);

            ShippingAddress::create([
                'order_id' => $order->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?: '9999999999',
                'line1' => 'Sample Street 123',
                'line2' => 'Near Market',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pincode' => '110001',
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'vendor_id' => $variant->product->vendor->id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_label' => $variant->label(),
                'sku' => $variant->sku,
                'weight_kg' => $variant->product->weight_kg,
                'product_image' => $variant->variantImageUrl(),
                'unit_price' => 899,
                'qty' => 1,
                'line_total' => 899,
                'commission_amount' => 40,
            ]);
        }
    }
}
