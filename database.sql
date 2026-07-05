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

-- ============================================================
-- Insert Default Categories
-- ============================================================
INSERT INTO categories (name, slug, icon, color) VALUES
('Resorts',              'resorts',  'fas fa-umbrella-beach', '#2d6a4f'),
('Barangays',            'barangays','fas fa-home',           '#52b788'),
('Cultural Sites',       'cultural', 'fas fa-landmark',       '#b7791f'),
('Food & Restaurants',   'food',     'fas fa-utensils',       '#e63946'),
('Agri-Tourism & Farms', 'farms',    'fas fa-seedling',       '#40916c'),
('Nature & Adventure',   'nature',   'fas fa-mountain',       '#1b4332');

-- ============================================================
-- Insert Default Admin (password: Admin@123)
-- ============================================================
INSERT INTO admins (username, email, password, full_name, role) VALUES
('admin', 'admin@sanenrique.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LGU Administrator', 'superadmin');

-- ============================================================
-- Sample Listings (original)
-- ============================================================
INSERT INTO listings (category_id, name, slug, description, address, barangay, latitude, longitude, operating_hours, entrance_fee, is_featured, status) VALUES
(1, 'Paradise Cove Resort',      'paradise-cove-resort',    'A breathtaking beachfront resort nestled along the pristine shores of San Enrique. Enjoy crystal-clear waters, white sandy beaches, and world-class amenities perfect for families and couples.',               'Barangay Camangahan, San Enrique', 'Camangahan', 10.9234, 122.8901, '6:00 AM - 10:00 PM', '₱150 per person', 1, 'active'),
(1, 'Green Hills Farm Resort',   'green-hills-farm-resort', 'Experience the serenity of nature at Green Hills Farm Resort. A perfect blend of agri-tourism and recreation surrounded by lush green hills and fresh mountain air.',                                      'Barangay Poblacion, San Enrique',  'Poblacion',  10.9156, 122.8823, '7:00 AM - 9:00 PM',  '₱100 per person', 1, 'active'),
(3, 'San Enrique Heritage Church','heritage-church',         'The historic Saint Enrique Parish Church, a centuries-old architectural marvel that stands as a testament to the rich cultural heritage of the municipality.',                                                'Poblacion, San Enrique',           'Poblacion',  10.9178, 122.8845, 'Open Daily',         'Free',            1, 'active'),
(4, 'Lutong Bisaya Kitchen',     'lutong-bisaya-kitchen',   'Authentic Visayan cuisine using fresh locally-sourced ingredients. Experience the true taste of San Enrique through traditional recipes passed down through generations.',                                          'National Highway, San Enrique',    'Poblacion',  10.9167, 122.8831, '7:00 AM - 8:00 PM',  'N/A',             0, 'active'),
(5, 'Verde Organic Farm',        'verde-organic-farm',      'Visit our thriving organic farm and learn sustainable farming practices. Harvest fresh vegetables, experience farm life, and take home organic produce.',                                                             'Barangay Agcalaga, San Enrique',   'Agcalaga',   10.9289, 122.8956, '8:00 AM - 5:00 PM',  '₱80 per person',  1, 'active'),
(2, 'Barangay Punta Verde',      'barangay-punta-verde',    'A vibrant coastal barangay known for its fishing community, colorful banca boats, and spectacular sunset views over the Guimaras Strait.',                                                                         'Punta Verde, San Enrique',         'Punta Verde', 10.9112, 122.8789, 'Open 24 hours',      'Free',            0, 'active');

