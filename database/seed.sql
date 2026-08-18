-- ============================================================================
--  Fashion Store — Sample Seed Data
--  Run AFTER schema.sql. Safe to re-run on a fresh database only
--  (uses fixed auto-increment order — do not run twice without truncating).
-- ============================================================================

SET NAMES utf8mb4;

-- ----------------------------------------------------------------------------
-- Gender / Category / Subcategory hierarchy
-- ----------------------------------------------------------------------------
INSERT INTO genders (id, name, slug, sort_order, is_active) VALUES
(1, 'Women', 'women', 1, 1);

INSERT INTO categories (id, gender_id, name, slug, description, sort_order, is_active) VALUES
(1, 1, 'Clothing',    'clothing',    'Sarees, three-piece sets, kurtis, dresses and abayas.', 1, 1),
(2, 1, 'Accessories', 'accessories', 'Bags, jewelry, scarves and hijabs.',                      2, 1),
(3, 1, 'Footwear',    'footwear',    'Flats and heels to complete the look.',                   3, 1);

INSERT INTO subcategories (id, category_id, name, slug, description, sort_order, is_active) VALUES
(1,  1, 'Sarees',               'sarees',              'Handloom and printed sarees.', 1, 1),
(2,  1, 'Three-Piece',          'three-piece',         'Unstitched and stitched three-piece sets.', 2, 1),
(3,  1, 'Kurtis & Tunics',      'kurtis-tunics',       'Everyday and occasion kurtis.', 3, 1),
(4,  1, 'Dresses & Western Wear','dresses-western',    'Contemporary silhouettes.', 4, 1),
(5,  1, 'Abayas',               'abayas',              'Modest wear and kaftans.', 5, 1),
(6,  2, 'Bags',                 'bags',                'Everyday and occasion bags.', 1, 1),
(7,  2, 'Jewelry',              'jewelry',             'Earrings, necklaces and sets.', 2, 1),
(8,  2, 'Scarves & Hijabs',     'scarves-hijabs',      'Chiffon, modal and cotton hijabs.', 3, 1),
(9,  3, 'Flats',                'flats',               'Everyday comfort flats.', 1, 1),
(10, 3, 'Heels',                'heels',               'Block heels and sandals.', 2, 1);

-- ----------------------------------------------------------------------------
-- Products (20 sample items)
-- ----------------------------------------------------------------------------
INSERT INTO products
(id, subcategory_id, name, slug, sku, short_description, description, price, discount_price, stock_quantity, stock_status, has_variations, is_featured, is_new_arrival, is_popular, is_active)
VALUES
(1,  1, 'Muslin Jamdani Saree — Ivory', 'muslin-jamdani-saree-ivory', 'WS-SAR-001',
    'Handwoven muslin jamdani with a traditional woven motif border.',
    'A handwoven muslin jamdani saree finished with a traditional motif border along the pallu. Lightweight and breathable, ideal for festive daywear. Comes with a matching blouse piece.',
    4200.00, NULL, 18, 'in_stock', 0, 0, 1, 0, 1),

(2,  1, 'Handloom Cotton Saree — Charcoal Black', 'handloom-cotton-saree-charcoal-black', 'WS-SAR-002',
    'Soft handloom cotton with a fine self-woven stripe.',
    'Woven on traditional handlooms, this cotton saree has a fine self-stripe texture and a subtle contrast border. Easy to drape and comfortable for all-day wear.',
    2850.00, 2450.00, 24, 'in_stock', 0, 0, 0, 1, 1),

(3,  2, 'Embroidered Georgette Three-Piece — Black', 'embroidered-georgette-three-piece-black', 'WS-3PC-001',
    'Hand-embroidered georgette set with dupatta.',
    'A three-piece set in flowing georgette, finished with fine hand embroidery at the neckline and hem. Includes kameez, salwar/trouser fabric and matching dupatta. Unstitched — tailor to your measurements.',
    5400.00, NULL, 15, 'in_stock', 1, 1, 0, 1, 1),

