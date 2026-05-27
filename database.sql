CREATE DATABASE IF NOT EXISTS ecommerce;
USE ecommerce;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255) DEFAULT 'no-image.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20),
    status ENUM('Pending','Processing','Shipped','Completed','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Sample categories
INSERT INTO categories (name) VALUES ('Electronics'), ('Clothing'), ('Books'), ('Home & Garden'), ('Sports');

-- Sample products
INSERT INTO products (category_id, name, description, price, stock) VALUES
(1, 'Wireless Headphones', 'Bluetooth headphones with noise cancellation and long battery life.', 1299.00, 50),
(1, 'USB-C Charger 65W', 'Fast charging universal laptop charger.', 499.00, 100),
(2, 'Cotton T-Shirt', 'Comfortable everyday cotton shirt. Multiple colors available.', 299.00, 150),
(2, 'Slim Jeans', 'Slim fit jeans with stretch fabric for all-day comfort.', 799.00, 80),
(3, 'Web Development Book', 'Complete guide to modern web development.', 450.00, 60),
(4, 'Indoor Plant Pot Set', 'Set of 3 ceramic pots for small plants.', 350.00, 75),
(5, 'Yoga Mat', 'Non-slip 6mm thick yoga mat with carry strap.', 699.00, 90);
