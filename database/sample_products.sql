-- Insert sample products only (skip categories since they exist)
INSERT INTO products (
    seller_id, 
    category_id, 
    name, 
    slug, 
    description, 
    price, 
    stock_quantity, 
    image_urls, 
    status
) VALUES
-- Electronics Category (assuming category_id = 1)
(1, 1, 'Sony WH-1000XM4 Wireless Headphones', 'sony-wh-1000xm4', 
'Industry-leading noise canceling with Dual Noise Sensor technology
Up to 30-hour battery life with quick charging (10 min charge for 5 hours of playback)
Touch Sensor controls to pause/play/skip tracks, control volume, activate your voice assistant, and answer phone calls
Speak-to-chat technology automatically reduces volume during conversations', 
349.99, 50, 
'["uploads/products/sony-headphones-1.jpg", "uploads/products/sony-headphones-2.jpg"]', 
'active'),

(1, 1, 'Samsung 55" 4K Smart TV', 'samsung-55-4k-tv',
'Crystal clear 4K resolution
Smart TV features with built-in streaming apps
Voice control compatibility
Multiple HDMI ports for gaming and entertainment',
699.99, 25,
'["uploads/products/samsung-tv-1.jpg", "uploads/products/samsung-tv-2.jpg"]',
'active'),

-- Smartphones Category (assuming category_id = 2)
(1, 2, 'iPhone 14 Pro Max', 'iphone-14-pro-max',
'Latest iPhone with revolutionary features
48MP main camera
Always-On display
A16 Bionic chip
Dynamic Island interface',
1099.99, 30,
'["uploads/products/iphone-14-1.jpg", "uploads/products/iphone-14-2.jpg"]',
'active'),

(1, 2, 'Samsung Galaxy S23 Ultra', 'samsung-s23-ultra',
'200MP main camera
S Pen functionality
Snapdragon 8 Gen 2 processor
5000mAh battery
45W fast charging',
1199.99, 35,
'["uploads/products/samsung-s23-1.jpg", "uploads/products/samsung-s23-2.jpg"]',
'active'),

-- Laptops Category (assuming category_id = 3)
(1, 3, 'MacBook Pro 14" M2', 'macbook-pro-14-m2',
'Latest M2 Pro/Max chip
Up to 18 hours battery life
14-inch Liquid Retina XDR display
Multiple ports including HDMI and SD card reader',
1999.99, 20,
'["uploads/products/macbook-pro-1.jpg", "uploads/products/macbook-pro-2.jpg"]',
'active'),

(1, 3, 'Dell XPS 15', 'dell-xps-15',
'15.6" 4K OLED display
Intel Core i9 processor
NVIDIA RTX 3050 Ti
32GB RAM, 1TB SSD
Premium aluminum build',
2199.99, 15,
'["uploads/products/dell-xps-1.jpg", "uploads/products/dell-xps-2.jpg"]',
'active'),

-- Fashion Category (assuming category_id = 4)
(1, 4, 'Classic Leather Jacket', 'classic-leather-jacket',
'Genuine leather construction
Multiple pockets
Quilted lining
Available in black and brown
Sizes: S, M, L, XL',
199.99, 40,
'["uploads/products/leather-jacket-1.jpg", "uploads/products/leather-jacket-2.jpg"]',
'active'),

(1, 4, 'Designer Sunglasses', 'designer-sunglasses',
'UV protection
Polarized lenses
Premium metal frame
Includes carrying case
Multiple colors available',
149.99, 60,
'["uploads/products/sunglasses-1.jpg", "uploads/products/sunglasses-2.jpg"]',
'active'),

-- Home & Living Category (assuming category_id = 5)
(1, 5, 'Smart Home Security Camera', 'smart-security-camera',
'1080p HD video
Night vision
Two-way audio
Motion detection
Cloud storage option',
79.99, 100,
'["uploads/products/security-camera-1.jpg", "uploads/products/security-camera-2.jpg"]',
'active'),

(1, 5, 'Robot Vacuum Cleaner', 'robot-vacuum',
'Smart mapping technology
WiFi connectivity
Compatible with Alexa
Automatic charging
Multiple cleaning modes',
299.99, 45,
'["uploads/products/robot-vacuum-1.jpg", "uploads/products/robot-vacuum-2.jpg"]',
'active');

-- Insert some reviews (make sure the user_id exists)
INSERT INTO reviews (user_id, product_id, rating, comment, created_at)
SELECT 1, p.id, r.rating, r.comment, NOW()
FROM (
    SELECT 1 as product_num, 5 as rating, 'Excellent noise cancellation and sound quality!' as comment UNION ALL
    SELECT 2, 4, 'Great TV for the price, amazing picture quality' UNION ALL
    SELECT 3, 5, 'Best iPhone ever, the camera is incredible' UNION ALL
    SELECT 4, 4, 'The S Pen is very useful, great battery life' UNION ALL
    SELECT 5, 5, 'The M2 chip is blazing fast, great battery life'
) r
JOIN products p ON p.id = (
    SELECT id FROM products WHERE slug = (
        CASE r.product_num
            WHEN 1 THEN 'sony-wh-1000xm4'
            WHEN 2 THEN 'samsung-55-4k-tv'
            WHEN 3 THEN 'iphone-14-pro-max'
            WHEN 4 THEN 'samsung-s23-ultra'
            WHEN 5 THEN 'macbook-pro-14-m2'
        END
    )
);

-- Update featured products
UPDATE products 
SET is_featured = TRUE 
WHERE slug IN ('sony-wh-1000xm4', 'iphone-14-pro-max', 'macbook-pro-14-m2', 'classic-leather-jacket', 'smart-security-camera');

-- Add some discounted prices
UPDATE products 
SET discount_price = ROUND(price * 0.85, 2)
WHERE is_featured = TRUE;