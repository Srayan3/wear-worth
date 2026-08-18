# Atelier — Women's Fashion E-Commerce Platform

A complete, production-ready e-commerce store built for a Bangladeshi women's
fashion brand: PHP 8 + MySQL storefront and admin panel, no frameworks,
designed to run on ordinary Apache/PHP shared hosting.

> **About the branding.** The brand's Facebook page could not be inspected
> automatically (Facebook blocks automated access), so this build ships with
> a sophisticated, minimalist black/off-white fashion aesthetic instead of
> invented brand details — per the "don't invent brand details" instruction.
> **Every piece of copy, the logo, hero image, colors used for badges, about
> text, contact info, and social links are editable from Admin → Settings**,
> so the real business owner can drop in the actual brand in minutes. Sample
> products use elegant placeholder illustrations clearly marked "replace in
> admin" — real photography should replace these before launch.

---

## 1. What's included

- **Storefront**: home, shop (filter/search/sort/pagination), product detail
  (gallery, size & color variations, inch size chart), cart, checkout (COD +
  manual bKash/Nagad), order confirmation, guest order tracking, optional
  customer accounts, sitemap.xml, robots.txt.
- **Admin panel** (`/admin`): dashboard with real sales stats, category &
  subcategory tree management, full product CRUD (images, variations, size
  chart), order management with status timeline & printable invoice,
  customers, and settings (store info, homepage content, delivery zones,
  payment methods, order statuses, admin users).
- **Database**: normalized MySQL/InnoDB schema (`database/schema.sql`) plus
  realistic sample data — 20 products across sarees, three-piece, kurtis,
  dresses, abayas, bags, jewelry, hijabs and footwear (`database/seed.sql`).
- **Security**: PDO prepared statements throughout, hashed admin passwords,
  CSRF protection on every form, session-timeout + login rate-limiting,
  server-verified image uploads (real MIME sniffing, randomized filenames),
  and server-side-only price/stock calculation — nothing from the browser
  is ever trusted for money or inventory.

This has been functionally tested end-to-end against a live MySQL database
during development: browsing, add-to-cart, checkout → real order creation
with stock decrement, admin login, product creation, image upload (including
rejecting disguised malicious uploads), order status updates, and CSRF/rate
limiting all verified working.

---

## 2. Requirements

- PHP **8.1+** with extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`
  (most shared hosts have all of these enabled by default)
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` and `.htaccess` support (`AllowOverride All`)

No Node.js, Composer, Docker, or build step is required.

---

## 3. Installation

### Step 1 — Upload the files
Upload everything to your hosting account so that `index.php` sits at your
domain's document root (e.g. `public_html/`).

### Step 2 — Create the database
In your hosting control panel (or via CLI), create a MySQL database and a
user with full privileges on it. Then import the schema and sample data, in
this order:

```bash
mysql -u YOUR_DB_USER -p YOUR_DB_NAME < database/schema.sql
mysql -u YOUR_DB_USER -p YOUR_DB_NAME < database/seed.sql   # optional but recommended for first run
```

If your host only offers phpMyAdmin, use its **Import** tab for both files,
in the same order.

### Step 3 — Configure the app
Copy the example config and fill in your real values:

```bash
cp config/config.example.php config/config.php
```

Edit `config/config.php`:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — your database credentials
- `BASE_URL` — your real domain, no trailing slash (e.g. `https://yourstore.com`)
- `APP_KEY` — replace with a random string:
  `php -r "echo bin2hex(random_bytes(32));"`
- `APP_DEBUG` — set to `false` before going live

### Step 4 — Set upload permissions
Make sure the web server can write to the uploads folder:

```bash
chmod -R 755 uploads/products
```

### Step 5 — Create your admin account
`/admin` has no default credentials — you create the first account yourself
with a securely hashed password:

```bash
php database/create_admin.php
```

Follow the prompts (name, username, email, password — 8+ characters). This
creates a **Super Admin** account.

*No SSH access?* Temporarily copy `database/create_admin.php` to your
project root, open it once in a browser, submit the form, then **delete the
copy immediately** — it has no login screen of its own.

### Step 6 — You're live
- Storefront: `https://yourstore.com/`
- Admin panel: `https://yourstore.com/admin/login.php`

---

## 4. First things to customize

All of these are in **Admin → Settings**, no code changes needed:
- Store name, logo, favicon, tagline, contact info, Facebook/Instagram links
- Homepage hero heading/subtext/button, promo banner text
- About page content, footer text
- Delivery zones and charges (Admin → Delivery Zones)
- Payment methods — replace the placeholder bKash/Nagad numbers with your
  real merchant numbers (Admin → Payment Methods)
- Order statuses and their colors (Admin → Order Statuses)

Then replace the sample products with your real catalog via
**Admin → Products**, and the placeholder sample images with real product
photography via each product's **Images** tab.

---

## 5. Project structure

```
/config           Configuration (config.php is git/deploy-ignored)
/core             Framework-free core: DB, Auth, Cart, CSRF, helpers
/models           All database/business logic (one class per entity)
/controllers      Storefront request handlers
/views            Storefront templates (partials/ has reusable pieces)
/assets           Storefront CSS/JS/placeholder images
/uploads/products Admin-uploaded product images (writable)
/admin            Completely separate admin app
  /assets           Admin-only CSS/JS
  /views            Admin templates
/database         schema.sql, seed.sql, create_admin.php
index.php         Storefront front controller (routes all clean URLs)
.htaccess         Rewrite rules + security headers
```

Admin pages are simple, direct PHP scripts (`/admin/products.php`,
`/admin/orders.php`, etc.) — each one requires `admin/bootstrap.php` (which
enforces login), calls into the model layer, and renders a view. This keeps
the admin easy to navigate and deploy on shared hosting without a second
router, while still keeping database logic, business logic, and presentation
in separate files as required.

---

## 6. Security notes

- Never edit `product_images`/order totals by hand expecting the storefront
  to trust client input — all prices, stock, and totals are always
  recalculated server-side at checkout inside a database transaction.
- `config/`, `core/`, `models/`, `controllers/`, and `database/` are all
  blocked from direct web access via `.htaccess`.
- Rotate `APP_KEY` and your admin passwords if you ever suspect they've
  leaked, and keep `APP_DEBUG` set to `false` in production so raw errors
  are never shown to visitors.

---

## 7. Extending the system

The schema and code were built with room to grow:
- **Coupons/discounts**: `orders.discount_amount` already exists — add a
  `coupons` table and apply it in `Order::createFromCart()`.
- **More delivery zones**: just add rows in Admin → Delivery Zones, no code
  changes needed.
- **More payment methods**: Admin → Payment Methods → Add — any manual
  method (Rocket, Upay, bank transfer) works the same way as bKash/Nagad.
- **Wishlist**: the product card UI already has a wishlist icon placeholder;
  wire it to a new `wishlists` table + a small controller when ready.
