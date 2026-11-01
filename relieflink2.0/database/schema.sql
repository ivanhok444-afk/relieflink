-- ReliefLink Database Schema
-- MySQL Database

CREATE DATABASE IF NOT EXISTS relief_goods_db;
USE relief_goods_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('admin', 'user', 'staff') NOT NULL DEFAULT 'user',
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    location VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Households Table
CREATE TABLE IF NOT EXISTS households (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    household_name VARCHAR(150) NOT NULL,
    household_id VARCHAR(50) UNIQUE NOT NULL,
    qr_code VARCHAR(255),
    street_address VARCHAR(255),
    barangay VARCHAR(100),
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(10),
    contact_number VARCHAR(20),
    total_members INT DEFAULT 0,
    monthly_income DECIMAL(10, 2),
    is_eligible BOOLEAN DEFAULT TRUE,
    eligibility_reason TEXT,
    last_verified TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Family Members Table
CREATE TABLE IF NOT EXISTS family_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    household_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    age INT,
    gender ENUM('Male', 'Female'),
    relationship VARCHAR(50),
    date_of_birth DATE,
    is_senior BOOLEAN DEFAULT FALSE,
    is_pwd BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE
);

-- Relief Goods Categories Table
CREATE TABLE IF NOT EXISTS relief_categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    category_icon VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Relief Goods Table
CREATE TABLE IF NOT EXISTS relief_goods (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    goods_name VARCHAR(150) NOT NULL,
    description TEXT,
    unit_of_measure VARCHAR(50),
    quantity_in_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    expiration_date DATE,
    source VARCHAR(150),
    unit_cost DECIMAL(10, 2),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES relief_categories(id)
);

-- Stock Movements Table
CREATE TABLE IF NOT EXISTS stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    relief_goods_id INT NOT NULL,
    movement_type ENUM('Stock In', 'Stock Out', 'Distributed', 'Expired') NOT NULL,
    quantity INT NOT NULL,
    reference_no VARCHAR(100),
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (relief_goods_id) REFERENCES relief_goods(id),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Relief Requests Table
CREATE TABLE IF NOT EXISTS relief_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    household_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Approved', 'Rejected', 'Distributed', 'Cancelled') DEFAULT 'Pending',
    request_reason TEXT,
    notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (household_id) REFERENCES households(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Request Items Table
CREATE TABLE IF NOT EXISTS request_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    relief_goods_id INT NOT NULL,
    quantity_requested INT NOT NULL,
    quantity_approved INT DEFAULT 0,
    quantity_distributed INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES relief_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (relief_goods_id) REFERENCES relief_goods(id)
);

-- Distribution Batches Table
CREATE TABLE IF NOT EXISTS distribution_batches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    batch_code VARCHAR(100) UNIQUE NOT NULL,
    batch_date DATE NOT NULL,
    description TEXT,
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Distributions Table
CREATE TABLE IF NOT EXISTS distributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    batch_id INT NOT NULL,
    household_id INT NOT NULL,
    distribution_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    distributed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (batch_id) REFERENCES distribution_batches(id),
    FOREIGN KEY (household_id) REFERENCES households(id),
    FOREIGN KEY (distributed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Distribution Details Table
CREATE TABLE IF NOT EXISTS distribution_details (
    id INT PRIMARY KEY AUTO_INCREMENT,
    distribution_id INT NOT NULL,
    relief_goods_id INT NOT NULL,
    quantity_distributed INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (distribution_id) REFERENCES distributions(id) ON DELETE CASCADE,
    FOREIGN KEY (relief_goods_id) REFERENCES relief_goods(id)
);

-- Activity Logs Table
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    module VARCHAR(100),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200),
    message TEXT,
    type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Business Chat
CREATE TABLE IF NOT EXISTS chat_conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    INDEX idx_chat_messages_conversation (conversation_id, id),
    INDEX idx_chat_messages_unread (conversation_id, sender_id, read_at),
    FOREIGN KEY (conversation_id) REFERENCES chat_conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Provision the first administrator with: php database/create_admin.php
-- Disable the known default account on existing installations.
UPDATE users
SET is_active = FALSE
WHERE username = 'admin' AND password = SHA2('admin123', 256);

-- Insert Default Categories
INSERT INTO relief_categories (category_name, description) VALUES
('Food Items', 'Rice, noodles, canned goods, etc.'),
('Hygiene Kits', 'Soap, toothpaste, sanitizers, etc.'),
('Clothing', 'T-shirts, pants, jackets, etc.'),
('Medical Supplies', 'First aid kits, medicines, etc.'),
('Household Items', 'Blankets, mats, utensils, etc.'),
('School Supplies', 'Notebooks, pens, educational materials');

-- Insert Default System Settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('system_name', 'ReliefLink', 'Name of the system'),
('system_email', 'info@reliefgoods.com', 'System contact email'),
('max_goods_per_household', '5', 'Maximum items per distribution'),
('request_approval_required', 'true', 'Require admin approval for requests');
