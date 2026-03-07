-- Create Database
CREATE DATABASE IF NOT EXISTS oml_para;
USE oml_para;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    slug VARCHAR(100) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0,
    is_available BOOLEAN DEFAULT 1,
    is_disabled BOOLEAN DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100),
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    whatsapp_message_sent BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Admin Users Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
-- IMPORTANT: the password field must be stored as a hash. The login() function in
-- auth.php now handles legacy plaintext passwords and will migrate them to a
-- hashed value on first successful login.
-- You can pre-hash the password using PHP: `echo password_hash('admin123', PASSWORD_DEFAULT);`
-- and then replace the value below with that hash.
INSERT INTO admin_users (username, email, password) 
VALUES ('admin', 'admin@omlpara.com', 'admin123'); -- <replace with hashed password in production


-- Sample Categories
INSERT INTO categories (name, description, slug) VALUES
('Skincare', 'Premium skincare products', 'skincare'),
('Supplements', 'Vitamins and nutritional supplements', 'supplements'),
('Natural', 'Natural and organic products', 'natural'),
('First Aid', 'First aid and medical supplies', 'firstaid'),
('Beauty', 'Beauty and cosmetic products', 'beauty');

-- Sample Products
INSERT INTO products (category_id, name, description, price, stock_quantity, image_url) VALUES
(1, 'Anti-Aging Cream', 'Reduce wrinkles and fine lines', 29.99, 50, 'https://via.placeholder.com/300x300?text=Anti-Aging'),
(1, 'Vitamin C Serum', 'Brighten and protect your skin', 34.99, 75, 'https://via.placeholder.com/300x300?text=Vitamin+C'),
(2, 'Multivitamin Daily', 'Daily essential vitamins', 19.99, 100, 'https://via.placeholder.com/300x300?text=Multivitamin'),
(2, 'Omega-3 Supplement', 'Heart and brain health support', 24.99, 60, 'https://via.placeholder.com/300x300?text=Omega-3'),
(3, 'Organic Honey', 'Pure organic honey', 12.99, 40, 'https://via.placeholder.com/300x300?text=Honey'),
(4, 'First Aid Kit', 'Complete first aid supplies', 49.99, 25, 'https://via.placeholder.com/300x300?text=First+Aid');
