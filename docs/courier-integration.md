# Courier integration

Adapter-based shipping module. The admin Orders screen talks only to `CourierManager`. Courier-specific HTTP and payload mapping live in adapters and clients.

## Architecture

```
Admin Orders
    → CourierShipmentController
        → CourierManager
            → CourierResolver
                → DelhiveryAdapter → DelhiveryClient
                → BlueDartAdapter  → BlueDartClient
```

Adding a partner (Shiprocket, Ekart, Xpressbees, DTDC, Amazon Shipping, Shadowfax):

1. Create `app/Services/Courier/Adapters/{Name}Adapter.php` implementing `CourierAdapterInterface`.
2. Create a client under `app/Services/Courier/Clients/` if the partner needs HTTP transport.
3. Register the partner in `config/couriers.php` with `adapter`, `enabled`, and credentials from `.env`.
4. Do **not** change `OrderAdminController`, the orders Blade, or the `Order` model.

## Environment

```
COURIER_DELHIVERY_ENABLED=true
DELHIVERY_API_TOKEN=
DELHIVERY_API_URL=https://staging-express.delhivery.com

COURIER_BLUEDART_ENABLED=false
BLUEDART_API_URL=https://apigateway.bluedart.com
BLUEDART_CLIENT_ID=
BLUEDART_CLIENT_SECRET=
BLUEDART_LOGIN_ID=
BLUEDART_LICENCE_KEY=

DEFAULT_COURIER=delhivery
COURIER_QUEUE=true
```

Existing `DELHIVERY_TOKEN` / `DELHIVERY_BASE_URL` are reused if the new keys are empty.

```
DELHIVERY_WAREHOUSE=   # exact dashboard warehouse name (case-sensitive)
DELHIVERY_CLIENT=      # optional registered client name
```

If `DELHIVERY_WAREHOUSE` is blank, create uses the first warehouse already registered on the token, or tries to register one from company settings (GST, address, phone). A dummy return pin (`000000`) is never sent.

Delhivery `end_date` / `NoneType` errors mean the pickup name is not a registered warehouse or the client contract is missing. Set `DELHIVERY_WAREHOUSE` to the exact dashboard name.

Never put credentials in PHP source. Never log tokens or passwords. Channel: `storage/logs/courier.log`.

## Shipment creation

1. Admin selects orders and an enabled courier.
2. `POST /admin/orders/shipments` (`admin.orders.shipments.store`).
3. `CreateCourierShipmentRequest` validates CSRF, admin role, order IDs, and enabled courier.
4. `CourierManager` loads orders, checks eligibility, skips duplicate active shipments, calls the adapter, then writes `shipments` and mirrors `orders.courier_name` / `orders.tracking_id`.

When `COURIER_QUEUE=true` and `QUEUE_CONNECTION` is not `sync`, jobs (`CreateCourierShipmentJob`) are dispatched. Run a worker:

```
php artisan queue:work
```

Retries: 3 attempts, backoff 10/30/60 seconds. 401/400-style validation errors are not retried.

## Eligibility

- Order is not cancelled or returned.
- Order has line items.
- Shipping address exists (name, phone, line, city, pincode).
- Prepaid orders must be `paid`. COD is allowed unpaid.
- An active shipment (not `failed` / `cancelled`) is returned as-is (idempotent). Failed rows can be retried.
- Delhivery payloads include `order_date`, `seller_gst_tin` (from company GST), `hsn_code` (product HSN or `COURIER_DEFAULT_HSN`), and a real return address parsed from company settings.

## Statuses

Normalized in `App\Enums\ShipmentStatus`. Courier strings are mapped in `StatusMapper` (Delhivery `Dispatched` → `in_transit`, Blue Dart `SHIPMENT IN TRANSIT` → `in_transit`).

## Webhooks

Keep the existing Delhivery tracking webhook. New generic route:

```
POST /webhooks/couriers/{courier}
```

Optional `X-Webhook-Token` / `?token=` must match `*_WEBHOOK_TOKEN` when set.

## Admin

- Orders index: bulk courier dropdown (enabled partners only) + Create Shipment.
- `/admin/orders/{order}/shipment`: track / cancel / retry.
- `/admin/courier-partners`: read-only enablement from `.env`.

## Tests

```
php artisan test --filter=Courier
```

HTTP is faked. Real courier APIs are never called.
