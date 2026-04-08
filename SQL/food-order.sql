-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2026 at 07:19 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food-order`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `admin_response` text DEFAULT NULL,
  `responded_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `subject`, `message`, `admin_response`, `responded_at`, `created_at`) VALUES
(2, 5, 'complaint', 'you should be developed the quantity of rice', 'Sry sir.Accidently we created a  mistake                        ', '2025-11-16 07:20:05', '2025-09-08 11:39:01'),
(3, 4, 'Food quality', 'pls increase rice quality', NULL, NULL, '2026-01-02 20:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `map_embed_url` text DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_info`
--

INSERT INTO `contact_info` (`id`, `email`, `phone`, `address`, `city`, `state`, `postal_code`, `country`, `lat`, `lng`, `map_embed_url`, `facebook`, `twitter`, `instagram`, `updated_at`) VALUES
(1, 'support@example.com', '+880-123-4567890', '123 Some Street, Gulshan 2', 'Dhaka', 'Dhaka Division', '1212', 'Bangladesh', 23.7925000, 90.4077000, 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.902874674474!2d90.40713061501356!3d23.81033138458501!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755b8564c1eb83f%3A0x712a276c95c7027!2sDhaka%2C%20Bangladesh!5e0!3m2!1sen!2sus!4v1629299239000!5m2!1sen!2sus', 'https://facebook.com/yourpage', 'https://twitter.com/yourhandle', 'https://instagram.com/yourhandle', '2025-07-27 20:35:26');

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('pdf','image') NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `file_name`, `file_type`, `uploaded_at`) VALUES
(1, 'Orientation Schedule', 'orientation_schedule.pdf', 'pdf', '2025-07-27 14:38:52'),
(2, 'Exam Routine', 'exam_routine.pdf', 'pdf', '2025-07-27 14:38:52'),
(3, 'Holiday Announcement', 'holiday_notice.jpg', 'image', '2025-07-27 14:38:52'),
(4, 'Classroom Shift Notice', 'classroom_shift.pdf', 'pdf', '2025-07-27 14:38:52'),
(5, 'Cultural Event Poster', 'event_poster.png', 'image', '2025-07-27 14:38:52'),
(7, 'dfgdfgdfd', 'pizza.jpg', 'image', '2025-08-28 07:26:06'),
(8, 'চক্রবাক ক্যাফেটেরিয়া জরুরি নোটিশ', 'চক্রবাক_ক্যাফেটেরিয়া_জরুরি_নোটিশ.pdf', 'pdf', '2025-09-07 03:59:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `food_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`food_ids`)),
  `quantities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`quantities`)),
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `prices` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`prices`)),
  `totalPrice` decimal(10,2) NOT NULL,
  `update_date` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_contact` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `food_ids`, `quantities`, `status`, `order_date`, `prices`, `totalPrice`, `update_date`, `user_name`, `user_email`, `user_contact`) VALUES
(8, 4, '[\"1\",\"2\"]', '[2,2]', 'Cancelled', '2025-09-08 04:29:39', '[\"25.00\",\"30.00\"]', 110.00, '2026-01-02 20:33:13', 'MD RAFUL MIA', 'mgrahul639@gmail.com', '01733703448'),
(9, 5, '[\"1\",\"2\"]', '[2,2]', 'Completed', '2025-09-08 05:35:53', '[\"25.00\",\"30.00\"]', 110.00, '2025-11-22 21:01:17', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854'),
(10, 5, '[\"1\"]', '[1]', 'Completed', '2025-09-08 06:14:21', '[\"30.00\"]', 30.00, '2025-11-23 00:36:08', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854'),
(11, 5, '[\"1\"]', '[1]', 'Completed', '2025-09-08 06:39:09', '[\"25.00\"]', 25.00, '2025-09-08 12:46:37', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854'),
(12, 5, '[\"10\"]', '[1]', 'Pending', '2025-09-08 07:45:34', '[\"5.00\"]', 5.00, '2025-09-08 13:45:34', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854'),
(13, 5, '[\"2\"]', '[20]', 'Cancelled', '2025-11-20 08:24:58', '[\"30.00\"]', 600.00, '2025-12-22 11:25:10', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854'),
(14, 5, '[\"1\",\"4\"]', '[14,1]', 'Completed', '2025-12-01 07:12:22', '[\"25.00\",\"10.00\"]', 360.00, '2025-12-01 13:14:51', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854'),
(15, 4, '[\"1\"]', '[2]', 'Completed', '2026-01-02 14:35:08', '[\"25.00\"]', 100.00, '2026-01-03 00:07:52', 'MD RAFUL MIA', 'mgrahul639@gmail.com', '01733703448'),
(16, 5, '[\"1\"]', '[1]', 'Completed', '2026-01-02 18:08:09', '[\"25.00\"]', 25.00, '2026-01-03 00:08:34', 'Al-imran', 'al.imran.cse98@gmail.com', '01788736854');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `food_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `food_id`, `order_id`, `rating`, `review`, `created_at`, `updated_at`) VALUES
(6, 5, 1, 9, 3, NULL, '2025-11-22 18:09:56', '2026-01-02 17:17:23'),
(7, 5, 2, 9, 3, NULL, '2025-11-22 18:09:56', '2026-01-02 17:17:23'),
(8, 5, 1, 11, 4, NULL, '2025-11-22 18:25:20', '2026-01-02 17:17:23'),
(9, 5, 1, 10, 1, NULL, '2025-11-22 18:36:13', '2026-01-02 17:17:23'),
(10, 5, 1, 14, 3, 'dfgh', '2025-12-01 07:15:19', '2026-01-02 18:11:32'),
(11, 5, 4, 14, 3, NULL, '2025-12-01 07:15:19', '2026-01-02 17:17:23'),
(12, 5, 1, 14, 5, NULL, '2025-12-01 07:15:30', '2026-01-02 17:17:23'),
(13, 5, 4, 14, 5, NULL, '2025-12-01 07:15:30', '2026-01-02 17:17:23'),
(39, 4, 1, 15, 5, 'dggagd', '2026-01-02 17:18:44', '2026-01-02 17:21:34'),
(42, 5, 1, 16, 5, 'good', '2026-01-02 18:09:07', '2026-01-02 18:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_admin`
--

CREATE TABLE `tbl_admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_admin`
--

INSERT INTO `tbl_admin` (`id`, `full_name`, `username`, `password`) VALUES
(1, 'Arsenio Leach', 'toduwaxobi', 'f3ed11bbdb94fd9ebdefbaf646ab94d3'),
(9, 'Sasha Mendez', 'goxemyde', 'f3ed11bbdb94fd9ebdefbaf646ab94d3'),
(10, 'Vijay Thapa', 'vijaythapa', 'f3ed11bbdb94fd9ebdefbaf646ab94d3'),
(12, 'Administrator', 'admin', '12345');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_category`
--

CREATE TABLE `tbl_category` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `featured` varchar(10) NOT NULL,
  `active` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_category`
--

INSERT INTO `tbl_category` (`id`, `title`, `image_name`, `featured`, `active`) VALUES
(5, 'Burger', 'Food_Category_344.jpg', 'Yes', 'Yes'),
(6, 'MoMo', 'Food_Category_77.jpg', 'Yes', 'Yes'),
(8, 'Quia est ipsum id id', 'Food_Category_929.jpg', 'Yes', 'No'),
(9, 'Pizza', '1756362768_pizza.jpg', 'Yes', 'Yes');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_food`
--

CREATE TABLE `tbl_food` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category_name` varchar(50) NOT NULL,
  `featured` enum('Yes','No') DEFAULT 'No',
  `active` enum('Yes','No') DEFAULT 'Yes',
  `totalFood` int(11) DEFAULT 0,
  `newAddFood` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_food`
--

INSERT INTO `tbl_food` (`id`, `title`, `description`, `price`, `image_path`, `category_name`, `featured`, `active`, `totalFood`, `newAddFood`) VALUES
(1, 'Dim Vaji', 'Curry With Egg Recipe', 25.00, '1757187181_dipa-bhattacharyya20180909233309808.webp', 'Breakfast', 'Yes', 'Yes', 20, 10),
(2, 'Chicken Vuna Khichuri', 'Khichuri Item', 30.00, '1757187037_Bengali-Bhuni-Khichuri-Recipe.webp', 'Breakfast', 'Yes', 'Yes', 28, 10),
(3, 'Dim Vuna', 'Curry With Egg Recipe', 30.00, '1757186938_ডম-ভনdim-vuna-recipe-in-bengali-রসপর-পরধন-ছব.webp', 'Breakfast', 'Yes', 'Yes', 10, 10),
(4, 'Alu & Dim Vorta', 'Vorta Item', 10.00, '1757186853_alu-dim-vorta-radhunubd-min.webp', 'Breakfast', 'Yes', 'Yes', 15, 15),
(5, 'Alu Vorta', 'Vorta Item', 5.00, '1757186757_Alu-Vorta-ok.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(6, 'Badam Vorta', 'Vorta Item', 5.00, '1757186697_badam+vorta.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(7, 'Begun Vorta', 'Vorta Item', 5.00, '1757186634_Begun-pora-recipe-11-1024x576.webp', 'Breakfast', 'Yes', 'Yes', 30, 30),
(8, 'Chepa Sutki Vorta', 'Vorta Item', 5.00, '1757186580_chepa-sutki-vorta-radhunibd-min.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(9, 'Kalo Jira Vorta', 'Vorta Item', 5.00, '1757186506_IMG20200522102318_InPixio-e1590132921364.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(10, 'Dal Vorta', 'Vorta Item', 5.00, '1757186440_mosur-dal-radhunibd-min.webp', 'Breakfast', 'Yes', 'Yes', 20, 20),
(12, 'Kolmi Shak', 'Shak Item', 10.00, '1757189342_Kangkong-with-Oyster-Sauce-Recipe.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(13, 'Chicken Curry', ' Layer Chicken Curry Item', 40.00, '1757189155_jamaican-curry-chicken-in-blue-pot-.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(14, 'Palong Shak', 'Shak Item', 10.00, '1757188367_Indian-Spinach-Curry-for-Kids-10-497x745.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(15, 'Pui Shak', 'Shak Item', 10.00, '1757188305_indian-spiced-sauteed-spinach-featured-728x485.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(16, 'Shing Mach', 'Fish Curry Item', 40.00, '1757188243_fish-curry.webp', 'Lunch', 'Yes', 'Yes', 15, 15),
(17, 'Lotpoti', 'Chicken Curry Item', 40.00, '1757188176_Curry stew chicken.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(18, 'Bata Mach', 'Fish Curry Item', 40.00, '1757188108_bata-fish-curry-recipe-step-2-photo.webp', 'Lunch', 'Yes', 'Yes', 10, 10),
(19, 'Rui Mach', 'Fish Curry Item', 40.00, '1757188060_649bfbfa2b7c091964b02ec5_katla kalia 4-3.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(20, 'Tengra Mach', 'Fish Curry Item', 40.00, '1757188009_360_F_876831388_z6hOBEZ6hQjP4adfVr6W2W98Dvkd1osl.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(21, 'Patla Dal', 'Dal Item', 5.00, '1757187910_patla dal.webp', 'Lunch', 'Yes', 'Yes', 20, 20),
(22, 'Rice', 'Common Food Item', 10.00, '1757187823_360_F_1216123917_cKuxOJ5T8WmYxa9M9kFmcMj2JwGYFxpg.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(23, 'Silver Mach', 'Fish Curry Recipe', 40.00, '1757190845_silver.webp', 'Dinner', 'Yes', 'Yes', 10, 10),
(24, 'Sutki Mach', 'Dried Fish Recipe', 25.00, '1757190783_Screenshot-2024-06-25-180216.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(25, 'Pangash Mach', 'Fish Curry Recipe', 40.00, '1757190680_Panga-fish-curry-Your-everyday-fish-350x234.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(26, 'Chicken Curry', 'Layer Chicken Curry', 40.00, '1757190568_jamaican-curry-chicken-in-blue-pot-.webp', 'Dinner', 'Yes', 'Yes', 33, 33),
(27, 'Vegetables', 'Mixing With Delicious Vegetables Items', 25.00, '1757190464_BS-Patient-First-15-Minute-Vegetable-Curry-5-of-16-1-680x1020.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(28, 'Rui Mach', 'Fish Curry Recipe', 40.00, '1757190303_649bfbfa2b7c091964b02ec5_katla kalia 4-3.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(29, 'Tengra Mach', 'Fish Curry Recipe', 40.00, '1757190250_360_F_876831388_z6hOBEZ6hQjP4adfVr6W2W98Dvkd1osl.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(30, 'Patla Dal', 'Dal Recipe', 5.00, '1757190148_patla dal.webp', 'Dinner', 'Yes', 'Yes', 25, 25),
(31, 'Dim Vuna', 'Egg Recipe', 30.00, '1757190097_ডম-ভনdim-vuna-recipe-in-bengali-রসপর-পরধন-ছব.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(32, 'Rice', 'Common Food Item', 10.00, '1757190040_360_F_1216123917_cKuxOJ5T8WmYxa9M9kFmcMj2JwGYFxpg.webp', 'Dinner', 'Yes', 'Yes', 0, 0),
(33, 'Coffee', 'Delicious Drinks', 10.00, '1757189988_360_F_176517375_TdfFVyrJahmr40ffqy1xBgUj9X07sVfB.webp', 'Dinner', 'Yes', 'Yes', 18, 18),
(34, 'Coffee', 'Delicious Drinks', 10.00, '1757187748_360_F_176517375_TdfFVyrJahmr40ffqy1xBgUj9X07sVfB.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(35, 'Mix Vorta', 'Vorta Item', 20.00, '1757186287_sutki-vorta-tomato-chatney.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(36, 'Patla Dal', 'Dal Recipi', 5.00, '1757187307_patla dal.webp', 'Breakfast', 'Yes', 'Yes', 40, 40),
(37, 'Vuna Dal', 'Dal Recipe', 10.00, '1757187358_mug-dal-vuna.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(38, 'Rice', 'Common Item', 10.00, '1757187534_360_F_1216123917_cKuxOJ5T8WmYxa9M9kFmcMj2JwGYFxpg.webp', 'Breakfast', 'Yes', 'Yes', 0, 0),
(39, 'Coffee', 'Delicious Drinks', 10.00, '1757187629_360_F_176517375_TdfFVyrJahmr40ffqy1xBgUj9X07sVfB.webp', 'Breakfast', 'Yes', 'Yes', 50, 50),
(40, 'Pangash Mach', 'Fish Curry Item', 40.00, '1757189442_Panga-fish-curry-Your-everyday-fish-350x234.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(41, 'Muro Ghonto', 'Fish Curry Recipe', 25.00, '1757189505_photo.webp', 'Lunch', 'Yes', 'Yes', 22, 22),
(42, 'Lal Shak', 'Shak Item', 10.00, '1757189616_red-amaranth-curry.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(43, 'Shutki Mach Vuna', 'Dried Fish Curry Item', 25.00, '1757189695_Screenshot-2024-06-25-180216.webp', 'Lunch', 'Yes', 'Yes', 0, 0),
(44, 'Silver Mach', 'Fish Curry Item', 40.00, '1757189772_silver.webp', 'Lunch', 'Yes', 'Yes', 25, 25),
(45, 'Chicken Biriyani', 'Biriyani Item', 60.00, '1757189834_biriyani.webp', 'Lunch', 'Yes', 'Yes', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_order`
--

CREATE TABLE `tbl_order` (
  `id` int(10) UNSIGNED NOT NULL,
  `food` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `order_date` datetime NOT NULL,
  `status` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `tbl_order`
--

INSERT INTO `tbl_order` (`id`, `food`, `price`, `qty`, `total`, `order_date`, `status`, `customer_name`, `customer_contact`, `customer_email`, `customer_address`) VALUES
(1, 'Sadeko Momo', 6.00, 3, 18.00, '2020-11-30 03:49:48', 'Cancelled', 'Bradley Farrell', '+1 (576) 504-4657', 'zuhafiq@mailinator.com', 'Duis aliqua Qui lor'),
(2, 'Best Burger', 4.00, 4, 16.00, '2020-11-30 03:52:43', 'Delivered', 'Kelly Dillard', '+1 (908) 914-3106', 'fexekihor@mailinator.com', 'Incidunt ipsum ad d'),
(3, 'Mixed Pizza', 10.00, 2, 20.00, '2020-11-30 04:07:17', 'Delivered', 'Jana Bush', '+1 (562) 101-2028', 'tydujy@mailinator.com', 'Minima iure ducimus');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `image`, `created_at`, `contact`) VALUES
(1, 'Rahim Uddin', 'rahim@example.com', 'rahim123', 'user_1_1753628269.png', '2025-07-27 14:25:17', '01733703448'),
(2, 'Karim Khan', 'karim@example.com', 'karim123', 'uploads/karim.jpg', '2025-07-27 14:25:17', NULL),
(3, 'Fatema Begum', 'fatema@example.com', 'fatema123', 'uploads/fatema.jpg', '2025-07-27 14:25:17', NULL),
(4, 'MD RAFUL MIA', 'mgrahul639@gmail.com', '123456', 'Logo.png', '2025-07-27 14:59:33', '01733703448'),
(5, 'Al-imran', 'al.imran.cse98@gmail.com', '1234', NULL, '2025-09-08 05:33:20', '01788736854');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_category`
--
ALTER TABLE `tbl_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_food`
--
ALTER TABLE `tbl_food`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_order`
--
ALTER TABLE `tbl_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tbl_admin`
--
ALTER TABLE `tbl_admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_category`
--
ALTER TABLE `tbl_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_food`
--
ALTER TABLE `tbl_food`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `tbl_order`
--
ALTER TABLE `tbl_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
