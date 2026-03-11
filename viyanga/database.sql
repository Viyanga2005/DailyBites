CREATE DATABASE IF NOT EXISTS dailybite_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dailybite_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(60) NOT NULL,
    price INT NOT NULL,
    badge VARCHAR(60) DEFAULT 'Popular',
    image_url VARCHAR(500) DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    total_amount INT NOT NULL,
    status ENUM('Pending', 'Preparing', 'Delivered', 'Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id VARCHAR(20) NOT NULL,
    qty INT NOT NULL,
    unit_price INT NOT NULL,
    line_total INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(160) NOT NULL,
    message VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admins (username, password)
SELECT 'admin', 'admin123'
WHERE NOT EXISTS (
    SELECT 1 FROM admins WHERE username = 'admin'
);

INSERT INTO products (id, name, category, price, badge, image_url)
SELECT * FROM (
    SELECT 'p1' AS id, 'Classic Cheese Pizza' AS name, 'Pizza' AS category, 1890 AS price, 'Best Seller' AS badge, '/viyanga/images/Classic Cheese Pizza.jpg' AS image_url
    UNION ALL SELECT 'p2', 'Pepperoni Pizza', 'Pizza', 2290, 'Hot Deal', '/viyanga/images/Pepperoni Pizza.jpg'
    UNION ALL SELECT 'p3', 'Chicken Burger', 'Burgers', 1290, 'New', '/viyanga/images/Chicken Burger.png'
    UNION ALL SELECT 'p4', 'Crispy Fries', 'Sides', 690, 'Crunchy', '/viyanga/images/Crispy Fries.jpg'
    UNION ALL SELECT 'p5', 'Chicken Kottu', 'Sri Lankan', 1590, 'Local Fav', '/viyanga/images/Chicken Kottu.jpg'
    UNION ALL SELECT 'p6', 'Seafood Fried Rice', 'Sri Lankan', 1790, 'Chef Pick', '/viyanga/images/Seafood Fried Rice.png'
    UNION ALL SELECT 'p7', 'Chocolate Brownie', 'Desserts', 890, 'Sweet', '/viyanga/images/chocolate-brownies.jpg'
    UNION ALL SELECT 'p8', 'Iced Lemon Tea', 'Drinks', 490, 'Fresh', '/viyanga/images/Iced Lemon Tea.png'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM products WHERE products.id = seed.id);

INSERT INTO announcements (title, message)
SELECT 'Welcome to DailyBite!', 'Order in seconds - checkout is demo (no payments).'
WHERE NOT EXISTS (SELECT 1 FROM announcements);
