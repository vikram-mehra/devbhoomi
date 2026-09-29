<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;

class GoogleAnalyticsService
{
    public static function flashEvent(string $name, array $data): void
    {
        $events = session()->get('ga_events', []);
        $events[] = ['name' => $name, 'data' => $data];
        session()->flash('ga_events', $events);
    }

    public static function flashAddToCart(ProductVariant $variant, int $qty, bool $isBuyNow = false): void
    {
        $eventData = [
            'currency' => 'INR',
            'value' => (float) ($variant->effectivePrice() * $qty),
            'items' => [
                [
                    'item_id' => (string) ($variant->sku ?: $variant->id),
                    'item_name' => (string) $variant->product->name,
                    'price' => (float) $variant->effectivePrice(),
                    'quantity' => (int) $qty,
                    'item_variant' => (string) $variant->label(),
                ]
            ]
        ];

        static::flashEvent('add_to_cart', $eventData);

        if ($isBuyNow) {
            static::flashEvent('buy_now', [
                'currency' => 'INR',
                'value' => $eventData['value'],
                'items' => $eventData['items'],
            ]);
        }
    }

    public static function flashPlaceOrder(Order $order): void
    {
        $order->loadMissing('items.variant.product');
        $eventData = [
            'currency' => 'INR',
            'value' => (float) $order->total,
            'transaction_id' => (string) $order->order_number,
            'items' => $order->items->map(fn($item) => [
                'item_id' => (string) ($item->sku ?: $item->product_variant_id),
                'item_name' => (string) $item->product_name,
                'price' => (float) $item->unit_price,
                'quantity' => (int) $item->qty,
                'item_variant' => (string) $item->variant_label,
            ])->toArray()
        ];

        static::flashEvent('place_order', $eventData);
    }

    public static function flashPurchase(Order $order): void
    {
        $order->loadMissing('items.variant.product');
        $eventData = [
            'transaction_id' => (string) $order->order_number,
            'value' => (float) $order->total,
            'currency' => 'INR',
            'tax' => (float) $order->tax_amount,
            'shipping' => (float) $order->shipping,
            'items' => $order->items->map(fn($item) => [
                'item_id' => (string) ($item->sku ?: $item->product_variant_id),
                'item_name' => (string) $item->product_name,
                'price' => (float) $item->unit_price,
                'quantity' => (int) $item->qty,
                'item_variant' => (string) $item->variant_label,
            ])->toArray()
        ];

        static::flashEvent('purchase', $eventData);
    }
}
