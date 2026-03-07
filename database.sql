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
    video VARCHAR(512) DEFAULT '',
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


-- All 28 Barangays of San Enrique, Iloilo
INSERT INTO listings (category_id, name, slug, description, address, barangay, website, operating_hours, entrance_fee, status) VALUES
(2, 'Barangay Abaca',            'barangay-abaca',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Abaca, San Enrique, Iloilo',            'Abaca',            'https://www.facebook.com/profile.php?id=61553757301737', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Asisig',           'barangay-asisig',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Asisig, San Enrique, Iloilo',           'Asisig',           'https://www.facebook.com/groups/223302368894780',        'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Bantayan',         'barangay-bantayan',         'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Bantayan, San Enrique, Iloilo',         'Bantayan',         '',                                                      'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Braulan',          'barangay-braulan',          'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Braulan, San Enrique, Iloilo',          'Braulan',          'https://www.facebook.com/profile.php?id=61553682964106', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Cabugao Nuevo',    'barangay-cabugao-nuevo',    'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Nuevo, San Enrique, Iloilo',    'Cabugao Nuevo',    '',                                                      'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Cabugao Viejo',    'barangay-cabugao-viejo',    'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Viejo, San Enrique, Iloilo',    'Cabugao Viejo',    'https://www.facebook.com/groups/213430483312531',        'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Camiri',           'barangay-camiri',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Camiri, San Enrique, Iloilo',           'Camiri',           'https://www.facebook.com/profile.php?id=61565832921860', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Compo',            'barangay-compo',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Compo, San Enrique, Iloilo',            'Compo',            'https://www.facebook.com/profile.php?id=100066410029706','Open 24 hours', 'Free', 'active'),
(2, 'Barangay Catan-Agan',       'barangay-catan-agan',       'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Catan-Agan, San Enrique, Iloilo',       'Catan-Agan',       'https://www.facebook.com/profile.php?id=100063789806327','Open 24 hours', 'Free', 'active'),
(2, 'Barangay Cubay',            'barangay-cubay',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cubay, San Enrique, Iloilo',            'Cubay',            'https://www.facebook.com/profile.php?id=61556987704834', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Dacal',            'barangay-dacal',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Dacal, San Enrique, Iloilo',            'Dacal',            'https://www.facebook.com/profile.php?id=61556520538650', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Dumiles',          'barangay-dumiles',          'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Dumiles, San Enrique, Iloilo',          'Dumiles',          'https://www.facebook.com/profile.php?id=100078247682899','Open 24 hours', 'Free', 'active'),
(2, 'Barangay Garita',           'barangay-garita',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Garita, San Enrique, Iloilo',           'Garita',           'https://www.facebook.com/profile.php?id=61558183211226', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Gines Nuevo',      'barangay-gines-nuevo',      'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Gines Nuevo, San Enrique, Iloilo',      'Gines Nuevo',      'https://www.facebook.com/profile.php?id=100078221154180','Open 24 hours', 'Free', 'active'),
(2, 'Barangay Imbang Pequeño',   'barangay-imbang-pequeno',   'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbang Pequeño, San Enrique, Iloilo',   'Imbang Pequeño',   'https://www.facebook.com/profile.php?id=61553163435790', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Imbesad-an',       'barangay-imbesad-an',       'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbesad-an, San Enrique, Iloilo',       'Imbesad-an',       'https://www.facebook.com/profile.php?id=61553557508530', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Iprog',            'barangay-iprog',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Iprog, San Enrique, Iloilo',            'Iprog',            'https://www.facebook.com/profile.php?id=61553154336493', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Lip-ac',           'barangay-lip-ac',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Lip-ac, San Enrique, Iloilo',           'Lip-ac',           'https://www.facebook.com/profile.php?id=61553035064866', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Madarag',          'barangay-madarag',          'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Madarag, San Enrique, Iloilo',          'Madarag',          'https://www.facebook.com/kasimaryo.sang.madarag',        'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Mapili',           'barangay-mapili',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Mapili, San Enrique, Iloilo',           'Mapili',           'https://www.facebook.com/profile.php?id=61553671329335', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Paga',             'barangay-paga',             'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Paga, San Enrique, Iloilo',             'Paga',             'https://www.facebook.com/profile.php?id=61583143784931', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Palje',            'barangay-palje',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Palje, San Enrique, Iloilo',            'Palje',            '',                                                      'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Poblacion Ilawod', 'barangay-poblacion-ilawod', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilawod, San Enrique, Iloilo', 'Poblacion Ilawod', 'https://www.facebook.com/profile.php?id=100086784212137','Open 24 hours', 'Free', 'active'),
(2, 'Barangay Poblacion Ilaya',  'barangay-poblacion-ilaya',  'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilaya, San Enrique, Iloilo',  'Poblacion Ilaya',  'https://www.facebook.com/SKPoblacionIlaya',              'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Quinolpan',        'barangay-quinolpan',        'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Quinolpan, San Enrique, Iloilo',        'Quinolpan',        'https://www.facebook.com/barangayquinolpansanenriqueiloilophilippines5036', 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Rumagayray',       'barangay-rumagayray',       'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Rumagayray, San Enrique, Iloilo',       'Rumagayray',       'https://www.facebook.com/groups/1556000491533439',       'Open 24 hours', 'Free', 'active'),
(2, 'Barangay San Antonio',      'barangay-san-antonio',      'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay San Antonio, San Enrique, Iloilo',      'San Antonio',      '',                                                      'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Tambunac',         'barangay-tambunac',         'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Tambunac, San Enrique, Iloilo',         'Tambunac',         'https://www.facebook.com/barangay.tambunac',             'Open 24 hours', 'Free', 'active');


-- Insert Sample Events
INSERT INTO events (title, description, event_date, end_date, location, latitude, longitude, status) VALUES
('San Enrique Fiesta Festival', 'Annual town fiesta celebrating the feast of San Enrique with cultural shows, parades, and traditional food fair. Join thousands of locals and visitors in this grand celebration!', '2025-07-15', '2025-07-17', 'Poblacion Plaza, San Enrique', 10.9178, 122.8845, 'active'),
('Harvest Festival 2025', 'Celebrate the abundance of San Enrique with a colorful harvest festival featuring agricultural exhibits, cooking competitions, and agri-tourism tours.', '2025-10-20', '2025-10-21', 'Verde Organic Farm, San Enrique', 10.9289, 122.8956, 'active'),
('Beach Clean-Up & Fun Day', 'Community beach clean-up activity followed by beach games, water sports, and environmental awareness programs at Paradise Cove Resort.', '2025-08-05', '2025-08-05', 'Paradise Cove Resort, San Enrique', 10.9234, 122.8901, 'active');