-- ============================================================
-- All 28 Barangays of San Enrique, Iloilo
-- ============================================================
INSERT INTO listings (category_id, name, slug, description, address, barangay, website, operating_hours, entrance_fee, status) VALUES
(2, 'Barangay Abaca',            'barangay-abaca',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Abaca, San Enrique, Iloilo',            'Abaca',            'https://www.facebook.com/profile.php?id=61553757301737',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Asisig',           'barangay-asisig',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Asisig, San Enrique, Iloilo',           'Asisig',           'https://www.facebook.com/groups/223302368894780',                                  'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Bantayan',         'barangay-bantayan',         'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Bantayan, San Enrique, Iloilo',         'Bantayan',         '',                                                                                 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Braulan',          'barangay-braulan',          'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Braulan, San Enrique, Iloilo',          'Braulan',          'https://www.facebook.com/profile.php?id=61553682964106',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Cabugao Nuevo',    'barangay-cabugao-nuevo',    'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Cabugao Nuevo, San Enrique, Iloilo',    'Cabugao Nuevo',    '',                                                                                 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Cabugao Viejo',    'barangay-cabugao-viejo',    'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Cabugao Viejo, San Enrique, Iloilo',    'Cabugao Viejo',    'https://www.facebook.com/groups/213430483312531',                                  'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Camiri',           'barangay-camiri',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay''s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community''s strong spiritual connection and respect for their history.',  'Barangay Camiri, San Enrique, Iloilo',           'Camiri',           'https://www.facebook.com/profile.php?id=61565832921860',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Compo',            'barangay-compo',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Compo, San Enrique, Iloilo',            'Compo',            'https://www.facebook.com/profile.php?id=100066410029706',                          'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Catan-Agan',       'barangay-catan-agan',       'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Catan-Agan, San Enrique, Iloilo',       'Catan-Agan',       'https://www.facebook.com/profile.php?id=100063789806327',                          'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Cubay',            'barangay-cubay',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.',           'Barangay Cubay, San Enrique, Iloilo',            'Cubay',            'https://www.facebook.com/profile.php?id=61556987704834',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Dacal',            'barangay-dacal',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Dacal, San Enrique, Iloilo',            'Dacal',            'https://www.facebook.com/profile.php?id=61556520538650',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Dumiles',          'barangay-dumiles',          'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay''s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.',                                      'Barangay Dumiles, San Enrique, Iloilo',          'Dumiles',          'https://www.facebook.com/profile.php?id=100078247682899',                          'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Garita',           'barangay-garita',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Garita, San Enrique, Iloilo',           'Garita',           'https://www.facebook.com/profile.php?id=61558183211226',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Gines Nuevo',      'barangay-gines-nuevo',      'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Gines Nuevo, San Enrique, Iloilo',      'Gines Nuevo',      'https://www.facebook.com/profile.php?id=100078221154180',                          'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Imbang Pequeño',   'barangay-imbang-pequeno',   'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Imbang Pequeño, San Enrique, Iloilo',   'Imbang Pequeño',   'https://www.facebook.com/profile.php?id=61553163435790',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Imbesad-an',       'barangay-imbesad-an',       'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Imbesad-an, San Enrique, Iloilo',       'Imbesad-an',       'https://www.facebook.com/profile.php?id=61553557508530',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Iprog',            'barangay-iprog',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Iprog, San Enrique, Iloilo',            'Iprog',            'https://www.facebook.com/profile.php?id=61553154336493',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Lip-ac',           'barangay-lip-ac',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Lip-ac, San Enrique, Iloilo',           'Lip-ac',           'https://www.facebook.com/profile.php?id=61553035064866',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Madarag',          'barangay-madarag',          'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Madarag, San Enrique, Iloilo',          'Madarag',          'https://www.facebook.com/kasimaryo.sang.madarag',                                  'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Mapili',           'barangay-mapili',           'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Mapili, San Enrique, Iloilo',           'Mapili',           'https://www.facebook.com/profile.php?id=61553671329335',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Paga',             'barangay-paga',             'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Paga, San Enrique, Iloilo',             'Paga',             'https://www.facebook.com/profile.php?id=61583143784931',                           'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Palje',            'barangay-palje',            'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Palje, San Enrique, Iloilo',            'Palje',            '',                                                                                 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Poblacion Ilawod', 'barangay-poblacion-ilawod', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Poblacion Ilawod, San Enrique, Iloilo', 'Poblacion Ilawod', 'https://www.facebook.com/profile.php?id=100086784212137',                          'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Poblacion Ilaya',  'barangay-poblacion-ilaya',  'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Poblacion Ilaya, San Enrique, Iloilo',  'Poblacion Ilaya',  'https://www.facebook.com/SKPoblacionIlaya',                                         'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Quinolpan',        'barangay-quinolpan',        'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo in Barangay Quinolpan stands as a peaceful landmark that reflects the barangay''s deep spiritual roots and close-knit community. Modest yet meaningful, it serves as a place where locals gather for quiet prayer and reflection. Surrounded by open fields and fresh air, the simboryo mirrors the simplicity and strong faith of the people, preserving their traditions through time.', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', 'https://www.facebook.com/barangayquinolpansanenriqueiloilophilippines5036',         'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Rumagayray',       'barangay-rumagayray',       'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. In Barangay Rumagayray, the simboryo holds a special place in the hearts of its people. Known for its weathered beauty, it represents not only faith but also the strength and unity of the community. It serves as a gathering point for reflection and remembrance, carrying with it the stories and prayers of generations.',                                          'Barangay Rumagayray, San Enrique, Iloilo',       'Rumagayray',       'https://www.facebook.com/groups/1556000491533439',                                 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay San Antonio',      'barangay-san-antonio',      'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay San Antonio, San Enrique, Iloilo',      'San Antonio',      '',                                                                                 'Open 24 hours', 'Free', 'active'),
(2, 'Barangay Tambunac',         'barangay-tambunac',         'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.',                                                         'Barangay Tambunac, San Enrique, Iloilo',         'Tambunac',         'https://www.facebook.com/barangay.tambunac',                                        'Open 24 hours', 'Free', 'active');

-- ============================================================
-- Locations from Description (Farms, Resorts, Rivers)
-- category 5 = Agri-Tourism & Farms | 1 = Resorts | 6 = Nature & Adventure
-- ============================================================
INSERT INTO listings (category_id, name, slug, description, address, barangay, contact, email, website, operating_hours, entrance_fee, amenities, is_featured, status) VALUES

(5,
 'Gumbans Amat Amat Farm',
 'gumbans-amat-amat-farm',
 'Gumbans Amat Amat Farm is a peaceful farm offering fresh produce and scenic views. Enjoy their fruit trees, fish pond, and vegetable garden. Enjoyable views include fruit trees (peach, lemon, avocado, coconut), fish pond, and vegetable garden.',
 'Sitio Gelonoc Cubay, San Enrique, Iloilo',
 'Cubay',
 '09627603707 / 09455067648',
 '',
 'https://www.facebook.com/share1DHRHN9kbMn/?mibextid=wwXlfr',
 'Monday to Friday: 8:00 AM - 5:00 PM; Saturday and Sunday: 9:00 AM - 5:00 PM',
 'Contact for rates',
 'Fruit trees (peach, lemon, avocado, coconut), Fish pond, Vegetable garden',
 1,
 'active'),

(6,
 'Cabas-an Cold Spring',
 'cabasan-cold-spring',
 'Cabasan Cold Spring is a natural spring offering cool, clear waters perfect for swimming and relaxation. Enjoyable views include lush greenery, natural rock formations, and scenic surroundings. Activities: swimming, picnic, and nature walks.',
 'Sitio Cabas-an, Brgy. Compo, San Enrique, Iloilo 5036',
 'Compo',
 '0917 302 3878',
 'ebtconfi2@gmail.com',
 'https://facebook.com/composanenriqueiloilo',
 '7:00 AM - 5:00 PM, Daily',
 'Contact for rates',
 'Natural spring pool, Swimming area, Picnic area, Nature walks, Rock formations',
 1,
 'active'),

(5,
 'BC Farm',
 'bc-farm',
 'BC Farm is a peaceful farm in San Enrique, Iloilo, offering a serene atmosphere for swimming and relaxation.',
 'Brgy Cabugao Viejo, San Enrique, Iloilo 5036',
 'Cabugao Viejo',
 '0985 181 9131',
 'benedictopadon@gmail.com',
 'https://facebook.com/BCResortSanEnrique',
 'Daily (open 24/7)',
 'Contact for rates',
 'Swimming area, Farm atmosphere, Relaxation area',
 0,
 'active'),

(1,
 'Tony''s Valley Farm Resort',
 'tonys-valley-farm-resort',
 'Tony''s Valley Farm Resort is a private resort offering a peaceful atmosphere with swimming pools and cottages. Enjoyable views include lush greenery, swimming pools, and scenic surroundings. Activities: swimming, picnic, and nature walks.',
 'Sitio Layog Bato, Brgy. Mapili, San Enrique, Iloilo',
 'Mapili',
 '0908 866 6909',
 '',
 'https://facebook.com/TVFRM',
 'Daily (contact for specific hours)',
 'Contact for rates',
 'Swimming pools, Cottages, Picnic area, Nature walks, Lush greenery',
 1,
 'active'),

(6,
 'San Antonio San Enrique River',
 'san-antonio-river',
 'The San Antonio San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.',
 'San Antonio, San Enrique, Iloilo',
 'San Antonio',
 '',
 '',
 '',
 '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM',
 'Free',
 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views',
 0,
 'active'),

(6,
 'Catan-Agan San Enrique River',
 'catan-agan-river',
 'The Catan-Agan San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.',
 'Catan-Agan, San Enrique, Iloilo',
 'Catan-Agan',
 '',
 '',
 '',
 '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM',
 'Free',
 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views',
 0,
 'active');

-- ============================================================
-- Native Delicacies from Description (category 4 = Food & Restaurants)
-- ============================================================
INSERT INTO listings (category_id, name, slug, description, address, barangay, operating_hours, entrance_fee, is_featured, status) VALUES

(4,
 'Puto (Native Delicacy)',
 'puto-native-delicacy',
 'A famous Filipino steamed rice cake. In San Enrique, Puto is traditionally sold by vendors outside the church or inside the market, especially after Sunday Mass. Best prepared and paired with Dinuguan.',
 'Local Markets & Church Vicinity, San Enrique, Iloilo',
 'Poblacion',
 'Sundays (after Mass) and daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Kutsinta (Native Delicacy)',
 'kutsinta-native-delicacy',
 'A popular steamed rice cake in San Enrique, Iloilo, often featured alongside the town''s famous kalamay as a staple "pang yam-is" (sweet) merienda. These sticky, chewy, and brown or black treats are typically served with freshly grated coconut. Made from all-purpose flour or tapioca starch, brown sugar, lye water, and annatto water, steamed for 30-45 minutes.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Alupe / Suman Balanghoy (Native Delicacy)',
 'alupe-suman-balanghoy',
 'A traditional Ilonggo delicacy popular in San Enrique, Iloilo. Also known as Alupi or Suman Balanghoy, it is a sweet, steamed, and chewy cassava-based suman wrapped in banana leaves, often featuring shredded young coconut (buko) and coconut milk. Best enjoyed with bukayo (sweetened coconut strips) in the center for a gooey, extra-sweet surprise. A staple merienda often present during local fiestas and special occasions.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Inday-Inday (Native Delicacy)',
 'inday-inday-native-delicacy',
 'A traditional Ilonggo native delicacy (kakanin) popular in San Enrique, Iloilo, and other parts of Western Visayas. A sweet, chewy snack made from poached glutinous rice dough discs, very similar to palitaw, but distinguished by its rich, caramelized coconut topping known as bukayo.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Kalamay Hati (Native Delicacy)',
 'kalamay-hati-native-delicacy',
 'A traditional Filipino sweet rice cake and a celebrated product of San Enrique, Iloilo, featured during their annual town festival. A very sticky, thick, and chewy delicacy made from ground glutinous rice, coconut milk, and muscovado or brown sugar, commonly topped with latik (coconut milk curds).',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 1,
 'active'),

(4,
 'Minatamis na Saging (Native Delicacy)',
 'minatamis-na-saging',
 'A staple Filipino dessert and merienda popular throughout Iloilo, including San Enrique, often served as a simple treat, a topping for shaved ice (Saba con Yelo), or an ingredient in halo-halo. Consists of ripe Saba bananas simmered until tender in a thickened, caramelized brown sugar syrup, sometimes enhanced with pandan leaves, vanilla, or salt to balance the sweetness.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Suman (Native Delicacy)',
 'suman-native-delicacy',
 'A popular native delicacy (kakanin) in San Enrique, Iloilo, known for its deep brown color, sticky texture, and rich flavor. A traditional Ilonggo suman typically wrapped in banana leaves and may come topped with latik (coconut caramel) or served plain, often prepared by local makers, including seniors who have learned the traditional process.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Bibingka (Native Delicacy)',
 'bibingka-native-delicacy',
 'A traditional, soft, and slightly sweet rice cake from San Enrique, Iloilo, cooked over live charcoal in banana leaf-lined molds. Known for its aromatic aroma, this native delicacy is typically made from rice flour (or soaked glutinous rice), coconut milk, sugar, eggs, and topped with cheese, salted eggs, or shredded coconut.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Baye-Baye (Native Delicacy)',
 'baye-baye-native-delicacy',
 'A popular native delicacy in the Visayas region, particularly in Iloilo. A well-loved Ilonggo treat found in various parts of the region, including areas like San Enrique.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Muasi (Native Delicacy)',
 'muasi-native-delicacy',
 'A popular, traditional Ilonggo delicacy often found in San Enrique and throughout Iloilo. Recognized as an Ilonggo version of palitaw, consisting of soft, chewy dumplings made from glutinous rice, commonly topped or stuffed with toasted sesame seeds and sugar.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active'),

(4,
 'Ibos / Dali-Dalin (Native Delicacy)',
 'ibos-dali-dalin-native-delicacy',
 'A type of sticky rice (glutinous rice) wrapped in coconut or buri leaves, popularly called Ibos. Best enjoyed dipped in sugar.',
 'Local Markets, San Enrique, Iloilo',
 'Poblacion',
 'Daily in local markets',
 'Market price',
 0,
 'active');

-- ============================================================
-- Simboryo Cultural Sites (category 3 = Cultural Sites)
-- ============================================================
INSERT INTO listings (category_id, name, slug, description, address, barangay, operating_hours, entrance_fee, is_featured, status) VALUES

(3,
 'Simboryo ng Barangay Quinolpan',
 'simboryo-quinolpan',
 'The simboryo in Barangay Quinolpan stands as a peaceful landmark that reflects the barangay''s deep spiritual roots and close-knit community. Modest yet meaningful, it serves as a place where locals gather for quiet prayer and reflection. Surrounded by open fields and fresh air, the simboryo mirrors the simplicity and strong faith of the people, preserving their traditions through time.',
 'Barangay Quinolpan, San Enrique, Iloilo',
 'Quinolpan',
 'Open daily',
 'Free',
 0,
 'active'),

(3,
 'Simboryo ng Barangay Cubay',
 'simboryo-cubay',
 'In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.',
 'Barangay Cubay, San Enrique, Iloilo',
 'Cubay',
 'Open daily',
 'Free',
 0,
 'active'),

(3,
 'Simboryo ng Barangay Camiri',
 'simboryo-camiri',
 'The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay''s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community''s strong spiritual connection and respect for their history.',
 'Barangay Camiri, San Enrique, Iloilo',
 'Camiri',
 'Open daily',
 'Free',
 0,
 'active'),

(3,
 'Simboryo ng Barangay Rumagayray',
 'simboryo-rumagayray',
 'In Barangay Rumagayray, the simboryo holds a special place in the hearts of its people. Known for its weathered beauty, it represents not only faith but also the strength and unity of the community. It serves as a gathering point for reflection and remembrance, carrying with it the stories and prayers of generations.',
 'Barangay Rumagayray, San Enrique, Iloilo',
 'Rumagayray',
 'Open daily',
 'Free',
 0,
 'active'),

(3,
 'Simboryo ng Barangay Dumiles',
 'simboryo-dumiles',
 'The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay''s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.',
 'Barangay Dumiles, San Enrique, Iloilo',
 'Dumiles',
 'Open daily',
 'Free',
 0,
 'active');

-- ============================================================
-- Sample Events (original)
-- ============================================================
INSERT INTO events (title, description, event_date, end_date, location, latitude, longitude, status) VALUES
('San Enrique Fiesta Festival', 'Annual town fiesta celebrating the feast of San Enrique with cultural shows, parades, and traditional food fair. Join thousands of locals and visitors in this grand celebration!',          '2025-07-15', '2025-07-17', 'Poblacion Plaza, San Enrique',           10.9178, 122.8845, 'active'),
('Harvest Festival 2025',       'Celebrate the abundance of San Enrique with a colorful harvest festival featuring agricultural exhibits, cooking competitions, and agri-tourism tours.',                                 '2025-10-20', '2025-10-21', 'Verde Organic Farm, San Enrique',        10.9289, 122.8956, 'active'),
('Beach Clean-Up & Fun Day',    'Community beach clean-up activity followed by beach games, water sports, and environmental awareness programs at Paradise Cove Resort.',                                               '2025-08-05', '2025-08-05', 'Paradise Cove Resort, San Enrique',      10.9234, 122.8901, 'active');


ALTER TABLE listings
ADD COLUMN distance_from_town VARCHAR(100) DEFAULT NULL AFTER amenities,
ADD COLUMN population VARCHAR(100) DEFAULT NULL AFTER distance_from_town;