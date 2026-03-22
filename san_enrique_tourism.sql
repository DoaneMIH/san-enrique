-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 04:43 PM
-- Server version: 8.0.44
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `san_enrique_tourism`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('superadmin','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@sanenrique.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'LGU Administrator', 'superadmin', '2026-03-22 14:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-map-marker-alt',
  `color` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '#2d6a4f',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `color`, `created_at`) VALUES
(1, 'Resorts', 'resorts', 'fas fa-umbrella-beach', '#2d6a4f', '2026-03-22 14:09:14'),
(2, 'Barangays', 'barangays', 'fas fa-home', '#52b788', '2026-03-22 14:09:14'),
(3, 'Cultural Sites', 'cultural', 'fas fa-landmark', '#b7791f', '2026-03-22 14:09:14'),
(4, 'Food & Restaurants', 'food', 'fas fa-utensils', '#e63946', '2026-03-22 14:09:14'),
(5, 'Agri-Tourism & Farms', 'farms', 'fas fa-seedling', '#40916c', '2026-03-22 14:09:14'),
(6, 'Nature & Adventure', 'nature', 'fas fa-mountain', '#1b4332', '2026-03-22 14:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `event_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `end_date`, `location`, `latitude`, `longitude`, `image`, `status`, `created_at`) VALUES
(1, 'San Enrique Fiesta Festival', 'Annual town fiesta celebrating the feast of San Enrique with cultural shows, parades, and traditional food fair. Join thousands of locals and visitors in this grand celebration!', '2025-07-15', '2025-07-17', 'Poblacion Plaza, San Enrique', 10.91780000, 122.88450000, NULL, 'active', '2026-03-22 14:09:14'),
(2, 'Harvest Festival 2025', 'Celebrate the abundance of San Enrique with a colorful harvest festival featuring agricultural exhibits, cooking competitions, and agri-tourism tours.', '2025-10-20', '2025-10-21', 'Verde Organic Farm, San Enrique', 10.92890000, 122.89560000, NULL, 'active', '2026-03-22 14:09:14'),
(3, 'Beach Clean-Up & Fun Day', 'Community beach clean-up activity followed by beach games, water sports, and environmental awareness programs at Paradise Cove Resort.', '2025-08-05', '2025-08-05', 'Paradise Cove Resort, San Enrique', 10.92340000, 122.89010000, NULL, 'active', '2026-03-22 14:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `address` text COLLATE utf8mb4_unicode_ci,
  `barangay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `featured_image` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` text COLLATE utf8mb4_unicode_ci,
  `video` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `operating_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrance_fee` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amenities` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `is_featured` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `rating` decimal(3,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `category_id`, `name`, `slug`, `description`, `address`, `barangay`, `contact`, `email`, `website`, `latitude`, `longitude`, `featured_image`, `gallery`, `video`, `operating_hours`, `entrance_fee`, `amenities`, `status`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 'Paradise Cove Resort', 'paradise-cove-resort', 'A breathtaking beachfront resort nestled along the pristine shores of San Enrique. Enjoy crystal-clear waters, white sandy beaches, and world-class amenities perfect for families and couples.', 'Barangay Camangahan, San Enrique', 'Camangahan', '', '', '', 10.92340000, 122.89010000, '', '[]', '', '6:00 AM - 10:00 PM', '₱150 per person', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:19:14'),
(2, 1, 'Green Hills Farm Resort', 'green-hills-farm-resort', 'Experience the serenity of nature at Green Hills Farm Resort. A perfect blend of agri-tourism and recreation surrounded by lush green hills and fresh mountain air.', 'Barangay Poblacion, San Enrique', 'Poblacion', '', '', '', 10.91560000, 122.88230000, '', '[]', '', '7:00 AM - 9:00 PM', '₱100 per person', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:19:22'),
(3, 3, 'San Enrique Heritage Church', 'san-enrique-heritage-church', 'The historic Saint Enrique Parish Church, a centuries-old architectural marvel that stands as a testament to the rich cultural heritage of the municipality.', 'Poblacion, San Enrique', 'Poblacion', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1lcRZlKw4-LNy7ok1lvallBeNKY-cBqUz', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1XF6irk8Dp6lTxtQW_enc8vUyjS9eT8k0\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1hjmlvRuBraXar_SYX2HQse2Nh3pNPx6y\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1f3M0Fyl2oyCAxhtu2wx_EOoIYymcBBYl\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1C-wjW7lyzva2CgtWcwGz6CSLzgLzbUpx\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1F-hqvnwifkGXwxQ5pYU5-nnMTJgEAFKa\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/10GBhPX_CWGvE5VPFESFY4z-6GISxxgA5\"]', '', 'Open Daily', 'Free', '', 'active', 1, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:11:30'),
(4, 4, 'Lutong Bisaya Kitchen', 'lutong-bisaya-kitchen', 'Authentic Visayan cuisine using fresh locally-sourced ingredients. Experience the true taste of San Enrique through traditional recipes passed down through generations.', 'National Highway, San Enrique', 'Poblacion', NULL, NULL, NULL, 10.91670000, 122.88310000, NULL, NULL, '', '7:00 AM - 8:00 PM', 'N/A', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(5, 5, 'Verde Organic Farm', 'verde-organic-farm', 'Visit our thriving organic farm and learn sustainable farming practices. Harvest fresh vegetables, experience farm life, and take home organic produce.', 'Barangay Agcalaga, San Enrique', 'Agcalaga', '', '', '', 10.92890000, 122.89560000, '', '[]', '', '8:00 AM - 5:00 PM', '₱80 per person', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:38:38'),
(6, 2, 'Barangay Punta Verde', 'barangay-punta-verde', 'A vibrant coastal barangay known for its fishing community, colorful banca boats, and spectacular sunset views over the Guimaras Strait.', 'Punta Verde, San Enrique', 'Punta Verde', NULL, NULL, NULL, 10.91120000, 122.87890000, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(7, 2, 'Barangay Abaca', 'barangay-abaca', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Abaca, San Enrique, Iloilo', 'Abaca', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553757301737', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 2, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:41:25'),
(8, 2, 'Barangay Asisig', 'barangay-asisig', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Asisig, San Enrique, Iloilo', 'Asisig', NULL, NULL, 'https://www.facebook.com/groups/223302368894780', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(9, 2, 'Barangay Bantayan', 'barangay-bantayan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Bantayan, San Enrique, Iloilo', 'Bantayan', NULL, NULL, '', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(10, 2, 'Barangay Braulan', 'barangay-braulan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Braulan, San Enrique, Iloilo', 'Braulan', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553682964106', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(11, 2, 'Barangay Cabugao Nuevo', 'barangay-cabugao-nuevo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Nuevo, San Enrique, Iloilo', 'Cabugao Nuevo', NULL, NULL, '', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(12, 2, 'Barangay Cabugao Viejo', 'barangay-cabugao-viejo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Viejo, San Enrique, Iloilo', 'Cabugao Viejo', NULL, NULL, 'https://www.facebook.com/groups/213430483312531', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(13, 2, 'Barangay Camiri', 'barangay-camiri', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay\'s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community\'s strong spiritual connection and respect for their history.', 'Barangay Camiri, San Enrique, Iloilo', 'Camiri', NULL, NULL, 'https://www.facebook.com/profile.php?id=61565832921860', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(14, 2, 'Barangay Compo', 'barangay-compo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Compo, San Enrique, Iloilo', 'Compo', NULL, NULL, 'https://www.facebook.com/profile.php?id=100066410029706', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(15, 2, 'Barangay Catan-Agan', 'barangay-catan-agan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Catan-Agan, San Enrique, Iloilo', 'Catan-Agan', NULL, NULL, 'https://www.facebook.com/profile.php?id=100063789806327', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(16, 2, 'Barangay Cubay', 'barangay-cubay', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.', 'Barangay Cubay, San Enrique, Iloilo', 'Cubay', NULL, NULL, 'https://www.facebook.com/profile.php?id=61556987704834', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(17, 2, 'Barangay Dacal', 'barangay-dacal', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Dacal, San Enrique, Iloilo', 'Dacal', NULL, NULL, 'https://www.facebook.com/profile.php?id=61556520538650', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(18, 2, 'Barangay Dumiles', 'barangay-dumiles', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay\'s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.', 'Barangay Dumiles, San Enrique, Iloilo', 'Dumiles', NULL, NULL, 'https://www.facebook.com/profile.php?id=100078247682899', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(19, 2, 'Barangay Garita', 'barangay-garita', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Garita, San Enrique, Iloilo', 'Garita', NULL, NULL, 'https://www.facebook.com/profile.php?id=61558183211226', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(20, 2, 'Barangay Gines Nuevo', 'barangay-gines-nuevo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Gines Nuevo, San Enrique, Iloilo', 'Gines Nuevo', NULL, NULL, 'https://www.facebook.com/profile.php?id=100078221154180', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(21, 2, 'Barangay Imbang Pequeño', 'barangay-imbang-pequeno', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbang Pequeño, San Enrique, Iloilo', 'Imbang Pequeño', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553163435790', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(22, 2, 'Barangay Imbesad-an', 'barangay-imbesad-an', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbesad-an, San Enrique, Iloilo', 'Imbesad-an', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553557508530', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(23, 2, 'Barangay Iprog', 'barangay-iprog', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Iprog, San Enrique, Iloilo', 'Iprog', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553154336493', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(24, 2, 'Barangay Lip-ac', 'barangay-lip-ac', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Lip-ac, San Enrique, Iloilo', 'Lip-ac', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553035064866', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(25, 2, 'Barangay Madarag', 'barangay-madarag', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Madarag, San Enrique, Iloilo', 'Madarag', NULL, NULL, 'https://www.facebook.com/kasimaryo.sang.madarag', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(26, 2, 'Barangay Mapili', 'barangay-mapili', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Mapili, San Enrique, Iloilo', 'Mapili', NULL, NULL, 'https://www.facebook.com/profile.php?id=61553671329335', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(27, 2, 'Barangay Paga', 'barangay-paga', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Paga, San Enrique, Iloilo', 'Paga', NULL, NULL, 'https://www.facebook.com/profile.php?id=61583143784931', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(28, 2, 'Barangay Palje', 'barangay-palje', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Palje, San Enrique, Iloilo', 'Palje', NULL, NULL, '', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(29, 2, 'Barangay Poblacion Ilawod', 'barangay-poblacion-ilawod', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilawod, San Enrique, Iloilo', 'Poblacion Ilawod', NULL, NULL, 'https://www.facebook.com/profile.php?id=100086784212137', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(30, 2, 'Barangay Poblacion Ilaya', 'barangay-poblacion-ilaya', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilaya, San Enrique, Iloilo', 'Poblacion Ilaya', NULL, NULL, 'https://www.facebook.com/SKPoblacionIlaya', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(31, 2, 'Barangay Quinolpan', 'barangay-quinolpan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo in Barangay Quinolpan stands as a peaceful landmark that reflects the barangay\'s deep spiritual roots and close-knit community. Modest yet meaningful, it serves as a place where locals gather for quiet prayer and reflection. Surrounded by open fields and fresh air, the simboryo mirrors the simplicity and strong faith of the people, preserving their traditions through time.', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', NULL, NULL, 'https://www.facebook.com/barangayquinolpansanenriqueiloilophilippines5036', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(32, 2, 'Barangay Rumagayray', 'barangay-rumagayray', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. In Barangay Rumagayray, the simboryo holds a special place in the hearts of its people. Known for its weathered beauty, it represents not only faith but also the strength and unity of the community. It serves as a gathering point for reflection and remembrance, carrying with it the stories and prayers of generations.', 'Barangay Rumagayray, San Enrique, Iloilo', 'Rumagayray', NULL, NULL, 'https://www.facebook.com/groups/1556000491533439', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(33, 2, 'Barangay San Antonio', 'barangay-san-antonio', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay San Antonio, San Enrique, Iloilo', 'San Antonio', NULL, NULL, '', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(34, 2, 'Barangay Tambunac', 'barangay-tambunac', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Tambunac, San Enrique, Iloilo', 'Tambunac', NULL, NULL, 'https://www.facebook.com/barangay.tambunac', NULL, NULL, NULL, NULL, '', 'Open 24 hours', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(35, 5, 'Gumbans Amat Amat Farm', 'gumbans-amat-amat-farm', 'Gumbans Amat Amat Farm is a peaceful farm offering fresh produce and scenic views. Enjoy their fruit trees, fish pond, and vegetable garden. Enjoyable views include fruit trees (peach, lemon, avocado, coconut), fish pond, and vegetable garden.', 'Sitio Gelonoc Cubay, San Enrique, Iloilo', 'Cubay', '09627603707 / 09455067648', '', 'https://www.facebook.com/share1DHRHN9kbMn/?mibextid=wwXlfr', 10.91780000, 122.88450000, '../uploads/listings/listing_1774193877_552.jpg', '[\"..\\/uploads\\/listings\\/gallery_1774193877_3946_0.jpg\"]', '', 'Monday to Friday: 8:00 AM - 5:00 PM; Saturday and Sunday: 9:00 AM - 5:00 PM', 'Contact for rates', 'Fruit trees (peach, lemon, avocado, coconut), Fish pond, Vegetable garden', 'active', 1, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:37:57'),
(36, 6, 'Cabas-an Cold Spring', 'cabas-an-cold-spring', 'Cabasan Cold Spring is a natural spring offering cool, clear waters perfect for swimming and relaxation. Enjoyable views include lush greenery, natural rock formations, and scenic surroundings. Activities: swimming, picnic, and nature walks.', 'Sitio Cabas-an, Brgy. Compo, San Enrique, Iloilo 5036', 'Compo', '0917 302 3878', 'ebtconfi2@gmail.com', 'https://facebook.com/composanenriqueiloilo', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1IIzqR-BdAVla0bMmoQVD6O4b2L7e-74G', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/12_pjtS1JzfpboXJGEyNQKp2DR7pRNsY5\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ojNuVohcmWTDIHtfh-IF8hGNbNUGEAxU\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1qma6CERcakkjnLlFdzkVojHpWaxVK-8C\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1abm-slqtPqTsznRa1AFP5KdU8xJRX-w8\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BDLkortHSboFCsq4CWV1Y5nyrH064tYc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1RWfwKrgR61EV0USFDUrydHelQK1mo3-D\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BLgm5FehwbCIyzO5Vri68G0DWSY61LYI\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1EtwrlQ4A-WW4jMiCuN5OVGKymORAMK_R\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1cF3dMUHLpp9DRIgxk_9uVEw6X1zXxoTV\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1IIzqR-BdAVla0bMmoQVD6O4b2L7e-74G\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1xRD3avFozrLY68iax2PEMOfTBzInihdp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1N_lIjMkZuqMjPx5JMYhqqqQwYqAaKaiU\"]', '', '7:00 AM - 5:00 PM, Daily', 'Contact for rates', 'Natural spring pool, Swimming area, Picnic area, Nature walks, Rock formations', 'active', 1, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:17:43'),
(37, 5, 'BC Farm', 'bc-farm', 'BC Farm is a peaceful farm in San Enrique, Iloilo, offering a serene atmosphere for swimming and relaxation.', 'Brgy Cabugao Viejo, San Enrique, Iloilo 5036', 'Cabugao Viejo', '0985 181 9131', 'benedictopadon@gmail.com', 'https://facebook.com/BCResortSanEnrique', NULL, NULL, NULL, NULL, '', 'Daily (open 24/7)', 'Contact for rates', 'Swimming area, Farm atmosphere, Relaxation area', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(38, 1, 'Tonys Farm Resort', 'tonys-farm-resort', 'Tonys Valley Farm Resort is a private resort offering a peaceful atmosphere with swimming pools and cottages. Enjoyable views include lush greenery, swimming pools, and scenic surroundings. Activities: swimming, picnic, and nature walks.', 'Sitio Layog Bato, Brgy. Mapili, San Enrique, Iloilo', 'Mapili', '0908 866 6909', '', 'https://facebook.com/TVFRM', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1im3ZlRXO7ii_KD2BSfv06e6M9FJOe94J', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/15AU4AYUXkLrSYhcjy4OR5JPYSTJHaSCY\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1MAulkNur2vBmRFKbdVPIYvNh7B0lDxIW\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1tUD02J6Ln8sRFatJjqoId-PYUJwTsrNp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/12jYDEdFRi98wjeVq5vRUuvDC7OVa7TFg\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ZSuQsVMts2dwPTtJqsPsoe0j1M0dx0ph\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1E-xlVBhiGrKroK4Kfj71b7pxeDz0a08r\"]', '', 'Daily (contact for specific hours)', 'Contact for rates', 'Swimming pools, Cottages, Picnic area, Nature walks, Lush greenery', 'active', 1, 5, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:21:13'),
(39, 6, 'San Antonio San Enrique River', 'san-antonio-san-enrique-river', 'The San Antonio San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.', 'San Antonio, San Enrique, Iloilo', 'San Antonio', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1Tk9xlfh6LCo7blrbYy4gwwEREKVHH4Xp', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jp5-jW7coEXeLZvgr1S_IXCo1Y43rmkK\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1xwdebQAqJm0h-e1esp4eKKHvwbdHR261\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1OPXLMJKHG4nKnafOpM4htfmPZ5eJDiSG\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1Tk9xlfh6LCo7blrbYy4gwwEREKVHH4Xp\"]', '', '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM', 'Free', 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:56:55'),
(40, 6, 'Catan-Agan San Enrique River', 'catan-agan-san-enrique-river', 'The Catan-Agan San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.', 'Catan-Agan, San Enrique, Iloilo', 'Catan-Agan', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1xJwMrLWubZyLyFkRMj0GAtVPGkeeFShZ', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1J0SUIT63FziBoAcCNzFbzqWji-wUJOja\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1wA_xLS_DGhGMQ0_hWUAvUovXAwc3FXO7\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/13Jq62X_UhiYG2genjcl-QvpfvnfEfFwp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/13eld7kjD7OlTpsd0bjIWpY0l4NLaq08W\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1dlMdr8MkLoHmAwZbm2djfejCyKe_zkqR\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1LWJvKKjzIoIC-M6FmIBIIuNZbq5SVucn\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lxLoslb7z6EMSkeTbkUI_tYC3V1dEoLp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1zzf_30KnT3uLs_sF-Bf3VFzMnjVkaVzo\"]', '', '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM', 'Free', 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:13:32'),
(41, 4, 'Puto (Native Delicacy)', 'puto-native-delicacy', 'A famous Filipino steamed rice cake. In San Enrique, Puto is traditionally sold by vendors outside the church or inside the market, especially after Sunday Mass. Best prepared and paired with Dinuguan.', 'Local Markets & Church Vicinity, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Sundays (after Mass) and daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(42, 4, 'Kutsinta (Native Delicacy)', 'kutsinta-native-delicacy', 'A popular steamed rice cake in San Enrique, Iloilo, often featured alongside the town\'s famous kalamay as a staple \"pang yam-is\" (sweet) merienda. These sticky, chewy, and brown or black treats are typically served with freshly grated coconut. Made from all-purpose flour or tapioca starch, brown sugar, lye water, and annatto water, steamed for 30-45 minutes.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(43, 4, 'Alupe / Suman Balanghoy (Native Delicacy)', 'alupe-suman-balanghoy', 'A traditional Ilonggo delicacy popular in San Enrique, Iloilo. Also known as Alupi or Suman Balanghoy, it is a sweet, steamed, and chewy cassava-based suman wrapped in banana leaves, often featuring shredded young coconut (buko) and coconut milk. Best enjoyed with bukayo (sweetened coconut strips) in the center for a gooey, extra-sweet surprise. A staple merienda often present during local fiestas and special occasions.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(44, 4, 'Inday-Inday (Native Delicacy)', 'inday-inday-native-delicacy', 'A traditional Ilonggo native delicacy (kakanin) popular in San Enrique, Iloilo, and other parts of Western Visayas. A sweet, chewy snack made from poached glutinous rice dough discs, very similar to palitaw, but distinguished by its rich, caramelized coconut topping known as bukayo.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(45, 4, 'Kalamay Hati (Native Delicacy)', 'kalamay-hati-native-delicacy', 'A traditional Filipino sweet rice cake and a celebrated product of San Enrique, Iloilo, featured during their annual town festival. A very sticky, thick, and chewy delicacy made from ground glutinous rice, coconut milk, and muscovado or brown sugar, commonly topped with latik (coconut milk curds).', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 1, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(46, 4, 'Minatamis na Saging (Native Delicacy)', 'minatamis-na-saging', 'A staple Filipino dessert and merienda popular throughout Iloilo, including San Enrique, often served as a simple treat, a topping for shaved ice (Saba con Yelo), or an ingredient in halo-halo. Consists of ripe Saba bananas simmered until tender in a thickened, caramelized brown sugar syrup, sometimes enhanced with pandan leaves, vanilla, or salt to balance the sweetness.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(47, 4, 'Suman (Native Delicacy)', 'suman-native-delicacy', 'A popular native delicacy (kakanin) in San Enrique, Iloilo, known for its deep brown color, sticky texture, and rich flavor. A traditional Ilonggo suman typically wrapped in banana leaves and may come topped with latik (coconut caramel) or served plain, often prepared by local makers, including seniors who have learned the traditional process.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(48, 4, 'Bibingka (Native Delicacy)', 'bibingka-native-delicacy', 'A traditional, soft, and slightly sweet rice cake from San Enrique, Iloilo, cooked over live charcoal in banana leaf-lined molds. Known for its aromatic aroma, this native delicacy is typically made from rice flour (or soaked glutinous rice), coconut milk, sugar, eggs, and topped with cheese, salted eggs, or shredded coconut.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(49, 4, 'Baye-Baye (Native Delicacy)', 'baye-baye-native-delicacy', 'A popular native delicacy in the Visayas region, particularly in Iloilo. A well-loved Ilonggo treat found in various parts of the region, including areas like San Enrique.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(50, 4, 'Muasi (Native Delicacy)', 'muasi-native-delicacy', 'A popular, traditional Ilonggo delicacy often found in San Enrique and throughout Iloilo. Recognized as an Ilonggo version of palitaw, consisting of soft, chewy dumplings made from glutinous rice, commonly topped or stuffed with toasted sesame seeds and sugar.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(51, 4, 'Ibos / Dali-Dalin (Native Delicacy)', 'ibos-dali-dalin-native-delicacy', 'A type of sticky rice (glutinous rice) wrapped in coconut or buri leaves, popularly called Ibos. Best enjoyed dipped in sugar.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(52, 3, 'Simboryo ng Barangay Quinolpan', 'simboryo-quinolpan', 'The simboryo in Barangay Quinolpan stands as a peaceful landmark that reflects the barangay\'s deep spiritual roots and close-knit community. Modest yet meaningful, it serves as a place where locals gather for quiet prayer and reflection. Surrounded by open fields and fresh air, the simboryo mirrors the simplicity and strong faith of the people, preserving their traditions through time.', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(53, 3, 'Simboryo ng Barangay Cubay', 'simboryo-cubay', 'In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.', 'Barangay Cubay, San Enrique, Iloilo', 'Cubay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(54, 3, 'Simboryo ng Barangay Camiri', 'simboryo-camiri', 'The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay\'s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community\'s strong spiritual connection and respect for their history.', 'Barangay Camiri, San Enrique, Iloilo', 'Camiri', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(55, 3, 'Simboryo ng Barangay Rumagayray', 'simboryo-rumagayray', 'In Barangay Rumagayray, the simboryo holds a special place in the hearts of its people. Known for its weathered beauty, it represents not only faith but also the strength and unity of the community. It serves as a gathering point for reflection and remembrance, carrying with it the stories and prayers of generations.', 'Barangay Rumagayray, San Enrique, Iloilo', 'Rumagayray', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(56, 3, 'Simboryo ng Barangay Dumiles', 'simboryo-ng-barangay-dumiles', 'The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay&amp;amp;amp;#039;s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.', 'Barangay Dumiles, San Enrique, Iloilo', 'Dumiles', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1PvwMc6fi8j2Y6KGqkTrtOVT4JP1soIcz', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1qiQViMSGw454VoNxLNfrv2E8_c2HBxdY\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1efwZ-NxzPxk1Sjw5C17ZnkEfzFGHGsMw\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1SyZeW4Pl9OgLFGHUFlCCW4FqZATHfJuQ\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jA-pv1qPg8Yrmn_ncS_knJpLh2giExNc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1sxZkzJJZ_DT3d9DD1ZGtYkZ2uuqawfqD\"]', '', 'Open daily', 'Free', '', 'active', 0, 1, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:01:22'),
(57, 6, 'Mt. Bayuso', 'mt-bayuso', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1YF7ejJvzeh4qCoaENAxzpm_bVO8AiGq4', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1FhIs_yCK0k_vPbrG4kly-z47wrZCUxQr\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1d4Ao81G9umVevvuFade54MF4IDhpKpDP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1unOrWlW3XP4u_WbTpL9hkuo0OByzd64c\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1qIP2wctAvBaMaVVcok_qEA6EXUPJJ8aC\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1oSs7X4MuLKFAdfT-DU4g7lRUvv0Eioyr\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1s6YA6iqqLPqcqVoHNHrAoSstcsVorjUP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1OnL95lbW819mF7c-ajYn0pkD5WorUJgS\"]', '', '', '', '', 'active', 0, 0, 0.00, '2026-03-22 15:07:06', '2026-03-22 15:07:06'),
(58, 6, 'Hacienda Pavilion', 'hacienda-pavilion', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1C_ifiPtcKIpAusFxksj3Jqk_2vPC8deq', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/16S54JI_3FXJr9O_t7qescaRB7d3t2H8-\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lrSeeHaaVa4uODwJ3lVaQQrEYOUMASCN\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1FS9FlHao9iuK0JAnIV7NVbz2LkrU-oiM\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lnChKrCZ_XKZLPjU_aAcoz3UANMKfBU6\"]', '', '', '', '', 'active', 1, 0, 0.00, '2026-03-22 15:08:52', '2026-03-22 15:38:44'),
(59, 1, 'BCs Resort', 'bcs-resort', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/17ZXy8WGIvHf_uwtmk5sekwd4r6wX4nFu', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1-FYNCi1f1Pej5RMhjDPijNBGIU4JecJC\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1yUZCmnHRmwMIdBaAe6B0PwJx5CfBPHyP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ofIuSMMIG71rmCEiB9GHoQAvXwdoVJS7\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1UJGvhQDXOtsGsRemeaPxK-vFN7emVG9j\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1EjD_0TY8o2SlyCjug3OJbBu6z_KAxBPN\"]', '', '', '', '', 'active', 0, 0, 0.00, '2026-03-22 15:21:05', '2026-03-22 15:27:16'),
(60, 3, 'Artifacts', 'artifacts', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1ANSA1C7-BiNJFVMPiB5s_C8x4wrW8IUb', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1XZ-EbThSle2mcch4X4EXRoYmRZU_2OUg\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jBrj0uKTS-aEhmu7fxUFXzdTXKX-KZ4w\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1USxwn50H_QCT75WbbnCYpRIZAWOgo8Am\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1a4Jq_M4NCrtZ-lpKB_aRE9O-PC-RZZo8\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BJeaohmcVtQvPKb7y0mtjtoGrZCWN_qc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/15rPqGEse4Xroy4UgbFUTLOKbgvNeGfdj\"]', '', '', '', '', 'active', 0, 0, 0.00, '2026-03-22 15:26:55', '2026-03-22 15:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `listing_id` int NOT NULL,
  `reviewer_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
