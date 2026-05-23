-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql310.infinityfree.com
-- Generation Time: Apr 26, 2026 at 01:42 AM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22



CREATE DATABASE IF NOT EXISTS san_enrique_tourism CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE san_enrique_tourism;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41458637_san_enrique_tourism`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) DEFAULT NULL,
  `role` enum('superadmin','admin') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@sanenrique.gov.ph', '$2y$10$P4Dz4E7UyhMZCgrWpai/iONWPdFLenH2DtfOxRDUJnE4jy3WpsQem', 'LGU Administrator', 'superadmin', 1, NULL, '2026-03-22 14:09:14'),
(2, 'Ken', 'kendorylpagdato@gmail.com', '$2y$10$EDjGnm/bvw.GwCdVXAEvtOU9qdpIbO6dYXX8iqWMTORI/sW3/iH5e', 'Ken Doryl Pagdato', 'admin', 1, NULL, '2026-03-31 00:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-map-marker-alt',
  `color` varchar(20) DEFAULT '#2d6a4f',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `color`, `created_at`) VALUES
(1, 'Resorts', 'resorts', 'fas fa-umbrella-beach', '#2d6a4f', '2026-03-22 14:09:14'),
(2, 'Barangays', 'barangays', 'fas fa-home', '#52b788', '2026-03-22 14:09:14'),
(3, 'Cultural Sites', 'cultural', 'fas fa-landmark', '#b7791f', '2026-03-22 14:09:14'),
(4, 'Local Foods', 'local-foods', 'fas fa-utensils', '#e63946', '2026-03-22 14:09:14'),
(5, 'Agri-Tourism & Farms', 'farms', 'fas fa-seedling', '#40916c', '2026-03-22 14:09:14'),
(6, 'Nature & Adventure', 'nature', 'fas fa-mountain', '#1b4332', '2026-03-22 14:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `end_date`, `location`, `latitude`, `longitude`, `image`, `status`, `is_pinned`, `created_at`) VALUES
(1, 'San Enrique Kapistahan', 'Annual town patronal fiesta. Celebrated in honor of Their Patroness, Mary, Help Of Christians every May 18-24.', '2025-05-18', '2025-07-24', 'Poblacion Plaza, San Enrique', '0.00000000', '0.00000000', '', 'active', 1, '2026-03-22 14:09:14'),
(4, 'Kasalan sa Banwa', 'The Kasalan sa Banwa is a heartwarming mass wedding ceremony that celebrates love, unity, and family within the community of San Enrique. This special event provides couples—especially those who may not have had the opportunity before—a chance to be legally and ceremonially wed. More than just a wedding, it reflects the town’s commitment to strengthening families and promoting social welfare. Held in a festive and supportive atmosphere, the event highlights the value of commitment and the importance of building a strong, united community.', '0001-03-27', '0001-12-16', 'Poblacion Plaza, San Enrique', '0.00000000', '0.00000000', '', 'active', 1, '2026-03-26 13:58:14'),
(5, 'KALAYAAN (Independence Day) Celebration', 'To Commemorate The Independence of The Republic Of The Philippines.', '2026-06-12', '2026-06-12', '', '0.00000000', '0.00000000', '', 'active', 1, '2026-03-27 06:53:56'),
(6, 'Foundation Day and  Kalamay Festival', 'The Kalamay Festival is a lively cultural celebration that honors San Enrique’s rich heritage and its famous delicacy, kalamay—a sweet product derived from sugarcane. \r\nA major highlight of the festival is the Tribe Competition, where different groups—Hubon Mamumugon, Hubon Sakada, Hubon Muscovado, Hubon Biraho, and Hubon Kampunero—perform energetic street dances and theatrical presentations. Each “hubon” represents aspects of the town’s history, livelihood, and culture, particularly its deep connection to the sugar industry. Through colorful costumes, rhythmic music, and storytelling, the competition brings to life the identity and traditions of San Enrique.\r\nThis festival is not only a celebration of food but also a tribute to the hardworking people behind the sugar industry, showcasing unity, creativity, and pride in local culture. It transforms the town into a vibrant stage of culture, making it a true reflection of San Enrique’s heritage and festive spirit', '2026-07-05', '2026-07-13', '', '0.00000000', '0.00000000', '', 'active', 1, '2026-03-28 15:15:25'),
(7, 'Paskwa sa Banwa', 'Paskwa sa Banwa is San Enrique’s vibrant celebration of the Christmas season, filled with lights, joy, and community spirit. The event usually features colorful decorations, lighting ceremonies, parades, and festive programs that bring people together in celebration. It symbolizes hope, unity, and generosity, as the entire town participates in spreading holiday cheer. Through cultural presentations, bazaars, and gatherings, this event showcases the warmth and strong sense of togetherness among the people of San Enrique, making it one of the most anticipated celebrations of the year. One of the Highlights and the most anticipated event  of this celebration is The Daigon Contest Participated by all of the Barangays in the Municipality, started in 1980’s up to now.', '2026-12-01', '2026-12-25', '', '0.00000000', '0.00000000', '', 'active', 1, '2026-03-28 15:16:39');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `featured_image` varchar(500) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `video` varchar(512) DEFAULT '',
  `operating_hours` varchar(255) DEFAULT NULL,
  `entrance_fee` varchar(100) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'active',
  `is_featured` tinyint(1) DEFAULT 0,
  `views` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `category_id`, `name`, `slug`, `description`, `address`, `barangay`, `contact`, `email`, `website`, `latitude`, `longitude`, `featured_image`, `gallery`, `video`, `operating_hours`, `entrance_fee`, `amenities`, `status`, `is_featured`, `views`, `rating`, `created_at`, `updated_at`) VALUES
(3, 3, 'Mary Help of Christians Parish', 'mary-help-of-christians-parish', 'The historic Saint Enrique Parish Church, a centuries-old architectural marvel that stands as a testament to the rich cultural heritage of the municipality.', 'Poblacion, San Enrique', 'Poblacion', '', '', '', '11.07294038', '122.65644729', 'https://lh3.googleusercontent.com/d/1lcRZlKw4-LNy7ok1lvallBeNKY-cBqUz', '[]', '', 'Open Daily', 'Free', '', 'active', 0, 3, '0.00', '2026-03-22 14:09:14', '2026-03-31 05:13:00'),
(7, 2, 'Barangay Abaca', 'barangay-abaca', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Abaca, San Enrique, Iloilo', 'Abaca', '', '', 'https://www.facebook.com/profile.php?id=61553757301737', '11.12857698', '122.71505688', 'https://lh3.googleusercontent.com/d/1gl3LLyeFmp_6SNoI5spY394jg7gbnIZx', '[]', '', 'Open 24 hours', '', '', 'active', 0, 20, '0.00', '2026-03-22 14:09:14', '2026-03-30 08:30:26'),
(8, 2, 'Barangay Asisig', 'barangay-asisig', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Asisig, San Enrique, Iloilo', 'Asisig', '', '', 'https://www.facebook.com/groups/223302368894780', '11.10568862', '122.67145496', 'https://lh3.googleusercontent.com/d/15QuC_8lPcbjVeY7HBFFOAbduXO-I3hPU', '[]', '', 'Open 24 hours', '', '', 'active', 0, 3, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:13:36'),
(9, 2, 'Barangay Bantayan', 'barangay-bantayan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Bantayan, San Enrique, Iloilo', 'Bantayan', '', '', '', '11.08706339', '122.69278188', 'https://lh3.googleusercontent.com/d/1SQhwXQSKYJwxZJhzatDEh5XiFI5KCdoe', '[]', '', 'Open 24 hours', '', '', 'active', 0, 4, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:14:28'),
(10, 2, 'Barangay Braulan', 'barangay-braulan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Braulan, San Enrique, Iloilo', 'Braulan', '', '', 'https://www.facebook.com/profile.php?id=61553682964106', '11.13591262', '122.73798886', 'https://lh3.googleusercontent.com/d/1cxjrRf4JE48Y3hY7l614QwpdANjSpfKM', '[]', '', 'Open 24 hours', '', '', 'active', 0, 2, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:14:40'),
(11, 2, 'Barangay Cabugao Nuevo', 'barangay-cabugao-nuevo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Nuevo, San Enrique, Iloilo', 'Cabugao Nuevo', '', '', '', '11.07133335', '122.67173606', 'https://lh3.googleusercontent.com/d/1QVTs4XMGT6X6EBsUXIqcsw63Gqu0IWvf', '[]', '', 'Open 24 hours', '', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:14:50'),
(12, 2, 'Barangay Cabugao Viejo', 'barangay-cabugao-viejo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Cabugao Viejo, San Enrique, Iloilo', 'Cabugao Viejo', '', '', 'https://www.facebook.com/groups/213430483312531', '11.07205077', '122.66318188', 'https://lh3.googleusercontent.com/d/181t3ikUdOy0bmSIzGn0Sx2cPwYuhEnI6', '[]', '', 'Open 24 hours', '', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:15:06'),
(13, 2, 'Barangay Camiri', 'barangay-camiri', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay&amp;#039;s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community&amp;#039;s strong spiritual connection and respect for their history.', 'Barangay Camiri, San Enrique, Iloilo', 'Camiri', '', '', 'https://www.facebook.com/profile.php?id=61565832921860', '11.08264153', '122.65384795', 'https://lh3.googleusercontent.com/d/1Bk7stSj8hqT2M0p1XnmrX1hZbB5dbPYK', '[]', '', 'Open 24 hours', '', '', 'active', 0, 2, '0.00', '2026-03-22 14:09:14', '2026-03-30 15:50:21'),
(14, 2, 'Barangay Compo', 'barangay-compo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Compo, San Enrique, Iloilo', 'Compo', '', '', 'https://www.facebook.com/profile.php?id=100066410029706', '11.05672006', '122.65896291', 'https://lh3.googleusercontent.com/d/1PF24dubB3njOX7RFCt2a1z5reU6nWBCO', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:13:33'),
(15, 2, 'Barangay Catan-Agan', 'barangay-catan-agan', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Catan-Agan, San Enrique, Iloilo', 'Catan-Agan', '', '', 'https://www.facebook.com/profile.php?id=100063789806327', '11.12410319', '122.74969667', 'https://lh3.googleusercontent.com/d/1OYHtcwxN6XDMRcoMcHfQ3eMLxY3hHPLs', '[]', '', 'Open 24 hours', '', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-26 00:19:57'),
(16, 2, 'Barangay Cubay', 'barangay-cubay', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.', 'Barangay Cubay, San Enrique, Iloilo', 'Cubay', '', '', 'https://www.facebook.com/profile.php?id=61556987704834', '11.08120345', '122.70823632', 'https://lh3.googleusercontent.com/d/16p_l5XHDQRgLeBocpotlsKBw9QtTgGLm', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:15:08'),
(17, 2, 'Barangay Dacal', 'barangay-dacal', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Dacal, San Enrique, Iloilo', 'Dacal', '', '', 'https://www.facebook.com/profile.php?id=61556520538650', '11.11524601', '122.70374866', 'https://lh3.googleusercontent.com/d/1Wun5bHOp54-FshtemKzPIBwKCYzLGpPN', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:15:36'),
(18, 2, 'Barangay Dumiles', 'barangay-dumiles', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality. The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangay&amp;#039;s traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.', 'Barangay Dumiles, San Enrique, Iloilo', 'Dumiles', '', '', 'https://www.facebook.com/profile.php?id=100078247682899', '11.09997670', '122.70950057', 'https://lh3.googleusercontent.com/d/1o_C5K_rUgnf-vtS2Wnxq9ZEC_q1MTDtt', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:18:15'),
(19, 2, 'Barangay Garita', 'barangay-garita', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Garita, San Enrique, Iloilo', 'Garita', '', '', 'https://www.facebook.com/profile.php?id=61558183211226', '11.08169824', '122.73195189', 'https://lh3.googleusercontent.com/d/12ECATm1e-mea778IvXYjituXdkFowD3L', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:20:21'),
(20, 2, 'Barangay Gines Nuevo', 'barangay-gines-nuevo', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Gines Nuevo, San Enrique, Iloilo', 'Gines Nuevo', '', '', 'https://www.facebook.com/profile.php?id=100078221154180', '11.09814518', '122.68014805', 'https://lh3.googleusercontent.com/d/1BVwGq9KDfhwm_BT7ARteck1m71_0G3SE', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:21:00'),
(21, 2, 'Barangay Imbang Pequeño', 'barangay-imbang-peque-o', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbang Pequeño, San Enrique, Iloilo', 'Imbang Pequeño', '', '', 'https://www.facebook.com/profile.php?id=61553163435790', '11.09166250', '122.65278398', 'https://lh3.googleusercontent.com/d/1nBEh3Iy4WL3PztWlGSgOsQTKz5giZh80', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:23:29'),
(22, 2, 'Barangay Imbesad-an', 'barangay-imbesad-an', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Imbesad-an, San Enrique, Iloilo', 'Imbesad-an', '', '', 'https://www.facebook.com/profile.php?id=61553557508530', '11.07212201', '122.69250297', 'https://lh3.googleusercontent.com/d/1Dv9EdLFDtVshAUFUY5YVhD_EF_-7k40f', '[]', '', 'Open 24 hours', '', '', 'active', 0, 2, '5.00', '2026-03-22 14:09:14', '2026-03-31 06:19:30'),
(23, 2, 'Barangay Iprog', 'barangay-iprog', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Iprog, San Enrique, Iloilo', 'Iprog', '', '', 'https://www.facebook.com/profile.php?id=61553154336493', '11.14831365', '122.75498496', 'https://lh3.googleusercontent.com/d/1SWiiUQmDvpADn85vYDBy1-_AeCn_Ys_k', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1DcklEI1R0gdAJkOJtK6gYmUOWKQozmqs\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1yQBOCZBvxCkCmIQDawaAw-BsCjDGrLBY\"]', '', 'Open 24 hours', '', '', 'active', 0, 2, '0.00', '2026-03-22 14:09:14', '2026-03-31 06:16:21'),
(24, 2, 'Barangay Lip-ac', 'barangay-lip-ac', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Lip-ac, San Enrique, Iloilo', 'Lip-ac', '', '', 'https://www.facebook.com/profile.php?id=61553035064866', '11.06866676', '122.68109716', 'https://lh3.googleusercontent.com/d/1-FpEkhJLvB4rYBU3pIlu6lj2M505O1rV', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:27:23'),
(25, 2, 'Barangay Madarag', 'barangay-madarag', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Madarag, San Enrique, Iloilo', 'Madarag', '', '', 'https://www.facebook.com/kasimaryo.sang.madarag', '11.14531400', '122.77727337', 'https://lh3.googleusercontent.com/d/1tL4ZyWpyRuEvpLuvcVRzHj4EVUAS5UGc', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:27:55'),
(26, 2, 'Barangay Mapili', 'barangay-mapili', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Mapili, San Enrique, Iloilo', 'Mapili', '', '', 'https://www.facebook.com/profile.php?id=61553671329335', '11.10250639', '122.74632353', 'https://lh3.googleusercontent.com/d/1LD8NwB13QwGiuRcZAsCmjRzwt5MWqJ2v', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1KEETAf6ukAKrqqanV4oYqTXIm5UE8oHa\"]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:28:43'),
(27, 2, 'Barangay Paga', 'barangay-paga', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Paga, San Enrique, Iloilo', 'Paga', '', '', 'https://www.facebook.com/profile.php?id=61583143784931', '11.08412041', '122.67577138', 'https://lh3.googleusercontent.com/d/1W9H4ugN565epT9U2GUv2CjD0KOG1MULS', '[]', '', 'Open 24 hours', '', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:41:00'),
(28, 2, 'Barangay Palje', 'barangay-palje', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Palje, San Enrique, Iloilo', 'Palje', '', '', '', '11.05658410', '122.66858714', 'https://lh3.googleusercontent.com/d/1k-SAc2b9t6_78hzeB5vjMrzyhQHvSooq', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:29:30'),
(29, 2, 'Barangay Poblacion Ilawod', 'barangay-poblacion-ilawod', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilawod, San Enrique, Iloilo', 'Poblacion Ilawod', '', '', 'https://www.facebook.com/profile.php?id=100086784212137', '11.06985974', '122.65527262', 'https://lh3.googleusercontent.com/d/14eBNpCB5GZhO59CfMyfH8UWes05mfrji', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:30:51'),
(30, 2, 'Barangay Poblacion Ilaya', 'barangay-poblacion-ilaya', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Poblacion Ilaya, San Enrique, Iloilo', 'Poblacion Ilaya', '', '', 'https://www.facebook.com/SKPoblacionIlaya', '11.07534964', '122.65771193', 'https://lh3.googleusercontent.com/d/1FAnx_tnZd2RfibmDEaZ62BDzbwJMIPi6', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:32:21'),
(31, 2, 'Barangay Quinolpan', 'barangay-quinolpan', 'Rural agricultural community', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', '', '', 'https://www.facebook.com/barangayquinolpansanenriqueiloilophilippines5036', '11.10277981', '122.69413118', 'https://lh3.googleusercontent.com/d/1c7tOTPS4E9rEm-XfA6vlnE8txmYMUOUk', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:33:14'),
(32, 2, 'Barangay Rumagayray', 'barangay-rumagayray', 'Small barangay with limited development share. \r\n‎', 'Barangay Rumagayray, San Enrique, Iloilo', 'Rumagayray', '', '', 'https://www.facebook.com/groups/1556000491533439', '11.04405158', '122.64825408', 'https://lh3.googleusercontent.com/d/1YlOJhb_Bjxz5kkini2N6gwz-bNnQ1-qk', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:34:03'),
(33, 2, 'Barangay San Antonio', 'barangay-san-antonio', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay San Antonio, San Enrique, Iloilo', 'San Antonio', '', '', '', '11.12558098', '122.77806342', 'https://lh3.googleusercontent.com/d/1C0hnFiOzU68wVntr2L4SGUzLyM4I3yyR', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:35:05'),
(34, 2, 'Barangay Tambunac', 'barangay-tambunac', 'One of the barangays of San Enrique, Iloilo. A close-knit rural community contributing to the agricultural and cultural heritage of the municipality.', 'Barangay Tambunac, San Enrique, Iloilo', 'Tambunac', '', '', 'https://www.facebook.com/barangay.tambunac', '11.07454366', '122.68047106', 'https://lh3.googleusercontent.com/d/1y4KKL1il7H8HWNBtJfrzgu2ZMYHRgR2s', '[]', '', 'Open 24 hours', '', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-24 13:35:29'),
(35, 5, 'Gumbans Amat Amat Farm', 'gumbans-amat-amat-farm', 'Gumbans Amat Amat Farm is a peaceful farm offering fresh produce and scenic views. Enjoy their fruit trees, fish pond, and vegetable garden. Enjoyable views include fruit trees (peach, lemon, avocado, coconut), fish pond, and vegetable garden.', 'Sitio Gelonoc Cubay, San Enrique, Iloilo', 'Cubay', '09627603707 / 09455067648', '', 'https://www.facebook.com/share1DHRHN9kbMn/?mibextid=wwXlfr', '11.07570800', '122.71619900', 'https://lh3.googleusercontent.com/d/1uaYxkPouG_dWftb9hcdaFfrhSLojdKLG', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1IW48uk2mMRw_V9t1GBlAJFZ2sqK734yr&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1t3SUhrRtzoDgU0LhzJ01e6guYK1vsH9j&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=10DbRpy4JVFF2pK1nam8yPp1all38hrCr&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1GkZTMhPzaJjA_Hb9jxcRTq4XhFYRQgFe&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1zKo-gTVb_LudmmV6zwsz-sTLuQmX8UWT&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=16xFi86vZSSb_jojTIQmSBc5XLZhUVJ9n&sz=w1200\"]', '', 'Monday to Friday: 8:00 AM - 5:00 PM; Saturday and Sunday: 9:00 AM - 5:00 PM', 'Contact for rates', 'Fruit trees (peach, lemon, avocado, coconut), Fish pond, Vegetable garden', 'active', 0, 22, '0.00', '2026-03-22 14:09:14', '2026-03-31 03:58:00'),
(38, 5, 'Tonys Farm Resort', 'tonys-farm-resort', 'Tonys Valley Farm Resort is a private resort offering a peaceful atmosphere with swimming pools and cottages. Enjoyable views include lush greenery, swimming pools, and scenic surroundings. Activities: swimming, picnic, and nature walks.', 'Sitio Layog Bato, Brgy. Mapili, San Enrique, Iloilo', 'Mapili', '0908 866 6909', '', 'https://facebook.com/TVFRM', '11.10524189', '122.74732590', 'https://lh3.googleusercontent.com/d/1im3ZlRXO7ii_KD2BSfv06e6M9FJOe94J', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=117MzqUHHBuOSq0_xkM_EJn_B8tpRNqGr&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=15AU4AYUXkLrSYhcjy4OR5JPYSTJHaSCY&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1rMd2jA-BPZJp3Hw9wbxkoJL8T6fX6Ikv&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1ZSuQsVMts2dwPTtJqsPsoe0j1M0dx0ph&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1mmblj7BmZVrhqkaWVo7SdOEV5yIJkRqu&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1tUD02J6Ln8sRFatJjqoId-PYUJwTsrNp&sz=w1200\"]', '', 'Daily (contact for specific hours)', 'Contact for rates', 'Swimming pools, Cottages, Picnic area, Nature walks, Lush greenery', 'active', 1, 19, '0.00', '2026-03-22 14:09:14', '2026-03-31 01:25:32'),
(39, 6, 'San Antonio San Enrique River', 'san-antonio-san-enrique-river', 'The San Antonio San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.', 'San Antonio, San Enrique, Iloilo', 'San Antonio', '', '', '', '11.13103180', '122.76720200', 'https://lh3.googleusercontent.com/d/1Tk9xlfh6LCo7blrbYy4gwwEREKVHH4Xp', '[]', '', '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM', 'Free', 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-31 06:32:12'),
(40, 6, 'Catan-Agan San Enrique River', 'catan-agan-san-enrique-river', 'The Catan-Agan San Enrique River offers a serene spot for nature lovers, fishing, and relaxation. Enjoy the scenic views and tranquility. Activities: fishing, swimming, picnic, and nature walks.', 'Catan-Agan, San Enrique, Iloilo', 'Catan-Agan', '', '', '', '11.12440940', '122.74875966', 'https://lh3.googleusercontent.com/d/1xJwMrLWubZyLyFkRMj0GAtVPGkeeFShZ', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1J0SUIT63FziBoAcCNzFbzqWji-wUJOja\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1wA_xLS_DGhGMQ0_hWUAvUovXAwc3FXO7\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/13Jq62X_UhiYG2genjcl-QvpfvnfEfFwp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/13eld7kjD7OlTpsd0bjIWpY0l4NLaq08W\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1dlMdr8MkLoHmAwZbm2djfejCyKe_zkqR\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1LWJvKKjzIoIC-M6FmIBIIuNZbq5SVucn\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1lxLoslb7z6EMSkeTbkUI_tYC3V1dEoLp\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1zzf_30KnT3uLs_sF-Bf3VFzMnjVkaVzo\"]', '', '24/7 (river access); Best time to visit: 6:00 AM - 5:00 PM', 'Free', 'Fishing, Swimming, Picnic area, Nature walks, Scenic river views', 'active', 1, 7, '0.00', '2026-03-22 14:09:14', '2026-04-23 02:57:53'),
(41, 4, 'Puto (Native Delicacy)', 'puto-native-delicacy', 'A famous Filipino steamed rice cake. In San Enrique, Puto is traditionally sold by vendors outside the church or inside the market, especially after Sunday Mass. Best prepared and paired with Dinuguan.', 'Local Markets &amp;amp; Church Vicinity, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1jH7vN_zU30GU9JKkb8fwUXFfWHN9OpNW&sz=w1200', '[]', '', 'Sundays (after Mass) and daily in local markets', 'Market price', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-30 15:30:20'),
(42, 4, 'Kutsinta (Native Delicacy)', 'kutsinta-native-delicacy', 'A popular steamed rice cake in San Enrique, Iloilo, often featured alongside the town&amp;#039;s famous kalamay as a staple &amp;quot;pang yam-is&amp;quot; (sweet) merienda. These sticky, chewy, and brown or black treats are typically served with freshly grated coconut. Made from all-purpose flour or tapioca starch, brown sugar, lye water, and annatto water, steamed for 30-45 minutes.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=17J3QnUoyYyECtfZ5VE1suFQS6IagDivD&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-30 06:56:51'),
(44, 4, 'Inday-Inday (Native Delicacy)', 'inday-inday-native-delicacy', 'A traditional Ilonggo native delicacy (kakanin) popular in San Enrique, Iloilo, and other parts of Western Visayas. A sweet, chewy snack made from poached glutinous rice dough discs, very similar to palitaw, but distinguished by its rich, caramelized coconut topping known as bukayo.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1TOU_rvuLRthmSFocbGTWHrL_dxqgV1d1&sz=w1200', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1EVc-KD8VjvmZP7Sfjtk1ldJl1VBvO5M0&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1EVc-KD8VjvmZP7Sfjtk1ldJl1VBvO5M0&sz=w1200\"]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-25 07:38:04'),
(45, 4, 'Kalamay Hati (Native Delicacy)', 'kalamay-hati-native-delicacy', 'A traditional Filipino sweet rice cake and a celebrated product of San Enrique, Iloilo, featured during their annual town festival. A very sticky, thick, and chewy delicacy made from ground glutinous rice, coconut milk, and muscovado or brown sugar, commonly topped with latik (coconut milk curds).', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1KLxBcaDEH2fwunVmusM6VA-867FWxM7u&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 6, '0.00', '2026-03-22 14:09:14', '2026-03-31 04:41:47'),
(46, 4, 'Minatamis na Saging (Native Delicacy)', 'minatamis-na-saging-native-delicacy', 'A staple Filipino dessert and merienda popular throughout Iloilo, including San Enrique, often served as a simple treat, a topping for shaved ice (Saba con Yelo), or an ingredient in halo-halo. Consists of ripe Saba bananas simmered until tender in a thickened, caramelized brown sugar syrup, sometimes enhanced with pandan leaves, vanilla, or salt to balance the sweetness.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1eISy9Y_I5cIfWa2zpcca8ydUfKDZP-rM&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:39:14'),
(47, 4, 'Suman (Native Delicacy)', 'suman-native-delicacy', 'A popular native delicacy (kakanin) in San Enrique, Iloilo, known for its deep brown color, sticky texture, and rich flavor. A traditional Ilonggo suman typically wrapped in banana leaves and may come topped with latik (coconut caramel) or served plain, often prepared by local makers, including seniors who have learned the traditional process.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1yBmYj08lx8xmUUdC6BRpqT1eh7sm9dYU&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-27 06:27:10'),
(48, 4, 'Bibingka (Native Delicacy)', 'bibingka-native-delicacy', 'A traditional, soft, and slightly sweet rice cake from San Enrique, Iloilo, cooked over live charcoal in banana leaf-lined molds. Known for its aromatic aroma, this native delicacy is typically made from rice flour (or soaked glutinous rice), coconut milk, sugar, eggs, and topped with cheese, salted eggs, or shredded coconut.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1gKuDXlDY0w4hpgXyh75YX-YD_--3Lcx7&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-28 10:39:51'),
(49, 4, 'Baye-Baye (Native Delicacy)', 'baye-baye-native-delicacy', 'A popular native delicacy in San Enrique, from Barangay Poblacion Ilawod. A well-loved San Enriquenhon treat made from grounded corn, sugar, and coconut. It is always served during Pista Mais Festival of the said barangay, every month of August.', 'Local Markets, San Enrique, Iloilo', 'Poblacion Ilawod', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1FThaPXvB-Wc0Uy5gPT2d65nuUwX-WtH3&sz=w1200', '[]', '', 'For order.', '150/Tub', '', 'active', 0, 3, '0.00', '2026-03-22 14:09:14', '2026-03-27 06:34:40'),
(50, 4, 'Muasi (Native Delicacy)', 'muasi-native-delicacy', 'A popular, traditional Ilonggo delicacy often found in San Enrique and throughout Iloilo. Recognized as an Ilonggo version of palitaw, consisting of soft, chewy dumplings made from glutinous rice, commonly topped or stuffed with toasted sesame seeds and sugar.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1y7nWn87p3mghZz-y7Mo-Xb1HzdLuqDED&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-25 07:16:42'),
(51, 4, 'Ibos / Dali-Dalin (Native Delicacy)', 'ibos-dali-dalin-native-delicacy', 'A type of sticky rice (glutinous rice) wrapped in coconut or buri leaves, popularly called Ibos. Best enjoyed dipped in sugar.', 'Local Markets, San Enrique, Iloilo', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1kOAVK3hqL4bt9CjgD8-epGvS9ZyVofVj&sz=w1200', '[]', '', 'Daily in local markets', 'Market price', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-25 07:17:13'),
(52, 3, 'Simboryo ng Barangay Quinolpan', 'simboryo-ng-barangay-quinolpan', 'The simboryo in Barangay Quinolpan stands as a peaceful landmark that reflects the barangay&amp;amp;#039;s deep spiritual roots and close-knit community. Modest yet meaningful, it serves as a place where locals gather for quiet prayer and reflection. Surrounded by open fields and fresh air, the simboryo mirrors the simplicity and strong faith of the people, preserving their traditions through time.', 'Barangay Quinolpan, San Enrique, Iloilo', 'Quinolpan', '', '', '', '11.10297512', '122.69392528', 'https://drive.google.com/thumbnail?id=1FpAWhG3yeYO7oQm1V-uVZqvvFdVkP08l&sz=w1200', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1jN5d0BuRelOoNFQ38wKmn6pfbMA8Fk9b&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1SyZeW4Pl9OgLFGHUFlCCW4FqZATHfJuQ&sz=w1200\"]', '', 'Open daily', 'Free', '', 'active', 0, 0, '0.00', '2026-03-22 14:09:14', '2026-03-30 15:08:10'),
(53, 3, 'Simboryo ng Barangay Cubay', 'simboryo-ng-barangay-cubay', 'In Barangay Cubay, the simboryo rises as a symbol of unity and devotion. Its aged structure tells stories of shared beliefs and generations who have kept their faith alive. Often visited during moments of need or gratitude, it remains a sacred space that connects the past with the present, reminding the community of their enduring cultural identity.', 'Barangay Cubay, San Enrique, Iloilo', 'Cubay', '', '', '', '11.08164679', '122.70841494', 'https://drive.google.com/thumbnail?id=19JMq4NNApJIL5niytnOnVLKWJq9MH_lL&sz=w1200', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1efwZ-NxzPxk1Sjw5C17ZnkEfzFGHGsMw&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1o0aBDwIZdqaYSjfWBGbn4FAY1kZEWJCn&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1jA-pv1qPg8Yrmn_ncS_knJpLh2giExNc&sz=w1200\"]', '', 'Open daily', 'Free', '', 'active', 0, 1, '0.00', '2026-03-22 14:09:14', '2026-03-31 06:28:19'),
(54, 3, 'Simboryo ng Barangay Camiri', 'simboryo-ng-barangay-camiri', 'The simboryo of Barangay Camiri is a quiet yet striking reminder of the barangay&#039;s cultural heritage. Nestled within a serene environment, it stands as a witness to the passing of time and the resilience of local traditions. Its presence brings a sense of peace and continuity, reflecting the community&#039;s strong spiritual connection and respect for their history. And it is the most intact of all the simboryo on the list', 'Barangay Camiri, San Enrique, Iloilo', 'Camiri', '', '', '', '11.08163314', '122.65216343', 'https://drive.google.com/thumbnail?id=1t-8OrfAJ6TSZDiuMHbjt8QszELTvDB4X&sz=w1200', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1Tr8OcKIfNy_R--9J1jDRK6pBi8szrZYV&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1P9khlHiddAQx4COuzuLPsWUDe0PqrFfg&sz=w1200\"]', '', 'Open daily', 'Free', '', 'active', 0, 2, '0.00', '2026-03-22 14:09:14', '2026-03-31 06:27:25'),
(56, 3, 'Simboryo ng Barangay Dumiles', 'simboryo-ng-barangay-dumiles', 'The simboryo in Barangay Dumiles stands as a humble yet meaningful structure that embodies the barangays traditions and beliefs. Surrounded by nature, it creates a calm and reflective atmosphere for visitors. More than just a physical landmark, it symbolizes the enduring faith and cultural pride of the people of Dumiles.', 'Barangay Dumiles, San Enrique, Iloilo', 'Dumiles', '', '', '', '11.09951713', '122.70926029', 'https://lh3.googleusercontent.com/d/1PvwMc6fi8j2Y6KGqkTrtOVT4JP1soIcz', '[]', '', 'Open daily', 'Free', '', 'active', 0, 3, '0.00', '2026-03-22 14:09:14', '2026-03-30 13:41:28'),
(57, 6, 'Mt. Bayuso', 'mt-bayuso', 'Mount Bayuso is one of San Enrique’s hidden natural treasures, offering a refreshing escape for nature lovers and adventure seekers. Known for its lush greenery and scenic views, the mountain provides a perfect spot for hiking and outdoor exploration. At the summit, visitors are rewarded with breathtaking landscapes that showcase the beauty of the town and its surroundings. It represents the untouched and serene side of San Enrique, promoting eco-tourism and appreciation for nature.', '', 'Abaca', '', '', '', '11.13060000', '122.72810000', 'https://lh3.googleusercontent.com/d/1YF7ejJvzeh4qCoaENAxzpm_bVO8AiGq4', '[]', '', '', '', '', 'active', 1, 0, '0.00', '2026-03-22 15:07:06', '2026-03-31 03:58:24'),
(58, 6, 'Hacienda Pavilion', 'hacienda-pavilion', 'The Hacienda Pavilion is a charming venue in San Enrique that blends rustic elegance with the peaceful ambiance of the countryside. Surrounded by natural scenery, it serves as a perfect location for g\\\\r\\\\natherings, celebrations, and tourism activities. Its open-air design and spacious setting make it ideal for weddings, events, and community programs. The pavilion reflects the town’s appreciation for simple yet beautiful spaces where people can connect, celebrate, and enjoy nature.', '', 'Dumiles', '', '', '', '11.10057700', '122.70732800', 'https://lh3.googleusercontent.com/d/1C_ifiPtcKIpAusFxksj3Jqk_2vPC8deq', '[]', '', '', '', '', 'active', 0, 1, '0.00', '2026-03-22 15:08:52', '2026-03-31 06:45:56'),
(59, 1, 'BCs Resort', 'bcs-resort', 'BC Farm is a relaxing farm resort in San Enrique that combines rural charm with modern leisure. Featuring a swimming pool and open spaces, it is a great destination for family outings, team-building activities, and weekend getaways. Guests can enjoy the calm environment, fresh air, and simple farm life while still having access to recreational facilities. It highlights the town’s growing tourism industry, offering both relaxation and enjoyment in one place.', '', '', '', '', '', '11.07595169', '122.66322255', 'https://lh3.googleusercontent.com/d/17ZXy8WGIvHf_uwtmk5sekwd4r6wX4nFu', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1-FYNCi1f1Pej5RMhjDPijNBGIU4JecJC\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1yUZCmnHRmwMIdBaAe6B0PwJx5CfBPHyP\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1ofIuSMMIG71rmCEiB9GHoQAvXwdoVJS7\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1UJGvhQDXOtsGsRemeaPxK-vFN7emVG9j\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1EjD_0TY8o2SlyCjug3OJbBu6z_KAxBPN\"]', '', '', 'Contact for rates', '', 'active', 0, 3, '0.00', '2026-03-22 15:21:05', '2026-03-31 06:34:53'),
(60, 3, 'Artifacts', 'artifacts', 'The collection of artifacts in San Enrique showcases the rich cultural heritage and everyday life of the past generations. Each item tells a unique story about tradition, livelihood, and history:', '', '', '', '', '', '11.07215485', '122.65655483', 'https://lh3.googleusercontent.com/d/1ANSA1C7-BiNJFVMPiB5s_C8x4wrW8IUb', '[\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1XZ-EbThSle2mcch4X4EXRoYmRZU_2OUg\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1jBrj0uKTS-aEhmu7fxUFXzdTXKX-KZ4w\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1USxwn50H_QCT75WbbnCYpRIZAWOgo8Am\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1a4Jq_M4NCrtZ-lpKB_aRE9O-PC-RZZo8\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/1BJeaohmcVtQvPKb7y0mtjtoGrZCWN_qc\",\"https:\\/\\/lh3.googleusercontent.com\\/d\\/15rPqGEse4Xroy4UgbFUTLOKbgvNeGfdj\"]', '', '', '', '', 'active', 0, 15, '0.00', '2026-03-22 15:26:55', '2026-04-12 01:38:29'),
(61, 4, 'Linugaw Nga Pilit', 'linugaw-nga-pilit', 'It is one of the native sweet delicacy in San Enrique, usually being prepared during birthdays and other occasions in the highlands. It is made from glutenous rice, coconut milk, ripe jackfruit, with different root crops.', 'Anywhere in the Municipality', 'Poblacion', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1VgQnQ_te9_IY_KpcnjXoJ9Eb6NhdllEI&sz=w1200', '[]', '', 'Sundays for local market day', 'Market price', '', 'active', 0, 2, '0.00', '2026-03-27 06:38:49', '2026-03-31 02:16:24'),
(62, 4, 'Banana and Cassava Cake', 'banana-and-cassava-cake', 'Another sweet delicacy from the municipality, made from cassava roots, and ripe banana.', 'Poblacion Ilaya, beside Municipal Plaza', 'Poblacion Ilaya', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1gA0sSk3mj8tKNva_KaOZXQM8nSApPh8B&sz=w1200', '[\"https:\\/\\/scontent.fceb1-3.fna.fbcdn.net\\/v\\/t39.30808-6\\/657265589_122105553921072244_2729049142381042487_n.jpg?_nc_cat=106&ccb=1-7&_nc_sid=13d280&_nc_eui2=AeEtwH0w6-3e9WL7OQlp2ipgE_x_1pYAlvUT_H_WlgCW9Sd6t4ltnS9ENNNjqZQ2pWJaa4AGEyJDAkEGd1NPMvSJ&_nc_ohc=eU5CobTN0BwQ7kNvwFO7Hjz&_nc_oc=AdoTzyEJTUPXbbYJy7WdDI2erTk4jj9vzrjdExhqxlO3_a1Sv3KFFmlqaZANho3FF0R3ZDbLP8t2Xa2L4TirLCSG&_nc_zt=23&_nc_ht=scontent.fceb1-3.fna&_nc_gid=dRO1gjQsFDgKryUAe_F7Cg&_nc_ss=7a3a8&oh=00_AfwbD8zmR2phAEeiLUIuRnVa3tc-P-yIt9uZlD7f84GEFw&oe=69D03F7B\"]', '', 'For order.', '400/Tub', '', 'active', 0, 3, '0.00', '2026-03-27 06:42:55', '2026-03-30 23:48:35'),
(63, 3, 'Simboryo  ng Barangay Paga', 'simboryo-ng-barangay-paga', 'The simboryo in Barangay Paga stands as a quiet yet powerful symbol of the rich heritage of San Enrique. This old structure, weathered by time, reflects the deep-rooted traditions and spiritual life of the community. Often built as a small stone chapel or shrine, the simboryo represents the strong faith and unity of the people, serving as a place for prayer, reflection, and remembrance.\r\n\r\nSurrounded by natural landscape, it blends history with nature—its aged walls telling stories of generations past. More than just an architectural feature, the simboryo embodies the identity of San Enrique, preserving its culture, beliefs, and enduring connection to its history.', '', 'Paga', '', '', '', '11.08551734', '122.67053767', 'https://drive.google.com/thumbnail?id=1dmvT472c05Q-uoZ3-oH2lvhotMoz9CRT&sz=w1200', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1qiQViMSGw454VoNxLNfrv2E8_c2HBxdY&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1rSdFzKmn4Bg8xmfPlOKBCWRJ-xKORxCH&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1DwknLMEFkABdWTxEj9h_6i0cZyT9EQAI&sz=w1200\"]', '', '', '', '', 'active', 0, 3, '0.00', '2026-03-30 14:00:11', '2026-03-31 06:26:11'),
(64, 4, 'Rosita Papaya (Crema de Leche)', 'rosita-papaya-crema-de-leche', 'Rosita Papaya Crema de Leche (also known as Papaya Rosette or Dinulce nga Kapayas) is a cherished, artisanal delicacy from San Enrique, Iloilo. It is a hand-crafted sweet made from thinly shaved, green papaya caramelized in sugar, painstakingly shaped into delicate flower petals. This delicacy reflects the agricultural bounty of San Enrique and requires intense labor and skill to produce, often available through local specialty makers like Nanay Emangs Delicacies.', '', 'Poblacion Ilaya', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1WzyW59nlEJ4PWq1nkl5PNnwfDkrHMgKy&sz=w1200', '[]', '', 'For order.', '15/pc', '', 'active', 0, 2, '0.00', '2026-03-30 15:19:56', '2026-03-31 00:34:58'),
(65, 1, 'Tonys Farm Resort', 'tonys-farm-resort-1', 'Tonys Valley Farm Resort is a private resort offering a peaceful atmosphere with swimming pools and cottages. Enjoyable views include lush greenery, swimming pools, and scenic surroundings. Activities: swimming, picnic, and nature walks.', '', 'Mapili', '0908 866 6909', '', '', '11.10524189', '122.74732590', 'https://drive.google.com/thumbnail?id=1rMd2jA-BPZJp3Hw9wbxkoJL8T6fX6Ikv&sz=w1200', '[\"https:\\/\\/drive.google.com\\/thumbnail?id=1sB6fes7Q_O4NJZtGuOb_V7Wa10fsEbik&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=117MzqUHHBuOSq0_xkM_EJn_B8tpRNqGr&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=12jYDEdFRi98wjeVq5vRUuvDC7OVa7TFg&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=15AU4AYUXkLrSYhcjy4OR5JPYSTJHaSCY&sz=w1200\",\"https:\\/\\/drive.google.com\\/thumbnail?id=1WVCNkOguBEd_pxrCWF0K9OAhG2jEzfFl&sz=w1200\"]', '', 'Daily (contact for specific hours)', 'Contact for rates', '', 'active', 0, 2, '0.00', '2026-03-31 01:29:58', '2026-03-31 06:37:13'),
(67, 3, 'Artifact-Plantsa de uling', 'artifact-plantsa-de-uling', 'These antique items are traditional charcoal-heated irons, often referred to in the Philippines as plantsa de uling. \r\nThey feature heavy cast iron bodies designed to hold live coals for heat. \r\nThe handles are typically made of wood, and some models include decorative elements like a rooster on top. \r\nThese irons are popular collectible items representing a pre-electric era of laundry care. \r\nThey are still sought after for decorative purposes or for their sustainable, non-electric functionality.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1OJfYBGT4fdgOCWcsIB3fQMNMUu_UjpwB&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-12 01:31:55', '2026-04-12 01:31:55'),
(68, 3, 'Artifact- Kingki', 'artifact-kingki', 'Kingki, a vintage kerosene lamp commonly used in the past for lighting before electricity became widely available. The Kingki has a sturdy black metal frame with a clear glass chamber in the center that protects the flame from wind. A small handle on top makes it easy to carry, while the base holds the fuel that powers the lamp.\r\nSeveral Kingki lamps are neatly displayed on a table, suggesting they are part of a historical or cultural exhibit. Behind them, other old objects and memorabilia can be seen, adding to the antique atmosphere. These lamps represent simple yet practical lighting tools used in homes, especially in rural areas, during nighttime or power outages.In addition, the Kingki symbolizes traditional living, resourcefulness, and the history of early household lighting.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=18BurI6evtTHSXEgYCg6aYWVnz_Ol9UNG&sz=w1200', '[]', '', '', '', '', 'active', 0, 3, '0.00', '2026-04-12 01:56:30', '2026-04-17 00:12:11'),
(69, 3, 'Artifact-Raynahan', 'artifact-raynahan', 'The Raynahan is a traditional wooden container used in the past for storing rice and other grains. It is usually made from thick, durable wood and shaped like a rectangular box with sturdy sides. The surface often looks rough and aged because it was commonly used in homes and farms for many years.\r\nThis particular Raynahan appears old and weathered, showing signs of long-term use. The wood has cracks and worn edges, which suggest it was handcrafted and repeatedly handled. Its deep interior allowed people to store harvested rice safely, keeping it dry and protected from pests.\r\nRaynahan reflects traditional Filipino farming practices, where families stored their rice supply after harvest. It symbolizes self-sufficiency, hard work, and the importance of agriculture in rural communities.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=10ad6t3mZH1nrJZRnebX89FNh9wkPbbhM&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-12 06:50:32', '2026-04-12 06:50:32'),
(70, 3, 'Artifact-Garapon', 'artifact-garapon', 'The Garapon is large glass jars and bottles traditionally used for storing liquids and food items. These containers come in different shapes and colors, including clear, green, and brown glass. They are wide-mouthed, making them easy to fill, clean, and reuse.\r\nIn the past, garapons were commonly used in households to store water, vinegar, oil, local beverages, or preserved foods. The thick glass material helped keep the contents safe and fresh. Some were also used in small stores for selling products in bulk.\r\nThe arrangement of multiple garapons on display suggests they are part of a cultural or historical exhibit, highlighting everyday storage tools used before modern plastic containers became popular. Overall, the garapon represents practicality, sustainability, and traditional Filipino household practices.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1BcgcAd9mt_WUDutuCOnKF_gE9JZM7WwY&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-12 06:51:24', '2026-04-12 06:51:24'),
(71, 3, 'Artifact-Alkansiya', 'artifact-alkansiya', 'Alkansiya is a humble yet powerful symbol of Ilonggo &amp;quot;pinaagi&amp;quot; (resourcefulness).\r\n​Handcrafted from local earthenware, its rugged, cross-hatched surface reflects the traditional pottery techniques of the region. For generations in San Enrique, these clay jars were essential household tools used to cultivate the habit of diniyut (saving bit by bit).\r\n​Often filled with coins from local trade or harvests, the jar would eventually be broken to fund a childs education or a family milestone. Today, displayed as a preserved artifact, it stands as a tribute to the town’s values of thrift, hard work, and preparation for the future.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1xEM_9EOw5ZrAH1ZryukKd_UetxdC_j1_&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-12 07:27:03', '2026-04-17 01:12:28'),
(72, 3, 'Artifact- Espading', 'artifact-espading', '​Espading: This is a traditional long-bladed tool, similar to a machete but with a distinct straight edge. In San Enrique, it is the primary tool of the sacada (sugar worker), used with precision to cut and clean sugarcane stalks during the harvest season. It symbolizes the strength and endurance of the local laborers.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1QM6OOkreEm8UtMUkOU6nJ5f_4N-qdXRz&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-17 01:28:27', '2026-04-17 01:28:27'),
(73, 3, 'Artifacts- Lusong', 'artifacts-lusong', 'Lusong (wooden mortar) is a powerful symbol of the town&#039;s agricultural roots and communal strength.\r\n​Carved from a single, sturdy log of hardwood, this mortar was once the heartbeat of the Ilonggo home. In the farming villages of San Enrique, it was used with a heavy wooden pestle to pound rice or grind corn, a task that required great physical endurance. The rhythmic sound of the lusong echoed through the fields, signaling the preparation of the day&#039;s meal or the bounty of a fresh harvest.\r\n​Today, this weathered artifact stands as a tribute to the grit and self-sufficiency of the town’s ancestors, honoring the hardworking spirit that built the community.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=144zpl-yaG0PfjX_RAbo1YgQQEPHLG24n&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-17 01:31:19', '2026-04-17 01:31:19'),
(74, 3, 'Artifact-Petromax', 'artifact-petromax', 'Petromax (kerosene pressure lamp) is a nostalgic symbol of light and hospitality in traditional Ilonggo homes.\r\n​Before widespread rural electrification, these heavy, high-powered lamps were the brightest light source in a household. In the farming community of San Enrique, the gisi (hissing sound) of the Petromax marked the gathering of the family for dinner, long conversations into the night, or crucial all-night work during harvest seasons. It required skill to operate, involving pumping pressure into the tank to vaporize the fuel.\r\n​Now preserved as a cultural relic, the Petromax stands as a tribute to the resilience and warmth of the town’s ancestors, symbolizing the light that brought the community together.', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1tpqEfiaGw469OYEjxhsx35lGwYsmXZ4D&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-17 01:34:44', '2026-04-17 01:34:44'),
(75, 3, 'Artifacts-stone mill,', 'artifacts-stone-mill', 'Galingan nga bato/stone mill, in the heritage context of San Enrique, Iloilo, the Gilingan nga Bato (stone grinder) is a timeless symbol of traditional Ilonggo culinary skill.\r\n​Hand-carved from heavy stone, these grinders were once the heart of the kitchen, used to transform soaked rice into galapong for local favorites like puto and bibingka. In the rural homes of San Enrique, the rhythmic sound of the stone was a sign of preparation for town fiestas and family gatherings.\r\n​Today, these artifacts represent the patience and authenticity of the town’s ancestors, honoring a time when every meal was crafted with labor, love, and natural ingredients', '', '', '', '', '', NULL, NULL, 'https://drive.google.com/thumbnail?id=1YYrQEsloB_3MnHRmeQPhqNTKVOaU83nP&sz=w1200', '[]', '', '', '', '', 'active', 0, 0, '0.00', '2026-04-17 01:55:19', '2026-04-17 01:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `reviewer_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `listing_id`, `reviewer_name`, `rating`, `comment`, `created_at`) VALUES
(2, 22, 'ALVIN SABORDA', 5, 'SO NICE', '2026-03-31 06:19:22');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
