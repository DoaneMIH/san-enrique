-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 03:49 PM
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
USE san_enrique_tourism;


-- Migration: Add is_pinned column to events table
-- Run this once in your MySQL database
 
ALTER TABLE events
  ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0
  AFTER status;
 
-- Optional: pin any existing events you want always visible
-- UPDATE events SET is_pinned = 1 WHERE id = 1;
 

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('superadmin','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
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
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fas fa-map-marker-alt',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#2d6a4f',
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
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `event_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
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
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `barangay` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `featured_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `video` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `operating_hours` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entrance_fee` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amenities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
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
(3, 3, 'San Enrique Heritage Church', 'san-enrique-heritage-church', 'The historic Saint Enrique Parish Church, a centuries-old architectural marvel that stands as a testament to the rich cultural heritage of the municipality.', 'Poblacion, San Enrique', 'Poblacion', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1lcRZlKw4-LNy7ok1lvallBeNKY-cBqUz', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1XF6irk8Dp6lTxtQW_enc8vUyjS9eT8k0\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1hjmlvRuBraXar_SYX2HQse2Nh3pNPx6y\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1f3M0Fyl2oyCAxhtu2wx_EOoIYymcBBYl\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1C-wjW7lyzva2CgtWcwGz6CSLzgLzbUpx\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1F-hqvnwifkGXwxQ5pYU5-nnMTJgEAFKa\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/10GBhPX_CWGvE5VPFESFY4z-6GISxxgA5\"]', '', 'Open Daily', 'Free', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 09:35:54'),
(7, 2, 'Barangay Abaca', 'barangay-abaca', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Abaca, San Enrique, Iloilo', 'Abaca', '', '', 'https://www.facebook.com/profile.php?id=61553757301737', 11.12857698, 122.71505688, 'https://lh3.googleusercontent.com/d/1gl3LLyeFmp_6SNoI5spY394jg7gbnIZx', '[]', '', 'Open 24 hours', '', '', 'active', 0, 10, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:31:02'),
(8, 2, 'Barangay Asisig', 'barangay-asisig', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Asisig, San Enrique, Iloilo', 'Asisig', '', '', 'https://www.facebook.com/groups/223302368894780', 11.10568862, 122.67145496, 'https://lh3.googleusercontent.com/d/15QuC_8lPcbjVeY7HBFFOAbduXO-I3hPU', '[]', '', 'Open 24 hours', '', '', 'active', 0, 2, 0.00, '2026-03-22 14:09:14', '2026-03-24 14:48:25'),
(9, 2, 'Barangay Bantayan', 'barangay-bantayan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Bantayan, San Enrique, Iloilo', 'Bantayan', '', '', '', 11.08706339, 122.69278188, 'https://lh3.googleusercontent.com/d/1SQhwXQSKYJwxZJhzatDEh5XiFI5KCdoe', '[]', '', 'Open 24 hours', '', '', 'active', 0, 2, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:07:22'),
(10, 2, 'Barangay Braulan', 'barangay-braulan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Braulan, San Enrique, Iloilo', 'Braulan', '', '', 'https://www.facebook.com/profile.php?id=61553682964106', 11.13591262, 122.73798886, 'https://lh3.googleusercontent.com/d/1cxjrRf4JE48Y3hY7l614QwpdANjSpfKM', '[]', '', 'Open 24 hours', '', '', 'active', 0, 1, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:09:54'),
(11, 2, 'Barangay Cabugao Nuevo', 'barangay-cabugao-nuevo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Nuevo, San Enrique, Iloilo', 'Cabugao Nuevo', '', '', '', 11.07133335, 122.67173606, 'https://lh3.googleusercontent.com/d/1QVTs4XMGT6X6EBsUXIqcsw63Gqu0IWvf', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:10:25'),
(12, 2, 'Barangay Cabugao Viejo', 'barangay-cabugao-viejo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Viejo, San Enrique, Iloilo', 'Cabugao Viejo', '', '', 'https://www.facebook.com/groups/213430483312531', 11.07205077, 122.66318188, 'https://lh3.googleusercontent.com/d/181t3ikUdOy0bmSIzGn0Sx2cPwYuhEnI6', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:10:45'),
(13, 2, 'Barangay Camiri', 'barangay-camiri', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay&amp;#039;s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community&amp;#039;s strong spiritual connection and respect for their history.', 'Barangay Camiri, San Enrique, Iloilo', 'Camiri', '', '', 'https://www.facebook.com/profile.php?id=61565832921860', 11.08264153, 122.65384795, 'https://lh3.googleusercontent.com/d/1Bk7stSj8hqT2M0p1XnmrX1hZbB5dbPYK', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:11:25'),
(14, 2, 'Barangay Compo', 'barangay-compo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Compo, San Enrique, Iloilo', 'Compo', '', '', 'https://www.facebook.com/profile.php?id=100066410029706', 11.05672006, 122.65896291, 'https://lh3.googleusercontent.com/d/1PF24dubB3njOX7RFCt2a1z5reU6nWBCO', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:13:33'),
(15, 2, 'Barangay Catan-Agan', 'barangay-catan-agan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Catan-Agan, San Enrique, Iloilo', 'Catan-Agan', '', '', 'https://www.facebook.com/profile.php?id=100063789806327', 11.12410319, 122.74969667, 'https://lh3.googleusercontent.com/d/1OYHtcwxN6XDMRcoMcHfQ3eMLxY3hHPLs', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:12:50'),
(16, 2, 'Barangay Cubay', 'barangay-cubay', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.', 'Barangay Cubay, San Enrique, Iloilo', 'Cubay', '', '', 'https://www.facebook.com/profile.php?id=61556987704834', 11.08120345, 122.70823632, 'https://lh3.googleusercontent.com/d/16p_l5XHDQRgLeBocpotlsKBw9QtTgGLm', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:15:08'),
(17, 2, 'Barangay Dacal', 'barangay-dacal', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Dacal, San Enrique, Iloilo', 'Dacal', '', '', 'https://www.facebook.com/profile.php?id=61556520538650', 11.11524601, 122.70374866, 'https://lh3.googleusercontent.com/d/1Wun5bHOp54-FshtemKzPIBwKCYzLGpPN', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:15:36'),
(18, 2, 'Barangay Dumiles', 'barangay-dumiles', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay&amp;#039;s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.', 'Barangay Dumiles, San Enrique, Iloilo', 'Dumiles', '', '', 'https://www.facebook.com/profile.php?id=100078247682899', 11.09997670, 122.70950057, 'https://lh3.googleusercontent.com/d/1o_C5K_rUgnf-vtS2Wnxq9ZEC_q1MTDtt', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:18:15'),
(19, 2, 'Barangay Garita', 'barangay-garita', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Garita, San Enrique, Iloilo', 'Garita', '', '', 'https://www.facebook.com/profile.php?id=61558183211226', 11.08169824, 122.73195189, 'https://lh3.googleusercontent.com/d/12ECATm1e-mea778IvXYjituXdkFowD3L', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:20:21'),
(20, 2, 'Barangay Gines Nuevo', 'barangay-gines-nuevo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Gines Nuevo, San Enrique, Iloilo', 'Gines Nuevo', '', '', 'https://www.facebook.com/profile.php?id=100078221154180', 11.09814518, 122.68014805, 'https://lh3.googleusercontent.com/d/1BVwGq9KDfhwm_BT7ARteck1m71_0G3SE', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:21:00'),
(21, 2, 'Barangay Imbang Pequeño', 'barangay-imbang-peque-o', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbang Pequeño, San Enrique, Iloilo', 'Imbang Pequeño', '', '', 'https://www.facebook.com/profile.php?id=61553163435790', 11.09166250, 122.65278398, 'https://lh3.googleusercontent.com/d/1nBEh3Iy4WL3PztWlGSgOsQTKz5giZh80', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:23:29'),
(22, 2, 'Barangay Imbesad-an', 'barangay-imbesad-an', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbesad-an, San Enrique, Iloilo', 'Imbesad-an', '', '', 'https://www.facebook.com/profile.php?id=61553557508530', 11.07212201, 122.69250297, 'https://lh3.googleusercontent.com/d/1Dv9EdLFDtVshAUFUY5YVhD_EF_-7k40f', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:25:08'),
(23, 2, 'Barangay Iprog', 'barangay-iprog', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Iprog, San Enrique, Iloilo', 'Iprog', '', '', 'https://www.facebook.com/profile.php?id=61553154336493', 11.14831365, 122.75498496, 'https://lh3.googleusercontent.com/d/1SWiiUQmDvpADn85vYDBy1-_AeCn_Ys_k', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1DcklEI1R0gdAJkOJtK6gYmUOWKQozmqs\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1yQBOCZBvxCkCmIQDawaAw-BsCjDGrLBY\"]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:26:03'),
(24, 2, 'Barangay Lip-ac', 'barangay-lip-ac', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Lip-ac, San Enrique, Iloilo', 'Lip-ac', '', '', 'https://www.facebook.com/profile.php?id=61553035064866', 11.06866676, 122.68109716, 'https://lh3.googleusercontent.com/d/1-FpEkhJLvB4rYBU3pIlu6lj2M505O1rV', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:27:23'),
(25, 2, 'Barangay Madarag', 'barangay-madarag', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Madarag, San Enrique, Iloilo', 'Madarag', '', '', 'https://www.facebook.com/kasimaryo.sang.madarag', 11.14531400, 122.77727337, 'https://lh3.googleusercontent.com/d/1tL4ZyWpyRuEvpLuvcVRzHj4EVUAS5UGc', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:27:55'),
(26, 2, 'Barangay Mapili', 'barangay-mapili', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Mapili, San Enrique, Iloilo', 'Mapili', '', '', 'https://www.facebook.com/profile.php?id=61553671329335', 11.10250639, 122.74632353, 'https://lh3.googleusercontent.com/d/1LD8NwB13QwGiuRcZAsCmjRzwt5MWqJ2v', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1KEETAf6ukAKrqqanV4oYqTXIm5UE8oHa\"]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:28:43'),
(27, 2, 'Barangay Paga', 'barangay-paga', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Paga, San Enrique, Iloilo', 'Paga', '', '', 'https://www.facebook.com/profile.php?id=61583143784931', 11.08412041, 122.67577138, 'https://lh3.googleusercontent.com/d/1W9H4ugN565epT9U2GUv2CjD0KOG1MULS', '[]', '', 'Open 24 hours', '', '', 'active', 0, 1, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:41:00'),
(28, 2, 'Barangay Palje', 'barangay-palje', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Palje, San Enrique, Iloilo', 'Palje', '', '', '', 11.05658410, 122.66858714, 'https://lh3.googleusercontent.com/d/1k-SAc2b9t6_78hzeB5vjMrzyhQHvSooq', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:29:30'),
(29, 2, 'Barangay Poblacion Ilawod', 'barangay-poblacion-ilawod', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilawod, San Enrique, Iloilo', 'Poblacion Ilawod', '', '', 'https://www.facebook.com/profile.php?id=100086784212137', 11.06985974, 122.65527262, 'https://lh3.googleusercontent.com/d/14eBNpCB5GZhO59CfMyfH8UWes05mfrji', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:30:51'),
(30, 2, 'Barangay Poblacion Ilaya', 'barangay-poblacion-ilaya', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilaya, San Enrique, Iloilo', 'Poblacion Ilaya', '', '', 'https://www.facebook.com/SKPoblacionIlaya', 11.07534964, 122.65771193, 'https://lh3.googleusercontent.com/d/1FAnx_tnZd2RfibmDEaZ62BDzbwJMIPi6', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:32:21'),
(31, 2, 'Barangay Quinolpan', 'barangay-quinolpan', 'Rural agricultural community', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', '', '', 'https://www.facebook.com/barangayquinolpansanenriqueiloilophilippines5036', 11.10277981, 122.69413118, 'https://lh3.googleusercontent.com/d/1c7tOTPS4E9rEm-XfA6vlnE8txmYMUOUk', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:33:14'),
(32, 2, 'Barangay Rumagayray', 'barangay-rumagayray', 'Small barangay with limited development share. \r\n‎', 'Barangay Rumagayray, San Enrique, Iloilo', 'Rumagayray', '', '', 'https://www.facebook.com/groups/1556000491533439', 11.04405158, 122.64825408, 'https://lh3.googleusercontent.com/d/1YlOJhb_Bjxz5kkini2N6gwz-bNnQ1-qk', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:34:03'),
(33, 2, 'Barangay San Antonio', 'barangay-san-antonio', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay San Antonio, San Enrique, Iloilo', 'San Antonio', '', '', '', 11.12558098, 122.77806342, 'https://lh3.googleusercontent.com/d/1C0hnFiOzU68wVntr2L4SGUzLyM4I3yyR', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:35:05'),
(34, 2, 'Barangay Tambunac', 'barangay-tambunac', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Tambunac, San Enrique, Iloilo', 'Tambunac', '', '', 'https://www.facebook.com/barangay.tambunac', 11.07454366, 122.68047106, 'https://lh3.googleusercontent.com/d/1y4KKL1il7H8HWNBtJfrzgu2ZMYHRgR2s', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 13:35:29'),
(35, 5, 'Gumbans Amat Amat Farm', 'gumbans-amat-amat-farm', 'Gumbans Amat Amat Farm is a peaceful farm offering fresh produce and scenic views. Enjoy their fruit trees, fish pond, and vegetable garden. Enjoyable views include fruit trees (peach, lemon, avocado, coconut), fish pond, and vegetable garden.', 'Sitio Gelonoc Cubay, San Enrique, Iloilo', 'Cubay', '09627603707 / 09455067648', '', 'https://www.facebook.com/share1DHRHN9kbMn/?mibextid=wwXlfr', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1uaYxkPouG_dWftb9hcdaFfrhSLojdKLG', '[\"..\\/uploads\\/listings\\/gallery_1774193877_3946_0.jpg\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1t3SUhrRtzoDgU0LhzJ01e6guYK1vsH9j\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1IW48uk2mMRw_V9t1GBlAJFZ2sqK734yr\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1Jpwx7v9x7Uyh2wrfpCJAKLuBIucS_HyP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1aBKBpSQvM_qsrfdpNTltyhxqgbdHBayU\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1t3SUhrRtzoDgU0LhzJ01e6guYK1vsH9j\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lZU2sO28viunut6_m2dwlTxw4dWYnWzB\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1RCEmFfbTkklzZI2hGqjVT8dcTrv2ESvU\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1OsbuagXta8GN2jIrqU0xuQMXSFJK4GNN\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1sQPd9yEUYZ-j2dwF8nyWwNy7495I27l3\"]', '', 'Monday to Friday: 8:00 AM - 5:00 PM; Saturday and Sunday: 9:00 AM - 5:00 PM', 'Contact for rates', 'Fruit trees (peach, lemon, avocado, coconut), Fish pond, Vegetable garden', 'active', 1, 11, 0.00, '2026-03-22 14:09:14', '2026-03-24 14:41:25'),
(36, 1, 'Cabas-an Cold Spring', 'cabas-an-cold-spring', 'Cabasan Cold Spring is a natural spring offering cool, clear waters perfect for swimming and relaxation. Enjoyable views include lush greenery, natural rock formations, and scenic surroundings. Activities: swimming, picnic, and nature walks.', 'Sitio Cabas-an, Brgy. Compo, San Enrique, Iloilo 5036', 'Compo', '0917 302 3878', 'ebtconfi2@gmail.com', 'https://facebook.com/composanenriqueiloilo', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1IIzqR-BdAVla0bMmoQVD6O4b2L7e-74G', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/12_pjtS1JzfpboXJGEyNQKp2DR7pRNsY5\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ojNuVohcmWTDIHtfh-IF8hGNbNUGEAxU\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1qma6CERcakkjnLlFdzkVojHpWaxVK-8C\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1abm-slqtPqTsznRa1AFP5KdU8xJRX-w8\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BDLkortHSboFCsq4CWV1Y5nyrH064tYc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1RWfwKrgR61EV0USFDUrydHelQK1mo3-D\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BLgm5FehwbCIyzO5Vri68G0DWSY61LYI\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1EtwrlQ4A-WW4jMiCuN5OVGKymORAMK_R\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1cF3dMUHLpp9DRIgxk_9uVEw6X1zXxoTV\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1IIzqR-BdAVla0bMmoQVD6O4b2L7e-74G\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1xRD3avFozrLY68iax2PEMOfTBzInihdp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1N_lIjMkZuqMjPx5JMYhqqqQwYqAaKaiU\"]', '', '7:00 AM - 5:00 PM, Daily', 'Contact for rates', 'Natural spring pool, Swimming area, Picnic area, Nature walks, Rock formations', 'active', 1, 16, 0.00, '2026-03-22 14:09:14', '2026-03-24 14:31:09'),
(37, 5, 'BC Farm', 'bc-farm', 'BC Farm is a peaceful farm in San Enrique, Iloilo, offering a serene atmosphere for swimming and relaxation.', 'Brgy Cabugao Viejo, San Enrique, Iloilo 5036', 'Cabugao Viejo', '0985 181 9131', 'benedictopadon@gmail.com', 'https://facebook.com/BCResortSanEnrique', NULL, NULL, NULL, NULL, '', 'Daily (open 24/7)', 'Contact for rates', 'Swimming area, Farm atmosphere, Relaxation area', 'active', 0, 1, 0.00, '2026-03-22 14:09:14', '2026-03-24 10:14:41'),
(38, 1, 'Tonys Farm Resort', 'tonys-farm-resort', 'Tonys Valley Farm Resort is a private resort offering a peaceful atmosphere with swimming pools and cottages. Enjoyable views include lush greenery, swimming pools, and scenic surroundings. Activities: swimming, picnic, and nature walks.', 'Sitio Layog Bato, Brgy. Mapili, San Enrique, Iloilo', 'Mapili', '0908 866 6909', '', 'https://facebook.com/TVFRM', 11.10524189, 122.74732590, 'https://lh3.googleusercontent.com/d/1im3ZlRXO7ii_KD2BSfv06e6M9FJOe94J', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/15AU4AYUXkLrSYhcjy4OR5JPYSTJHaSCY\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1MAulkNur2vBmRFKbdVPIYvNh7B0lDxIW\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1tUD02J6Ln8sRFatJjqoId-PYUJwTsrNp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/12jYDEdFRi98wjeVq5vRUuvDC7OVa7TFg\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ZSuQsVMts2dwPTtJqsPsoe0j1M0dx0ph\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1E-xlVBhiGrKroK4Kfj71b7pxeDz0a08r\"]', '', 'Daily (contact for specific hours)', 'Contact for rates', 'Swimming pools, Cottages, Picnic area, Nature walks, Lush greenery', 'active', 1, 6, 0.00, '2026-03-22 14:09:14', '2026-03-24 11:45:32'),
(39, 6, 'San Antonio San Enrique River', 'san-antonio-san-enrique-river', 'The San Antonio San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.', 'San Antonio, San Enrique, Iloilo', 'San Antonio', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1Tk9xlfh6LCo7blrbYy4gwwEREKVHH4Xp', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jp5-jW7coEXeLZvgr1S_IXCo1Y43rmkK\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1xwdebQAqJm0h-e1esp4eKKHvwbdHR261\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1OPXLMJKHG4nKnafOpM4htfmPZ5eJDiSG\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1Tk9xlfh6LCo7blrbYy4gwwEREKVHH4Xp\"]', '', '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM', 'Free', 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:56:55'),
(40, 6, 'Catan-Agan San Enrique River', 'catan-agan-san-enrique-river', 'The Catan-Agan San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.', 'Catan-Agan, San Enrique, Iloilo', 'Catan-Agan', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1xJwMrLWubZyLyFkRMj0GAtVPGkeeFShZ', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1J0SUIT63FziBoAcCNzFbzqWji-wUJOja\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1wA_xLS_DGhGMQ0_hWUAvUovXAwc3FXO7\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/13Jq62X_UhiYG2genjcl-QvpfvnfEfFwp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/13eld7kjD7OlTpsd0bjIWpY0l4NLaq08W\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1dlMdr8MkLoHmAwZbm2djfejCyKe_zkqR\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1LWJvKKjzIoIC-M6FmIBIIuNZbq5SVucn\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lxLoslb7z6EMSkeTbkUI_tYC3V1dEoLp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1zzf_30KnT3uLs_sF-Bf3VFzMnjVkaVzo\"]', '', '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM', 'Free', 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:13:32'),
(41, 4, 'Puto (Native Delicacy)', 'puto-native-delicacy', 'A famous Filipino steamed rice cake. In San Enrique, Puto is traditionally sold by vendors outside the church or inside the market, especially after Sunday Mass. Best prepared and paired with Dinuguan.', 'Local Markets & Church Vicinity, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Sundays (after Mass) and daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(42, 4, 'Kutsinta (Native Delicacy)', 'kutsinta-native-delicacy', 'A popular steamed rice cake in San Enrique, Iloilo, often featured alongside the town\'s famous kalamay as a staple \"pang yam-is\" (sweet) merienda. These sticky, chewy, and brown or black treats are typically served with freshly grated coconut. Made from all-purpose flour or tapioca starch, brown sugar, lye water, and annatto water, steamed for 30-45 minutes.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(44, 4, 'Inday-Inday (Native Delicacy)', 'inday-inday-native-delicacy', 'A traditional Ilonggo native delicacy (kakanin) popular in San Enrique, Iloilo, and other parts of Western Visayas. A sweet, chewy snack made from poached glutinous rice dough discs, very similar to palitaw, but distinguished by its rich, caramelized coconut topping known as bukayo.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(45, 4, 'Kalamay Hati (Native Delicacy)', 'kalamay-hati-native-delicacy', 'A traditional Filipino sweet rice cake and a celebrated product of San Enrique, Iloilo, featured during their annual town festival. A very sticky, thick, and chewy delicacy made from ground glutinous rice, coconut milk, and muscovado or brown sugar, commonly topped with latik (coconut milk curds).', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 1, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(46, 4, 'Minatamis na Saging (Native Delicacy)', 'minatamis-na-saging', 'A staple Filipino dessert and merienda popular throughout Iloilo, including San Enrique, often served as a simple treat, a topping for shaved ice (Saba con Yelo), or an ingredient in halo-halo. Consists of ripe Saba bananas simmered until tender in a thickened, caramelized brown sugar syrup, sometimes enhanced with pandan leaves, vanilla, or salt to balance the sweetness.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(47, 4, 'Suman (Native Delicacy)', 'suman-native-delicacy', 'A popular native delicacy (kakanin) in San Enrique, Iloilo, known for its deep brown color, sticky texture, and rich flavor. A traditional Ilonggo suman typically wrapped in banana leaves and may come topped with latik (coconut caramel) or served plain, often prepared by local makers, including seniors who have learned the traditional process.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(48, 4, 'Bibingka (Native Delicacy)', 'bibingka-native-delicacy', 'A traditional, soft, and slightly sweet rice cake from San Enrique, Iloilo, cooked over live charcoal in banana leaf-lined molds. Known for its aromatic aroma, this native delicacy is typically made from rice flour (or soaked glutinous rice), coconut milk, sugar, eggs, and topped with cheese, salted eggs, or shredded coconut.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(49, 4, 'Baye-Baye (Native Delicacy)', 'baye-baye-native-delicacy', 'A popular native delicacy in the Visayas region, particularly in Iloilo. A well-loved Ilonggo treat found in various parts of the region, including areas like San Enrique.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(50, 4, 'Muasi (Native Delicacy)', 'muasi-native-delicacy', 'A popular, traditional Ilonggo delicacy often found in San Enrique and throughout Iloilo. Recognized as an Ilonggo version of palitaw, consisting of soft, chewy dumplings made from glutinous rice, commonly topped or stuffed with toasted sesame seeds and sugar.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(51, 4, 'Ibos / Dali-Dalin (Native Delicacy)', 'ibos-dali-dalin-native-delicacy', 'A type of sticky rice (glutinous rice) wrapped in coconut or buri leaves, popularly called Ibos. Best enjoyed dipped in sugar.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Daily in local markets', 'Market price', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(52, 3, 'Simboryo ng Barangay Quinolpan', 'simboryo-ng-barangay-quinolpan', 'The simboryo in Barangay Quinolpan stands as a peaceful landmark that reflects the barangay&#039;s deep spiritual roots and close-knit community. Modest yet meaningful, it serves as a place where locals gather for quiet prayer and reflection. Surrounded by open fields and fresh air, the simboryo mirrors the simplicity and strong faith of the people, preserving their traditions through time.', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', '', '', '', 10.91780000, 122.88450000, '', '[]', '', 'Open daily', 'Free', '', 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-24 08:55:09'),
(53, 3, 'Simboryo ng Barangay Cubay', 'simboryo-cubay', 'In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.', 'Barangay Cubay, San Enrique, Iloilo', 'Cubay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(54, 3, 'Simboryo ng Barangay Camiri', 'simboryo-camiri', 'The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay\'s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community\'s strong spiritual connection and respect for their history.', 'Barangay Camiri, San Enrique, Iloilo', 'Camiri', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 1, 0.00, '2026-03-22 14:09:14', '2026-03-23 16:31:09'),
(55, 3, 'Simboryo ng Barangay Rumagayray', 'simboryo-rumagayray', 'In Barangay Rumagayray, the simboryo holds a special place in the hearts of its people. Known for its weathered beauty, it represents not only faith but also the strength and unity of the community. It serves as a gathering point for reflection and remembrance, carrying with it the stories and prayers of generations.', 'Barangay Rumagayray, San Enrique, Iloilo', 'Rumagayray', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 'Open daily', 'Free', NULL, 'active', 0, 0, 0.00, '2026-03-22 14:09:14', '2026-03-22 14:09:14'),
(56, 3, 'Simboryo ng Barangay Dumiles', 'simboryo-ng-barangay-dumiles', 'The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay&amp;amp;amp;#039;s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.', 'Barangay Dumiles, San Enrique, Iloilo', 'Dumiles', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1PvwMc6fi8j2Y6KGqkTrtOVT4JP1soIcz', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1qiQViMSGw454VoNxLNfrv2E8_c2HBxdY\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1efwZ-NxzPxk1Sjw5C17ZnkEfzFGHGsMw\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1SyZeW4Pl9OgLFGHUFlCCW4FqZATHfJuQ\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jA-pv1qPg8Yrmn_ncS_knJpLh2giExNc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1sxZkzJJZ_DT3d9DD1ZGtYkZ2uuqawfqD\"]', '', 'Open daily', 'Free', '', 'active', 0, 1, 0.00, '2026-03-22 14:09:14', '2026-03-22 15:01:22'),
(57, 6, 'Mt. Bayuso', 'mt-bayuso', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1YF7ejJvzeh4qCoaENAxzpm_bVO8AiGq4', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1FhIs_yCK0k_vPbrG4kly-z47wrZCUxQr\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1d4Ao81G9umVevvuFade54MF4IDhpKpDP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1unOrWlW3XP4u_WbTpL9hkuo0OByzd64c\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1qIP2wctAvBaMaVVcok_qEA6EXUPJJ8aC\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1oSs7X4MuLKFAdfT-DU4g7lRUvv0Eioyr\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1s6YA6iqqLPqcqVoHNHrAoSstcsVorjUP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1OnL95lbW819mF7c-ajYn0pkD5WorUJgS\"]', '', '', '', '', 'active', 0, 0, 0.00, '2026-03-22 15:07:06', '2026-03-22 15:07:06'),
(58, 6, 'Hacienda Pavilion', 'hacienda-pavilion', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1C_ifiPtcKIpAusFxksj3Jqk_2vPC8deq', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/16S54JI_3FXJr9O_t7qescaRB7d3t2H8-\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lrSeeHaaVa4uODwJ3lVaQQrEYOUMASCN\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1FS9FlHao9iuK0JAnIV7NVbz2LkrU-oiM\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lnChKrCZ_XKZLPjU_aAcoz3UANMKfBU6\"]', '', '', '', '', 'active', 0, 0, 0.00, '2026-03-22 15:08:52', '2026-03-24 09:35:46'),
(59, 1, 'BCs Resort', 'bcs-resort', '', '', '', '', '', '', 11.07595169, 122.66322255, 'https://lh3.googleusercontent.com/d/17ZXy8WGIvHf_uwtmk5sekwd4r6wX4nFu', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1-FYNCi1f1Pej5RMhjDPijNBGIU4JecJC\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1yUZCmnHRmwMIdBaAe6B0PwJx5CfBPHyP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ofIuSMMIG71rmCEiB9GHoQAvXwdoVJS7\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1UJGvhQDXOtsGsRemeaPxK-vFN7emVG9j\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1EjD_0TY8o2SlyCjug3OJbBu6z_KAxBPN\"]', '', '', '', '', 'active', 0, 0, 0.00, '2026-03-22 15:21:05', '2026-03-24 11:47:15'),
(60, 3, 'Artifacts', 'artifacts', '', '', '', '', '', '', 10.91780000, 122.88450000, 'https://lh3.googleusercontent.com/d/1ANSA1C7-BiNJFVMPiB5s_C8x4wrW8IUb', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1XZ-EbThSle2mcch4X4EXRoYmRZU_2OUg\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jBrj0uKTS-aEhmu7fxUFXzdTXKX-KZ4w\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1USxwn50H_QCT75WbbnCYpRIZAWOgo8Am\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1a4Jq_M4NCrtZ-lpKB_aRE9O-PC-RZZo8\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BJeaohmcVtQvPKb7y0mtjtoGrZCWN_qc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/15rPqGEse4Xroy4UgbFUTLOKbgvNeGfdj\"]', '', '', '', '', 'active', 0, 1, 0.00, '2026-03-22 15:26:55', '2026-03-24 11:37:09');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
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
  `reviewer_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