(4,  2, 'Cotton Three-Piece Set — Off-White', 'cotton-three-piece-off-white', 'WS-3PC-002',
    'Breathable cotton set with delicate thread embroidery.',
    'A relaxed cotton three-piece in off-white, with delicate thread embroidery along the neckline. Comes stitched in standard sizes, ready to wear.',
    3200.00, NULL, 20, 'in_stock', 1, 0, 0, 0, 1),

(5,  3, 'Block-Print Cotton Kurti — White', 'block-print-cotton-kurti-white', 'WS-KUR-001',
    'Hand block-printed cotton kurti with side slits.',
    'A relaxed-fit cotton kurti finished with traditional hand block printing and side slits for ease of movement. Pairs beautifully with palazzos or jeans.',
    1650.00, NULL, 30, 'in_stock', 1, 0, 1, 0, 1),

(6,  3, 'A-Line Linen Tunic — Black', 'a-line-linen-tunic-black', 'WS-KUR-002',
    'Minimal A-line tunic in breathable linen.',
    'A clean, minimal A-line tunic cut from breathable linen blend fabric. Designed for everyday wear with a flattering silhouette and side pockets.',
    2100.00, 1800.00, 22, 'in_stock', 1, 0, 0, 1, 1),

(7,  3, 'Chikankari Kurti — Ivory', 'chikankari-kurti-ivory', 'WS-KUR-003',
    'Hand chikankari embroidery on soft cotton.',
    'Traditional hand chikankari embroidery worked onto soft cotton fabric in an ivory tone. A timeless piece for both daywear and occasion.',
    2950.00, NULL, 16, 'in_stock', 1, 0, 0, 0, 1),

(8,  4, 'Black Satin Wrap Dress', 'black-satin-wrap-dress', 'WS-DRS-001',
    'Fluid satin dress with a self-tie waist.',
    'A fluid satin wrap dress with a flattering self-tie waist and midi-length hem. Finished with a subtle sheen for evening occasions.',
    4500.00, 3600.00, 12, 'in_stock', 1, 1, 0, 0, 1),

(9,  4, 'White Tiered Midi Dress', 'white-tiered-midi-dress', 'WS-DRS-002',
    'Soft cotton-voile dress with tiered hem detail.',
    'A breezy cotton-voile midi dress with a tiered hem and adjustable straps. Fully lined, with side pockets for everyday practicality.',
    3800.00, NULL, 14, 'in_stock', 1, 0, 1, 1, 1),

(10, 4, 'Floral Chiffon Midi Dress', 'floral-chiffon-midi-dress', 'WS-DRS-003',
    'Printed chiffon dress with a fitted bodice.',
    'A printed chiffon midi dress with a fitted bodice and flowing skirt. Fully lined with a concealed back zip.',
    3950.00, NULL, 10, 'in_stock', 1, 0, 0, 1, 1),

(11, 5, 'Classic Nida Abaya — Black', 'classic-nida-abaya-black', 'WS-ABY-001',
    'Structured nida fabric abaya with a clean silhouette.',
    'A structured, closed-style abaya cut from premium nida fabric with a clean, minimal silhouette and full-length sleeves. Comes with a matching inner belt.',
    3600.00, NULL, 20, 'in_stock', 1, 1, 0, 0, 1),

(12, 5, 'Embroidered Kaftan Abaya', 'embroidered-kaftan-abaya', 'WS-ABY-002',
    'Relaxed kaftan-cut abaya with hand embroidery at the cuffs.',
    'A relaxed kaftan-cut abaya finished with fine hand embroidery at the cuffs and front placket. Currently sold out — restocking soon.',
    4100.00, NULL, 0, 'out_of_stock', 1, 0, 0, 0, 1),

(13, 6, 'Mini Shoulder Bag — Black', 'mini-shoulder-bag-black', 'WS-BAG-001',
    'Compact structured shoulder bag in vegan leather.',
    'A compact, structured shoulder bag in vegan leather with an adjustable chain strap and magnetic closure. Fits a phone, cards and essentials.',
    2200.00, NULL, 25, 'in_stock', 1, 0, 1, 0, 1),

