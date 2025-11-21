
CREATE DATABASE IF NOT EXISTS clothing_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clothing_shop;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  sku VARCHAR(100) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  size VARCHAR(50),
  category VARCHAR(100),
  description TEXT,
  image_url VARCHAR(255),
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO products (name, sku, price, size, category, description, image_url)
VALUES
('Basic Tee', 'TEE-001', 9.99, 'M', 'Shirt', 'A comfy basic tee.', 'https://picsum.photos/seed/tee001/640/480'),
('Slim Jeans', 'JEAN-002', 29.90, '32', 'Pants', 'Slim-fit jeans for daily wear.', 'https://picsum.photos/seed/jean002/640/480')
ON DUPLICATE KEY UPDATE name=VALUES(name);
