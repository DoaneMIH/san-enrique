-- San Enrique Tourism Hub Database Schema
-- Run this SQL in your MySQL/MariaDB

CREATE DATABASE IF NOT EXISTS san_enrique_tourism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE san_enrique_tourism;

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'fas fa-map-marker-alt',
    color VARCHAR(20) DEFAULT '#2d6a4f',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Listings Table
CREATE TABLE IF NOT EXISTS listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    address TEXT,
    barangay VARCHAR(100),
    contact VARCHAR(100),
    email VARCHAR(150),
    website VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    featured_image VARCHAR(500),
    gallery TEXT,
    operating_hours VARCHAR(255),
    entrance_fee VARCHAR(100),
    amenities TEXT,
    status ENUM('active','inactive','pending') DEFAULT 'active',
    is_featured TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE,
    end_date DATE,
    location VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    image VARCHAR(500),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200),
    role ENUM('superadmin','admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    listing_id INT NOT NULL,
    reviewer_name VARCHAR(150),
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE
);

-- Contact Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150),
    email VARCHAR(150),
    subject VARCHAR(255),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Categories
INSERT INTO categories (name, slug, icon, color) VALUES
('Resorts', 'resorts', 'fas fa-umbrella-beach', '#2d6a4f'),
('Barangays', 'barangays', 'fas fa-home', '#52b788'),
('Cultural Sites', 'cultural', 'fas fa-landmark', '#b7791f'),
('Food & Restaurants', 'food', 'fas fa-utensils', '#e63946'),
('Agri-Tourism & Farms', 'farms', 'fas fa-seedling', '#40916c'),
('Nature & Adventure', 'nature', 'fas fa-mountain', '#1b4332');

-- Insert Default Admin (password: Admin@123)
INSERT INTO admins (username, email, password, full_name, role) VALUES
('admin', 'admin@sanenrique.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LGU Administrator', 'superadmin');

-- Insert Sample Listings
INSERT INTO listings (category_id, name, slug, description, address, barangay, latitude, longitude, operating_hours, entrance_fee, is_featured, status) VALUES
(1, 'Paradise Cove Resort', 'paradise-cove-resort', 'A breathtaking beachfront resort nestled along the pristine shores of San Enrique. Enjoy crystal-clear waters, white sandy beaches, and world-class amenities perfect for families and couples.', 'Barangay Camangahan, San Enrique', 'Camangahan', 10.9234, 122.8901, '6:00 AM - 10:00 PM', '₱150 per person', 1, 'active'),
(1, 'Green Hills Farm Resort', 'green-hills-farm-resort', 'Experience the serenity of nature at Green Hills Farm Resort. A perfect blend of agri-tourism and recreation surrounded by lush green hills and fresh mountain air.', 'Barangay Poblacion, San Enrique', 'Poblacion', 10.9156, 122.8823, '7:00 AM - 9:00 PM', '₱100 per person', 1, 'active'),
(3, 'San Enrique Heritage Church', 'heritage-church', 'The historic Saint Enrique Parish Church, a centuries-old architectural marvel that stands as a testament to the rich cultural heritage of the municipality.', 'Poblacion, San Enrique', 'Poblacion', 10.9178, 122.8845, 'Open Daily', 'Free', 1, 'active'),
(4, 'Lutong Bisaya Kitchen', 'lutong-bisaya-kitchen', 'Authentic Visayan cuisine using fresh locally-sourced ingredients. Experience the true taste of San Enrique through traditional recipes passed down through generations.', 'National Highway, San Enrique', 'Poblacion', 10.9167, 122.8831, '7:00 AM - 8:00 PM', 'N/A', 0, 'active'),
(5, 'Verde Organic Farm', 'verde-organic-farm', 'Visit our thriving organic farm and learn sustainable farming practices. Harvest fresh vegetables, experience farm life, and take home organic produce.', 'Barangay Agcalaga, San Enrique', 'Agcalaga', 10.9289, 122.8956, '8:00 AM - 5:00 PM', '₱80 per person', 1, 'active'),
(2, 'Barangay Punta Verde', 'barangay-punta-verde', 'A vibrant coastal barangay known for its fishing community, colorful banca boats, and spectacular sunset views over the Guimaras Strait.', 'Punta Verde, San Enrique', 'Punta Verde', 10.9112, 122.8789, 'Open 24 hours', 'Free', 0, 'active');

-- Insert Sample Events
INSERT INTO events (title, description, event_date, end_date, location, latitude, longitude, status) VALUES
('San Enrique Fiesta Festival', 'Annual town fiesta celebrating the feast of San Enrique with cultural shows, parades, and traditional food fair. Join thousands of locals and visitors in this grand celebration!', '2025-07-15', '2025-07-17', 'Poblacion Plaza, San Enrique', 10.9178, 122.8845, 'active'),
('Harvest Festival 2025', 'Celebrate the abundance of San Enrique with a colorful harvest festival featuring agricultural exhibits, cooking competitions, and agri-tourism tours.', '2025-10-20', '2025-10-21', 'Verde Organic Farm, San Enrique', 10.9289, 122.8956, 'active'),
('Beach Clean-Up & Fun Day', 'Community beach clean-up activity followed by beach games, water sports, and environmental awareness programs at Paradise Cove Resort.', '2025-08-05', '2025-08-05', 'Paradise Cove Resort, San Enrique', 10.9234, 122.8901, 'active');