(14, 6, 'Structured Leather Handbag — Tan', 'structured-leather-handbag-tan', 'WS-BAG-002',
    'Top-handle handbag with detachable strap.',
    'A structured top-handle handbag in genuine leather with a detachable, adjustable cross-body strap and interior zip pocket.',
    5200.00, NULL, 9, 'in_stock', 1, 1, 0, 0, 1),

(15, 7, 'Pearl Drop Earrings', 'pearl-drop-earrings', 'WS-JWL-001',
    'Freshwater pearl drops on gold-tone stems.',
    'Freshwater pearl drops set on delicate gold-tone stems. Lightweight enough for all-day wear, from desk to dinner.',
    950.00, NULL, 40, 'in_stock', 0, 0, 0, 1, 1),

(16, 7, 'Layered Chain Necklace', 'layered-chain-necklace', 'WS-JWL-002',
    'Two-layer gold-tone chain necklace.',
    'A two-layer gold-tone chain necklace designed to be worn together or separately. Tarnish-resistant plating.',
    1150.00, NULL, 35, 'in_stock', 0, 0, 0, 0, 1),

(17, 8, 'Premium Chiffon Hijab — Black', 'premium-chiffon-hijab-black', 'WS-HIJ-001',
    'Lightweight chiffon hijab with finished edges.',
    'A lightweight, breathable chiffon hijab with neatly finished edges and a soft drape that holds its shape through the day.',
    650.00, NULL, 50, 'in_stock', 1, 0, 1, 0, 1),

(18, 8, 'Modal Hijab Set — Neutral Tones', 'modal-hijab-set-neutral-tones', 'WS-HIJ-002',
    'Set of three jersey-modal hijabs in neutral tones.',
    'A set of three soft jersey-modal hijabs in versatile neutral tones. Stretch fabric that stays put without pins.',
    1450.00, NULL, 28, 'in_stock', 1, 0, 0, 1, 1),

(19, 9, 'Pointed Ballet Flats — Black', 'pointed-ballet-flats-black', 'WS-FLT-001',
    'Cushioned flats with a pointed toe.',
    'Classic pointed-toe ballet flats with a cushioned footbed and non-slip sole — built for full days on your feet.',
    1850.00, NULL, 20, 'in_stock', 1, 1, 1, 0, 1),

(20, 10, 'Block Heel Sandals — Nude', 'block-heel-sandals-nude', 'WS-HLS-001',
    'Comfortable block heel with an ankle strap.',
    'A comfortable 2.5-inch block heel sandal with an adjustable ankle strap — stable enough for everyday wear, polished enough for evenings out.',
    2450.00, NULL, 17, 'in_stock', 1, 0, 1, 0, 1);

