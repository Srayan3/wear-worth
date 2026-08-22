-- ============================================================================
--  Fashion Store — Database Schema
--  Engine: InnoDB | Charset: utf8mb4
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------------------------------------------------------
-- ADMINS
-- ----------------------------------------------------------------------------
CREATE TABLE admins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(120)        NOT NULL,
    username        VARCHAR(60)         NOT NULL,
    email           VARCHAR(190)        NOT NULL,
    password_hash   VARCHAR(255)        NOT NULL,
    role            ENUM('super_admin','manager','staff') NOT NULL DEFAULT 'staff',
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    last_login_at   DATETIME            NULL,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admins_username (username),
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tracks login attempts for rate limiting / lockout
CREATE TABLE admin_login_attempts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(60)         NOT NULL,
    ip_address      VARCHAR(45)         NOT NULL,
    was_successful  TINYINT(1)          NOT NULL DEFAULT 0,
    attempted_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_attempts_lookup (username, ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- CUSTOMERS (optional accounts — guest checkout is always available)
-- ----------------------------------------------------------------------------
CREATE TABLE customers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(150)        NOT NULL,
    phone           VARCHAR(30)         NOT NULL,
    email           VARCHAR(190)        NULL,
    password_hash   VARCHAR(255)        NULL,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_customers_phone (phone),
    KEY idx_customers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- PRODUCT HIERARCHY: gender -> category -> subcategory -> product
-- ----------------------------------------------------------------------------
CREATE TABLE genders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(60)         NOT NULL,
    slug            VARCHAR(70)         NOT NULL,
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_genders_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gender_id       INT UNSIGNED        NOT NULL,
    name            VARCHAR(100)        NOT NULL,
    slug            VARCHAR(120)        NOT NULL,
    image           VARCHAR(255)        NULL,
    description     TEXT                NULL,
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_categories_slug (slug),
    KEY idx_categories_gender (gender_id),
    CONSTRAINT fk_categories_gender FOREIGN KEY (gender_id) REFERENCES genders(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE subcategories (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id     INT UNSIGNED        NOT NULL,
    name            VARCHAR(100)        NOT NULL,
    slug            VARCHAR(120)        NOT NULL,
    image           VARCHAR(255)        NULL,
    description     TEXT                NULL,
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_subcategories_slug (slug),
    KEY idx_subcategories_category (category_id),
    CONSTRAINT fk_subcategories_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subcategory_id      INT UNSIGNED        NOT NULL,
    name                VARCHAR(180)        NOT NULL,
    slug                VARCHAR(200)        NOT NULL,
    sku                 VARCHAR(60)         NOT NULL,
    short_description   VARCHAR(500)        NULL,
    description         TEXT                NULL,
    price               DECIMAL(10,2)       NOT NULL,
    discount_price       DECIMAL(10,2)       NULL,
    stock_quantity       INT UNSIGNED        NOT NULL DEFAULT 0,
    stock_status         ENUM('in_stock','out_of_stock') NOT NULL DEFAULT 'in_stock',
    has_variations        TINYINT(1)          NOT NULL DEFAULT 0,
    size_chart_type       ENUM('clothing','footwear') NOT NULL DEFAULT 'clothing',
    is_featured          TINYINT(1)          NOT NULL DEFAULT 0,
    is_new_arrival        TINYINT(1)          NOT NULL DEFAULT 0,
    is_popular           TINYINT(1)          NOT NULL DEFAULT 0,
    is_active            TINYINT(1)          NOT NULL DEFAULT 1,
    views_count           INT UNSIGNED        NOT NULL DEFAULT 0,
    created_at           DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_products_slug (slug),
    UNIQUE KEY uq_products_sku (sku),
    KEY idx_products_subcategory (subcategory_id),
    KEY idx_products_flags (is_active, is_featured, is_new_arrival, is_popular),
    KEY idx_products_created (created_at),
    KEY idx_products_price (price),
    FULLTEXT KEY ft_products_search (name, short_description, description),
    CONSTRAINT fk_products_subcategory FOREIGN KEY (subcategory_id) REFERENCES subcategories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_images (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED        NOT NULL,
    image_path      VARCHAR(255)        NOT NULL,
    is_primary      TINYINT(1)          NOT NULL DEFAULT 0,
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_product_images_product (product_id),
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-product size chart. Two shapes share one table: clothing (inch
-- measurements) uses chest/waist/hip/length; footwear (size conversion)
-- uses uk/eu/us. Which one a product shows is products.size_chart_type.
CREATE TABLE product_size_chart (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED        NOT NULL,
    size_label      VARCHAR(20)         NOT NULL,   -- clothing: XS/S/M/L...  footwear: brand's own size (e.g. 38)
    chest_in        DECIMAL(5,2)        NULL,
    waist_in        DECIMAL(5,2)        NULL,
    hip_in          DECIMAL(5,2)        NULL,
    length_in       DECIMAL(5,2)        NULL,
    uk_size         VARCHAR(10)         NULL,        -- footwear only: UK / Bata
    eu_size         VARCHAR(10)         NULL,        -- footwear only: EU / Apex
    us_size         VARCHAR(10)         NULL,        -- footwear only: US
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    KEY idx_size_chart_product (product_id),
    CONSTRAINT fk_size_chart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Purchasable size/color variations. Optional — has_variations flag on product controls this.
CREATE TABLE product_variations (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id      INT UNSIGNED        NOT NULL,
    size_label      VARCHAR(20)         NULL,
    color_name      VARCHAR(40)         NULL,
    color_hex       VARCHAR(7)          NULL,
    sku             VARCHAR(60)         NULL,
    price_override  DECIMAL(10,2)       NULL,
    stock_quantity  INT UNSIGNED        NOT NULL DEFAULT 0,
    image_path      VARCHAR(255)        NULL,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_variations_product (product_id),
    CONSTRAINT fk_variations_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- CART (guest via session_id, or customer_id when logged in)
-- ----------------------------------------------------------------------------
CREATE TABLE carts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id     INT UNSIGNED        NULL,
    session_id      VARCHAR(128)        NULL,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_carts_customer (customer_id),
    KEY idx_carts_session (session_id),
    CONSTRAINT fk_carts_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id         INT UNSIGNED        NOT NULL,
    product_id      INT UNSIGNED        NOT NULL,
    variation_id    INT UNSIGNED        NULL,
    quantity        INT UNSIGNED        NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cart_items_cart (cart_id),
    KEY idx_cart_items_product (product_id),
    CONSTRAINT fk_cart_items_cart FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_items_variation FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- DELIVERY & PAYMENT (admin-configurable)
-- ----------------------------------------------------------------------------
CREATE TABLE delivery_zones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(100)        NOT NULL,     -- "Inside Dhaka", "Outside Dhaka", ...
    charge          DECIMAL(10,2)       NOT NULL DEFAULT 0,
    is_default      TINYINT(1)          NOT NULL DEFAULT 0,
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payment_methods (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code            VARCHAR(30)         NOT NULL,     -- cod, bkash, nagad, ...
    name            VARCHAR(100)        NOT NULL,
    account_number  VARCHAR(60)         NULL,
    instructions    TEXT                NULL,
    requires_reference TINYINT(1)       NOT NULL DEFAULT 1,  -- show/require a transaction ID at checkout
    is_active       TINYINT(1)          NOT NULL DEFAULT 1,
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_payment_methods_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- ORDERS
-- ----------------------------------------------------------------------------
CREATE TABLE order_statuses (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name            VARCHAR(50)         NOT NULL,
    slug            VARCHAR(50)         NOT NULL,
    color           VARCHAR(7)          NOT NULL DEFAULT '#0A0A0A',
    sort_order      INT UNSIGNED        NOT NULL DEFAULT 0,
    is_default      TINYINT(1)          NOT NULL DEFAULT 0,
    UNIQUE KEY uq_order_statuses_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number        VARCHAR(30)         NOT NULL,
    customer_id         INT UNSIGNED        NULL,
    full_name           VARCHAR(150)        NOT NULL,
    phone               VARCHAR(30)         NOT NULL,
    email               VARCHAR(190)        NULL,
    district            VARCHAR(100)        NOT NULL,
    area                VARCHAR(150)        NOT NULL,
    address             TEXT                NOT NULL,
    order_notes         TEXT                NULL,
    subtotal            DECIMAL(10,2)       NOT NULL DEFAULT 0,
    discount_amount     DECIMAL(10,2)       NOT NULL DEFAULT 0,
    delivery_charge     DECIMAL(10,2)       NOT NULL DEFAULT 0,
    total               DECIMAL(10,2)       NOT NULL DEFAULT 0,
    payment_method_id   INT UNSIGNED        NOT NULL,
    payment_status      ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
    status_id           INT UNSIGNED        NOT NULL,
    admin_notes         TEXT                NULL,
    created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_orders_number (order_number),
    KEY idx_orders_status (status_id),
    KEY idx_orders_phone (phone),
    KEY idx_orders_created (created_at),
    KEY idx_orders_customer (customer_id),
    CONSTRAINT fk_orders_payment_method FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id) ON DELETE RESTRICT,
    CONSTRAINT fk_orders_status FOREIGN KEY (status_id) REFERENCES order_statuses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- order_items store a full SNAPSHOT — prices never change retroactively
CREATE TABLE order_items (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id                INT UNSIGNED        NOT NULL,
    product_id              INT UNSIGNED        NULL,
    variation_id            INT UNSIGNED        NULL,
    product_name_snapshot   VARCHAR(180)        NOT NULL,
    sku_snapshot            VARCHAR(60)         NULL,
    variation_label_snapshot VARCHAR(100)       NULL,
    price_snapshot          DECIMAL(10,2)       NOT NULL,
    quantity                INT UNSIGNED        NOT NULL,
    line_total              DECIMAL(10,2)       NOT NULL,
    KEY idx_order_items_order (order_id),
    KEY idx_order_items_product (product_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    CONSTRAINT fk_order_items_variation FOREIGN KEY (variation_id) REFERENCES product_variations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_status_history (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED        NOT NULL,
    status_id       INT UNSIGNED        NOT NULL,
    note            VARCHAR(255)        NULL,
    changed_by      VARCHAR(120)        NULL,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status_history_order (order_id),
    CONSTRAINT fk_status_history_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_status_history_status FOREIGN KEY (status_id) REFERENCES order_statuses(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- SETTINGS (key/value — store info, hero content, social links, footer, etc.)
-- ----------------------------------------------------------------------------
CREATE TABLE settings (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key     VARCHAR(100)        NOT NULL,
    setting_value   LONGTEXT            NULL,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE newsletter_subscribers (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email           VARCHAR(190)        NOT NULL,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_newsletter_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
