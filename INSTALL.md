# Alluringstyle — Installation

## Requirements

- PHP 8.0+ with extensions: `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`
- Composer
- MySQL 5.7+ / MariaDB
- XAMPP (Windows): use `C:\xampp\php\php.exe` if `php` is not on PATH

## Steps

1. **Clone / copy** the project into your web root, e.g. `C:\xampp\htdocs\zionshoping`.

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
   Edit `.env`: set `APP_NAME`, `APP_URL` (e.g. `http://localhost/alluringstyle/public`), and `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

4. **Database**
   ```sql
   CREATE DATABASE zionshop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   ```bash
   php artisan migrate:fresh --seed
   ```

5. **Run**

   **If you see “Index of /zionshoping”** in the browser, Apache is serving the project folder instead of `public/`. Fix it one of these ways:

   - **Easiest:** Open **`http://localhost/alluringstyle/public/`** (note the `/public` segment).
   - **Recommended (this repo):** A root **`.htaccess`** is included so **`http://localhost/alluringstyle/`** should rewrite into `public/`. You need **`mod_rewrite` enabled** in XAMPP and **`AllowOverride All`** for `htdocs` in `httpd.conf` (then restart Apache).
   - **Best for production:** Set the virtual host **DocumentRoot** to `.../zionshoping/public` (not the parent folder).

   Alternatively: `php artisan serve` and visit `http://127.0.0.1:8000`.

6. **“Index of /zionshoping/public” (directory listing)**  
   Apache is not using **`index.php`** as the folder index. This project sets **`DirectoryIndex index.php`** in **`public/.htaccess`** and ships **`public/index.html`** as a fallback redirect. You still need **`AllowOverride All`** (includes **Indexes**) for `htdocs` or your vhost so `.htaccess` is read. In **`httpd.conf`**, you can also set globally: **`DirectoryIndex index.php index.html`**.

7. **403 Forbidden**
   - Often caused by **`Options` inside `.htaccess`** when the server does not allow `AllowOverride Options`. This project avoids `Options` in `.htaccess` for that reason.
   - In **`httpd.conf`**, ensure for `htdocs` (or your vhost) you have **`AllowOverride All`** (not `None`) so rewrite rules run.
   - Ensure **`LoadModule rewrite_module`** is uncommented, then restart Apache.

8. **404 / Not Found**
   - Set `.env` → `APP_URL` to the **exact** URL you open in the browser (e.g. `http://localhost/alluringstyle/public` — no trailing slash). The path segment must match (folder name spelling counts). Then run `php artisan config:clear`.
   - **`public/index.php`** reads **`APP_URL`** from `.env` (via Dotenv before bootstrap) and strips that path from `REQUEST_URI` so routes like `/` match.
   - **Best fix:** use **`apache-vhost.example.conf`**: set Apache **DocumentRoot** to `.../zionshoping/public` and open `http://zionshoping.test/` (see comments in that file).

## Demo accounts (after seed)

| Role   | Email                 | Password  |
|--------|----------------------|-----------|
| Admin  | admin@alluringstyle.test | password |
| Vendor | info@devbhoominaturals.com | password |
| Customer | customer@alluringstyle.test | password |

## Payments

- **Razorpay**: set `RAZORPAY_KEY` and `RAZORPAY_SECRET` in `.env`. Without keys, checkout still works; Razorpay screen is skipped in demo flow where applicable.
- **Stripe**: set `STRIPE_KEY`, `STRIPE_SECRET`, and `STRIPE_CURRENCY` (e.g. `inr` or `usd`). Without keys, paid status is simulated for development.

## API (Sanctum)

- Public JSON: `GET /api/v1/categories`, `GET /api/v1/products`, `GET /api/v1/products/{slug}`
- Authenticated: `GET /api/user` with `Authorization: Bearer {token}` (create tokens via `php artisan tinker` or a small token route you add).

## Notes

- Mobile OTP codes are stored in cache for 10 minutes; with `APP_DEBUG=true`, the dev OTP is also shown after “Send OTP”.
- Clear config/cache after changing `.env`: `php artisan config:clear && php artisan cache:clear`.