-- ----------------------------------------------------------------------------
-- Product images (2 per product — placeholder photography, replace in admin)
-- ----------------------------------------------------------------------------
INSERT INTO product_images (product_id, image_path, is_primary, sort_order) VALUES
(1,  'assets/images/placeholders/saree-1.svg', 1, 0), (1,  'assets/images/placeholders/saree-2.svg', 0, 1),
(2,  'assets/images/placeholders/saree-2.svg', 1, 0), (2,  'assets/images/placeholders/saree-1.svg', 0, 1),
(3,  'assets/images/placeholders/three-piece-1.svg', 1, 0), (3,  'assets/images/placeholders/three-piece-2.svg', 0, 1),
(4,  'assets/images/placeholders/three-piece-2.svg', 1, 0), (4,  'assets/images/placeholders/three-piece-1.svg', 0, 1),
(5,  'assets/images/placeholders/kurti-1.svg', 1, 0), (5,  'assets/images/placeholders/kurti-2.svg', 0, 1),
(6,  'assets/images/placeholders/kurti-2.svg', 1, 0), (6,  'assets/images/placeholders/kurti-1.svg', 0, 1),
(7,  'assets/images/placeholders/kurti-1.svg', 1, 0), (7,  'assets/images/placeholders/kurti-2.svg', 0, 1),
(8,  'assets/images/placeholders/dress-1.svg', 1, 0), (8,  'assets/images/placeholders/dress-2.svg', 0, 1),
(9,  'assets/images/placeholders/dress-2.svg', 1, 0), (9,  'assets/images/placeholders/dress-1.svg', 0, 1),
(10, 'assets/images/placeholders/dress-1.svg', 1, 0), (10, 'assets/images/placeholders/dress-2.svg', 0, 1),
(11, 'assets/images/placeholders/abaya-1.svg', 1, 0), (11, 'assets/images/placeholders/abaya-2.svg', 0, 1),
(12, 'assets/images/placeholders/abaya-2.svg', 1, 0), (12, 'assets/images/placeholders/abaya-1.svg', 0, 1),
(13, 'assets/images/placeholders/bag-1.svg', 1, 0), (13, 'assets/images/placeholders/bag-2.svg', 0, 1),
(14, 'assets/images/placeholders/bag-2.svg', 1, 0), (14, 'assets/images/placeholders/bag-1.svg', 0, 1),
(15, 'assets/images/placeholders/jewelry-1.svg', 1, 0), (15, 'assets/images/placeholders/jewelry-2.svg', 0, 1),
(16, 'assets/images/placeholders/jewelry-2.svg', 1, 0), (16, 'assets/images/placeholders/jewelry-1.svg', 0, 1),
(17, 'assets/images/placeholders/hijab-1.svg', 1, 0), (17, 'assets/images/placeholders/hijab-2.svg', 0, 1),
(18, 'assets/images/placeholders/hijab-2.svg', 1, 0), (18, 'assets/images/placeholders/hijab-1.svg', 0, 1),
(19, 'assets/images/placeholders/flats-1.svg', 1, 0), (19, 'assets/images/placeholders/flats-2.svg', 0, 1),
(20, 'assets/images/placeholders/heels-1.svg', 1, 0), (20, 'assets/images/placeholders/heels-2.svg', 0, 1);

-- ----------------------------------------------------------------------------
-- Size chart (inches) — demonstrated on a few clothing items
-- ----------------------------------------------------------------------------
INSERT INTO product_size_chart (product_id, size_label, chest_in, waist_in, hip_in, length_in, sort_order) VALUES
(3, 'S', 34, 28, 36, 42, 1), (3, 'M', 36, 30, 38, 43, 2), (3, 'L', 38, 32, 40, 44, 3), (3, 'XL', 40, 34, 42, 45, 4),
(8, 'S', 32, 26, 35, 40, 1), (8, 'M', 34, 28, 37, 41, 2), (8, 'L', 36, 30, 39, 42, 3),
(11,'S', 36, 32, 40, 54, 1), (11,'M', 38, 34, 42, 55, 2), (11,'L', 40, 36, 44, 56, 3);

