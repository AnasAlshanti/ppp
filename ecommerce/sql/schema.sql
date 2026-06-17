-- ============================================================================
--  ShopSphere — E-Commerce Database Schema
--  Target: MySQL 8.x / MariaDB 10.4+
--
--  Usage:
--    mysql -u root -p < sql/schema.sql
--  (creates the database `ecommerce_db`, all 7 tables, and seed data)
--
--  Seed logins:
--    Admin     : admin@codenest.test  /  Admin@123
--    Customer  : sara@example.com     /  Sara@123
--    Customer  : omar@example.com     /  Omar@123
-- ============================================================================

DROP DATABASE IF EXISTS ecommerce_db;
CREATE DATABASE ecommerce_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- ---------------------------------------------------------------------------
-- 1. users  — stores both customers and admins
-- ---------------------------------------------------------------------------
CREATE TABLE users (
  user_id     INT             NOT NULL AUTO_INCREMENT,
  full_name   VARCHAR(100)    NOT NULL,
  email       VARCHAR(100)    NOT NULL,
  password    VARCHAR(255)    NOT NULL,                       -- stored hashed (password_hash)
  phone       VARCHAR(20),
  address     TEXT,
  role        ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 2. categories
-- ---------------------------------------------------------------------------
CREATE TABLE categories (
  category_id  INT          NOT NULL AUTO_INCREMENT,
  name         VARCHAR(50)  NOT NULL,
  description  TEXT,
  PRIMARY KEY (category_id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 3. products
-- ---------------------------------------------------------------------------
CREATE TABLE products (
  product_id      INT            NOT NULL AUTO_INCREMENT,
  name            VARCHAR(150)   NOT NULL,
  description     TEXT,
  price           DECIMAL(10,2)  NOT NULL,
  stock_quantity  INT            NOT NULL DEFAULT 0,
  image_url       VARCHAR(255),
  category_id     INT,
  created_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (product_id),
  KEY idx_products_category (category_id),
  KEY idx_products_name (name),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories (category_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 4. cart_items  — one row per (user, product); quantity must be positive
-- ---------------------------------------------------------------------------
CREATE TABLE cart_items (
  cart_id     INT        NOT NULL AUTO_INCREMENT,
  user_id     INT        NOT NULL,
  product_id  INT        NOT NULL,
  quantity    INT        NOT NULL CHECK (quantity > 0),
  added_at    TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (cart_id),
  UNIQUE KEY uq_cart_user_product (user_id, product_id),
  KEY idx_cart_user (user_id),
  CONSTRAINT fk_cart_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_cart_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 5. orders
-- ---------------------------------------------------------------------------
CREATE TABLE orders (
  order_id          INT            NOT NULL AUTO_INCREMENT,
  user_id           INT            NOT NULL,
  order_date        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_amount      DECIMAL(10,2)  NOT NULL,
  status            ENUM('pending','paid','shipped','delivered','cancelled')
                    NOT NULL DEFAULT 'pending',
  shipping_address  TEXT,
  PRIMARY KEY (order_id),
  KEY idx_orders_user (user_id),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users (user_id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 6. order_items  — unit_price is the price captured at time of purchase
-- ---------------------------------------------------------------------------
CREATE TABLE order_items (
  order_item_id  INT            NOT NULL AUTO_INCREMENT,
  order_id       INT            NOT NULL,
  product_id     INT,
  quantity       INT            NOT NULL,
  unit_price     DECIMAL(10,2)  NOT NULL,
  PRIMARY KEY (order_item_id),
  KEY idx_order_items_order (order_id),
  CONSTRAINT fk_order_items_order
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_order_items_product
    FOREIGN KEY (product_id) REFERENCES products (product_id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------------
-- 7. contacts  — messages submitted via the contact form
-- ---------------------------------------------------------------------------
CREATE TABLE contacts (
  message_id    INT          NOT NULL AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NOT NULL,
  subject       VARCHAR(100),
  message       TEXT         NOT NULL,
  is_read       BOOLEAN      NOT NULL DEFAULT FALSE,
  submitted_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (message_id)
) ENGINE=InnoDB;

-- ============================================================================
--  Seed data  (no hard-coded content lives in the PHP pages — it all comes
--  from here)
-- ============================================================================

-- Users (passwords are bcrypt hashes — see header for the plaintext logins)
INSERT INTO users (full_name, email, password, phone, address, role) VALUES
('Site Administrator', 'admin@codenest.test', '$2y$12$/iWlI3skwhH/lkxnEkx0H.lbkqpbKNf/kUf3Qlz5bE6yHtru2lL9m', '+962790000000', 'HQ, Amman, Jordan', 'admin'),
('Sara Khalil',        'sara@example.com',    '$2y$12$6Fx9YNV6.skVKbj/xUf2ieVqGZN/Nqq/jV5J3FniIEhTA7jpdP3Si', '+962791111111', '12 Rainbow St, Amman', 'customer'),
('Omar Nasser',        'omar@example.com',    '$2y$12$B3Y972nHITpST9B05wFy4.SQr.tO9Sw/e5wz2yowHN60pOLBhflKC', '+962792222222', '45 University Rd, Irbid', 'customer');

-- Categories
INSERT INTO categories (name, description) VALUES
('Electronics',  'Audio, cameras and everyday smart electronics.'),
('Accessories',  'Keyboards, mice, chargers and desk peripherals.'),
('Wearables',    'Smartwatches and fitness tracking devices.'),
('Home Office',  'Lighting, stands and ergonomics for your workspace.');

-- Products (category_id values follow the insert order above: 1..4)
INSERT INTO products (name, description, price, stock_quantity, image_url, category_id) VALUES
('Aurora Wireless Headphones', 'Over-ear wireless headphones with active noise cancellation and 30-hour battery life.', 129.99, 40,  'assets/products/aurora-headphones.svg', 1),
('Pulse Bluetooth Speaker',    'Compact 360° speaker with deep bass and IPX7 water resistance.',                        59.99,  75,  'assets/products/pulse-speaker.svg',     1),
('Nimbus 4K Action Camera',    'Rugged 4K/60fps action camera with stabilization and waterproof housing.',              199.99, 25,  'assets/products/nimbus-camera.svg',     1),
('Quill Noise-Canceling Earbuds', 'True-wireless earbuds with hybrid ANC and a pocket-size charging case.',             99.99,  0,   'assets/products/quill-earbuds.svg',     1),
('Forge Mechanical Keyboard',  'Hot-swappable mechanical keyboard with tactile switches and RGB backlight.',            89.99,  60,  'assets/products/forge-keyboard.svg',    2),
('Glide Ergonomic Mouse',      'Silent ergonomic mouse with adjustable DPI and a sculpted grip.',                       39.99,  120, 'assets/products/glide-mouse.svg',       2),
('Volt USB-C Charger 65W',     'Gallium-nitride 65W charger that fast-charges laptops, tablets and phones.',            29.99,  200, 'assets/products/volt-charger.svg',      2),
('Zen Smartwatch Series 5',    'AMOLED smartwatch with GPS, heart-rate and SpO2 tracking.',                             179.99, 50,  'assets/products/zen-smartwatch.svg',    3),
('Stride Fitness Band',        'Lightweight fitness band with sleep tracking and a 10-day battery.',                    49.99,  90,  'assets/products/stride-band.svg',       3),
('Lumin LED Desk Lamp',        'Dimmable LED desk lamp with adjustable color temperature and USB port.',                34.99,  80,  'assets/products/lumin-lamp.svg',        4),
('Summit Laptop Stand',        'Aluminium laptop stand with adjustable height and a ventilated design.',                44.99,  65,  'assets/products/summit-stand.svg',      4),
('Atlas Standing Desk Mat',    'Anti-fatigue standing desk mat with a non-slip surface.',                               54.99,  35,  'assets/products/atlas-mat.svg',         4);

-- A sample completed order for Sara (user_id = 2) so the profile/admin order
-- views have data to show out of the box.
INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES
(2, 219.98, 'paid', '12 Rainbow St, Amman');

INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES
(1, 1, 1, 129.99),
(1, 5, 1, 89.99);

-- A sample contact message (unread) for the admin messages view.
INSERT INTO contacts (name, email, subject, message, is_read) VALUES
('Lina Haddad', 'lina@example.com', 'Inquiry', 'Do the Aurora headphones ship internationally?', FALSE);
