# ShopSphere — Full-Stack E-Commerce Website

A complete e-commerce web application with a **customer storefront** and an
**admin back-office**, built with **HTML5 / CSS3 / JavaScript** on the front end
and **PHP (PDO) + MySQL** on the back end. It implements the full project
specification: a 7-table schema, 8 public/customer pages, 5 admin pages, and all
the supporting server-side scripts.

> All displayed content (products, categories, cart, orders, messages) comes
> from the database — there is no hard-coded catalogue in the pages.

---

## Tech stack

| Layer     | Technology                                   |
|-----------|----------------------------------------------|
| Front end | HTML5, CSS3 (responsive), vanilla JavaScript |
| Back end  | PHP 8 (PDO, prepared statements)             |
| Database  | MySQL 8 / MariaDB                            |
| Auth      | Sessions + `password_hash()` / `password_verify()` |

## Features

**Storefront (customer)**
- Home page with hero, category strip and DB-driven featured products.
- Product listing with **search by name**, **filter by category**, and **price/name sorting** (all via `$_GET` + prepared statements).
- Product detail page (`?id=`) with a stock-bounded quantity selector and add-to-cart.
- Database-backed shopping cart: update quantity, remove items, live subtotal / tax / total.
- Checkout that creates an order, copies the cart into `order_items`, decrements stock and clears the cart — all in a transaction.
- Registration, login (role-based redirect), logout.
- Profile page: edit details, change password, and view order history with per-order details.
- Contact form that stores messages in the database.

**Admin back-office** (`/admin`, gated on `role = 'admin'`)
- Dashboard with summary cards (products, orders, users, unread messages) + recent orders.
- Product management: list + create + edit + delete (with confirmation).
- Order management: list with customer names, change status, view line items.
- User management: change role, delete (blocked when the user has orders / is yourself).
- Messages: list contact submissions, mark read/unread, delete.

## Security

- **Prepared statements** (PDO) everywhere → no SQL injection.
- **`htmlspecialchars()`** on all output via the `e()` helper → no XSS.
- Passwords stored with **`password_hash()`**, checked with `password_verify()`.
- **CSRF tokens** on every state-changing form (`verify_csrf()`).
- Server-side validation on all inputs; session fixation prevented via `session_regenerate_id()` on login.
- Admin pages and per-user resources (orders/cart) are access-controlled.

---

## Getting started

### 1. Create the database

```bash
mysql -u root -p < sql/schema.sql
```

This creates the `ecommerce_db` database, all 7 tables, and seed data
(categories, products, users, a sample order and a sample message).

### 2. Configure the connection

`config.php` reads its connection from environment variables, falling back to a
local MySQL default (`127.0.0.1`, db `ecommerce_db`, user `root`, no password).
Override as needed:

```bash
export APP_DB_DSN="mysql:host=127.0.0.1;dbname=ecommerce_db;charset=utf8mb4"
export APP_DB_USER="root"
export APP_DB_PASS="yourpassword"
```

(or just edit the defaults at the top of `config.php`).

### 3. Run

Drop the folder into your PHP server's web root (XAMPP/Apache/Nginx), or use the
built-in server for a quick look:

```bash
php -S localhost:8000
```

Then open <http://localhost:8000/index.php>.

### Demo logins

| Role     | Email                 | Password   |
|----------|-----------------------|------------|
| Admin    | `admin@codenest.test` | `Admin@123`|
| Customer | `sara@example.com`    | `Sara@123` |
| Customer | `omar@example.com`    | `Omar@123` |

---

## Project structure

```
ecommerce/
├── config.php                 # PDO connection + app constants
├── index.php                  # home (hero, featured products)
├── products.php               # catalogue: search / filter / sort
├── product-detail.php         # single product (?id=) + add to cart
├── cart.php                    # cart view, update/remove, summary
├── checkout.php               # transactional order placement
├── order-confirmation.php     # post-checkout receipt (owner-only)
├── contact.php / contact_process.php
├── login.php / register.php / logout.php
├── profile.php                # details, password, order history
├── add_to_cart.php / update_cart.php / remove_from_cart.php
├── admin/
│   ├── dashboard.php  products.php  orders.php  users.php  messages.php
├── includes/
│   ├── functions.php          # session, escaping, auth, CSRF, flash
│   ├── header.php  footer.php  admin_tabs.php
├── css/style.css              # responsive design system
├── js/main.js                 # mobile nav, AJAX add-to-cart, confirms
└── sql/schema.sql             # database schema + seed data
```

## Notes on design decisions

- **Cart requires login.** Guests are redirected to sign in before adding to the
  cart, keeping every cart database-backed (`cart_items`) and avoiding
  localStorage/DB merge edge cases. (The spec permits either approach.)
- **Pages are `.php`** so content is injected dynamically; the same file renders
  the form and handles its `POST` where that keeps related logic together.
- **Portable SQL.** Queries avoid vendor-specific functions, so the exact same
  code runs on MySQL (production) and was smoke-tested end-to-end against SQLite.