-- ----------------------------------------------------------------------------
-- Purchasable variations (size and/or color)
-- ----------------------------------------------------------------------------
INSERT INTO product_variations (product_id, size_label, color_name, color_hex, sku, price_override, stock_quantity, is_active) VALUES
-- three-piece #3 (size only)
(3, 'S', NULL, NULL, 'WS-3PC-001-S', NULL, 4, 1),
(3, 'M', NULL, NULL, 'WS-3PC-001-M', NULL, 6, 1),
(3, 'L', NULL, NULL, 'WS-3PC-001-L', NULL, 3, 1),
(3, 'XL', NULL, NULL, 'WS-3PC-001-XL', NULL, 2, 1),
-- three-piece #4
(4, 'S', NULL, NULL, 'WS-3PC-002-S', NULL, 5, 1),
(4, 'M', NULL, NULL, 'WS-3PC-002-M', NULL, 7, 1),
(4, 'L', NULL, NULL, 'WS-3PC-002-L', NULL, 5, 1),
(4, 'XL', NULL, NULL, 'WS-3PC-002-XL', NULL, 3, 1),
-- kurti #5
(5, 'S', NULL, NULL, 'WS-KUR-001-S', NULL, 8, 1),
(5, 'M', NULL, NULL, 'WS-KUR-001-M', NULL, 10, 1),
(5, 'L', NULL, NULL, 'WS-KUR-001-L', NULL, 7, 1),
(5, 'XL', NULL, NULL, 'WS-KUR-001-XL', NULL, 5, 1),
-- tunic #6
(6, 'S', NULL, NULL, 'WS-KUR-002-S', NULL, 6, 1),
(6, 'M', NULL, NULL, 'WS-KUR-002-M', NULL, 8, 1),
(6, 'L', NULL, NULL, 'WS-KUR-002-L', NULL, 6, 1),
(6, 'XL', NULL, NULL, 'WS-KUR-002-XL', NULL, 2, 1),
-- kurti #7
(7, 'S', NULL, NULL, 'WS-KUR-003-S', NULL, 5, 1),
(7, 'M', NULL, NULL, 'WS-KUR-003-M', NULL, 6, 1),
(7, 'L', NULL, NULL, 'WS-KUR-003-L', NULL, 5, 1),
-- dress #8
(8, 'S', NULL, NULL, 'WS-DRS-001-S', NULL, 4, 1),
(8, 'M', NULL, NULL, 'WS-DRS-001-M', NULL, 5, 1),
(8, 'L', NULL, NULL, 'WS-DRS-001-L', NULL, 3, 1),
-- dress #9
(9, 'S', NULL, NULL, 'WS-DRS-002-S', NULL, 4, 1),
(9, 'M', NULL, NULL, 'WS-DRS-002-M', NULL, 6, 1),
(9, 'L', NULL, NULL, 'WS-DRS-002-L', NULL, 4, 1),
-- dress #10
(10, 'S', NULL, NULL, 'WS-DRS-003-S', NULL, 3, 1),
(10, 'M', NULL, NULL, 'WS-DRS-003-M', NULL, 4, 1),
(10, 'L', NULL, NULL, 'WS-DRS-003-L', NULL, 3, 1),
-- abaya #11
(11, 'S', NULL, NULL, 'WS-ABY-001-S', NULL, 6, 1),
(11, 'M', NULL, NULL, 'WS-ABY-001-M', NULL, 8, 1),
(11, 'L', NULL, NULL, 'WS-ABY-001-L', NULL, 6, 1),
-- abaya #12 (out of stock overall)
(12, 'S', NULL, NULL, 'WS-ABY-002-S', NULL, 0, 1),
(12, 'M', NULL, NULL, 'WS-ABY-002-M', NULL, 0, 1),
(12, 'L', NULL, NULL, 'WS-ABY-002-L', NULL, 0, 1),
-- bag #13 (color only)
(13, NULL, 'Black', '#141414', 'WS-BAG-001-BLK', NULL, 15, 1),
(13, NULL, 'Ivory', '#F4F2EE', 'WS-BAG-001-IVR', NULL, 10, 1),
-- bag #14 (color only)
(14, NULL, 'Tan', '#B08968', 'WS-BAG-002-TAN', NULL, 5, 1),
(14, NULL, 'Black', '#141414', 'WS-BAG-002-BLK', 5400.00, 4, 1),
-- hijab #17 (color only)
(17, NULL, 'Black', '#141414', 'WS-HIJ-001-BLK', NULL, 20, 1),
(17, NULL, 'Charcoal', '#3A3A3A', 'WS-HIJ-001-CHR', NULL, 15, 1),
(17, NULL, 'Ivory', '#F4F2EE', 'WS-HIJ-001-IVR', NULL, 15, 1),
-- hijab set #18 (no color split — single set option kept simple)
(18, NULL, 'Neutral Set', NULL, 'WS-HIJ-002-SET', NULL, 28, 1),
-- flats #19 (shoe size)
(19, '36', NULL, NULL, 'WS-FLT-001-36', NULL, 4, 1),
(19, '37', NULL, NULL, 'WS-FLT-001-37', NULL, 5, 1),
(19, '38', NULL, NULL, 'WS-FLT-001-38', NULL, 5, 1),
(19, '39', NULL, NULL, 'WS-FLT-001-39', NULL, 4, 1),
(19, '40', NULL, NULL, 'WS-FLT-001-40', NULL, 2, 1),
-- heels #20 (shoe size)
(20, '36', NULL, NULL, 'WS-HLS-001-36', NULL, 3, 1),
(20, '37', NULL, NULL, 'WS-HLS-001-37', NULL, 4, 1),
(20, '38', NULL, NULL, 'WS-HLS-001-38', NULL, 4, 1),
(20, '39', NULL, NULL, 'WS-HLS-001-39', NULL, 3, 1),
(20, '40', NULL, NULL, 'WS-HLS-001-40', NULL, 3, 1);

