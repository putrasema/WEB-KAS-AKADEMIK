-- Database Schema for Sistem Uang Kas Akademik Internasional

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `academic_cash_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','treasurer','student') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'); 
-- Password is 'password'

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(5) NOT NULL,
  `exchange_rate` decimal(15,6) NOT NULL DEFAULT 1.000000 COMMENT 'Rate relative to Base Currency',
  `is_base` tinyint(1) NOT NULL DEFAULT 0,
  `last_updated` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`code`, `name`, `symbol`, `exchange_rate`, `is_base`, `last_updated`) VALUES
('IDR', 'Indonesian Rupiah', 'Rp', 1.000000, 1, NOW()),
('USD', 'US Dollar', '$', 15000.000000, 0, NOW()),
('EUR', 'Euro', '€', 16500.000000, 0, NOW());

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--


--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id_number` varchar(20) NOT NULL COMMENT 'NIM/NIS',
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('active','graduated','dropped_out') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id_number` (`student_id_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id_number`, `full_name`, `email`, `phone`, `status`) VALUES
('2023001', 'John Doe', 'john@example.com', '08123456789', 'active'),
('2023002', 'Jane Smith', 'jane@example.com', '08198765432', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`name`, `type`, `description`) VALUES
('SPP', 'income', 'Monthly Tuition Fee'),
('Donation', 'income', 'Voluntary Donation'),
('Equipment', 'expense', 'Buying class equipment'),
('Event', 'expense', 'Class events and gatherings'),
('Snacks', 'expense', 'Daily snacks for meetings');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `type` enum('email','web_push') NOT NULL,
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'User associated with transaction (e.g. payer)',
  `student_id` int(11) DEFAULT NULL COMMENT 'Student associated if applicable',
  `category_id` int(11) DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount_original` decimal(15,2) NOT NULL,
  `currency_code` varchar(3) NOT NULL,
  `exchange_rate_at_time` decimal(15,6) NOT NULL,
  `amount_base` decimal(15,2) NOT NULL COMMENT 'Converted to Base Currency',
  `description` text NOT NULL,
  `payment_method` enum('cash','bank_transfer','credit_card','debit_card','e_wallet') NOT NULL DEFAULT 'cash' COMMENT 'Payment method used',
  `transaction_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `student_id` (`student_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('app_name', 'Academic Cash System'),
('base_currency', 'IDR');

COMMIT;
