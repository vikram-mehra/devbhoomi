<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Abandoned-cart email reminders

Reminders use the existing `cart_items`, `orders`, and `order_items` tables; no replacement cart or customer-data table is introduced. This also covers an unpaid Razorpay checkout, whose order-item snapshot survives the existing checkout behavior that clears cart rows. Reminders are automatic for customers with eligible abandoned carts. The feature is disabled globally unless explicitly enabled.

Set these environment variables after deploying:

```dotenv
ABANDONED_CART_REMINDERS_ENABLED=true
ABANDONED_CART_REMINDER_CLAIM_TIMEOUT_MINUTES=120
MAIL_SUPPORT_ADDRESS=support@example.com
```

The three reminder stages are fixed at 1 hour, 24 hours, and 3 days from the cart's latest activity (or unpaid checkout order creation). Their sent markers are stored per cart row/order. Configure `APP_URL`, `MAIL_MAILER`, and the appropriate mail transport credentials using the application's existing mail configuration. Do not commit real credentials.

Apply the migration:

```powershell
php artisan migrate
```

Laravel 8 uses the console kernel scheduler. Add this cron entry on the server (replace the application path and PHP executable as appropriate):

```cron
* * * * * cd /path/to/application && php artisan schedule:run >> /dev/null 2>&1
```

For local development on Windows, run the scheduler in a terminal:

```powershell
php artisan schedule:work
```

You can also run the command once to check it manually:

```powershell
php artisan app:send-abandoned-cart-reminders
```

The project currently defaults to `QUEUE_CONNECTION=sync`, which sends each queued job inline and does not require a worker. For asynchronous queued delivery, set `QUEUE_CONNECTION=database` and generate/apply Laravel's queue-table migration if the project does not already have one. The current repository has no `jobs` table migration:

```powershell
php artisan queue:table
php artisan migrate
php artisan queue:work --tries=3
```

Or use an already configured Redis/SQS connection, then keep a worker running:

```powershell
php artisan queue:work --tries=3
```

On production, use a supervised worker for a persistent queue. The scheduler/worker records a stage as sent only after the configured mail transport accepts the message; transport acceptance does not guarantee inbox delivery. Verify mail delivery and scheduler/worker health before enabling reminders.

Run the feature tests with:

```powershell
vendor\bin\phpunit tests\Feature\AbandonedCartReminderTest.php
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