-- ----------------------------------------------------------------------------
-- Order statuses (configurable — admin can add more via admin panel)
-- ----------------------------------------------------------------------------
INSERT INTO order_statuses (id, name, slug, color, sort_order, is_default) VALUES
(1, 'Pending',    'pending',    '#9A9284', 1, 1),
(2, 'Confirmed',  'confirmed',  '#3A5A8C', 2, 0),
(3, 'Processing', 'processing', '#8C6A3A', 3, 0),
(4, 'Packed',     'packed',     '#6A6A6A', 4, 0),
(5, 'Shipped',    'shipped',    '#2E7D6B', 5, 0),
(6, 'Delivered',  'delivered',  '#1E7A34', 6, 0),
(7, 'Cancelled',  'cancelled',  '#A32E2E', 7, 0),
(8, 'Returned',   'returned',   '#7A3E9D', 8, 0);

-- ----------------------------------------------------------------------------
-- Payment methods (admin-configurable — account numbers are placeholders)
-- ----------------------------------------------------------------------------
INSERT INTO payment_methods (id, code, name, account_number, instructions, requires_reference, is_active, sort_order) VALUES
(1, 'cod',   'Cash on Delivery', NULL,           'Pay in cash when your order is delivered to your door.', 0, 1, 1),
(2, 'bkash', 'bKash (Manual)',   '01XXXXXXXXX',  'Send Money to the number above, then enter the bKash Transaction ID at checkout. Replace this number in Admin → Settings → Payment.', 1, 1, 2),
(3, 'nagad', 'Nagad (Manual)',   '01XXXXXXXXX',  'Send Money to the number above, then enter the Nagad Transaction ID at checkout. Replace this number in Admin → Settings → Payment.', 1, 1, 3);

-- ----------------------------------------------------------------------------
-- Delivery zones
-- ----------------------------------------------------------------------------
INSERT INTO delivery_zones (id, name, charge, is_default, sort_order, is_active) VALUES
(1, 'Inside Dhaka',  70.00,  1, 1, 1),
(2, 'Outside Dhaka', 130.00, 0, 2, 1);

-- ----------------------------------------------------------------------------
-- Store settings (all editable via Admin → Settings)
-- ----------------------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
('store_name',            'Atelier'),
('store_tagline',         'Modern essentials, quietly considered.'),
('store_logo',            ''),
('store_favicon',         ''),
('store_phone',           '+880 1XXXXXXXXX'),
('store_email',           'hello@example.com'),
('store_address',         'Gulshan, Dhaka, Bangladesh'),
('facebook_url',          'https://www.facebook.com/profile.php?id=100044816760076'),
('instagram_url',         ''),
('currency_symbol',       '৳'),
('homepage_hero_heading', 'The New Season Edit'),
('homepage_hero_subtext', 'Considered pieces for the woman who dresses on her own terms.'),
('homepage_hero_cta_text','Shop New Arrivals'),
('homepage_hero_cta_link','/shop?sort=newest'),
('promo_heading',         'Free delivery inside Dhaka on orders over ৳3,000'),
('about_heading',         'Our Story'),
('about_body',            'This section is fully editable from Admin → Settings. Replace it with your brand''s real story, values and craftsmanship notes.'),
('footer_text',           '© 2026 Atelier. All rights reserved.'),
('trust_delivery_text',   'Nationwide delivery, 2–5 business days.'),
('trust_return_text',     'Easy exchanges within 3 days of delivery.'),
('trust_payment_text',    'Cash on delivery, bKash & Nagad accepted.'),
('meta_default_title',    'Atelier — Women''s Fashion, Dhaka'),
('meta_default_description', 'Sarees, three-piece sets, kurtis, dresses, abayas and accessories — considered women''s fashion, delivered across Bangladesh.');
