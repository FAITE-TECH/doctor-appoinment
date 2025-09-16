-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2025 at 12:36 PM
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
-- Database: `doctor`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'Cardiology', 'Heart and cardiovascular system treatment', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(2, 'Pediatrics', 'Medical care for infants, children, and adolescents', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(3, 'Orthopedics', 'Bones, joints, and musculoskeletal system', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(4, 'Neurology', 'Nervous system and brain disorders', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(5, 'Dermatology', 'Skin, hair, and nail conditions', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `specialization` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `name`, `email`, `specialization`, `phone`, `image_path`, `description`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 2, 'Dr. John Smith', 'john.smith@hospital.com', 'Cardiology', '+1234567890', NULL, 'Experienced cardiologist with 15 years of practice in heart disease treatment and prevention.', 1, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(2, 3, 'Dr. Sarah Johnson', 'sarah.johnson@hospital.com', 'Pediatrics', '+1234567891', NULL, 'Pediatric specialist focused on child health and development with expertise in preventive care.', 2, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(3, NULL, 'Dr. Michael Brown', 'michael.brown@hospital.com', 'Orthopedics', '+1234567892', NULL, 'Orthopedic surgeon specializing in joint replacement and sports medicine.', 3, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(4, NULL, 'Dr. Emily Davis', 'emily.davis@hospital.com', 'Neurology', '+1234567893', NULL, 'Neurologist with expertise in treating brain and nervous system disorders.', 4, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(5, NULL, 'Dr. Robert Wilson', 'robert.wilson@hospital.com', 'Dermatology', '+1234567894', NULL, 'Dermatologist specializing in skin conditions and cosmetic dermatology.', 5, '2025-09-15 09:56:02', '2025-09-15 09:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'Health Awareness Seminar', 'Learn about preventive healthcare and wellness', '2025-09-10', '14:00:00', 'Main Conference Hall', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(2, 'Blood Donation Drive', 'Community blood donation event', '2025-09-15', '10:00:00', 'Hospital Lobby', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(3, 'Diabetes Management Workshop', 'Educational workshop for diabetes patients', '2025-09-20', '15:30:00', 'Seminar Room A', NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `description`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'Modern Hospital Facility', 'Our state-of-the-art hospital building with advanced medical equipment', '/doctor-appoinment/uploads/gallery/hospital-building.jpg', '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(2, 'Emergency Department', '24/7 emergency care facility with dedicated medical staff', '/doctor-appoinment/uploads/gallery/emergency-dept.jpg', '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(3, 'Surgery Suite', 'Advanced operating rooms equipped with latest surgical technology', '/doctor-appoinment/uploads/gallery/surgery-suite.jpg', '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(4, 'Patient Recovery Room', 'Comfortable recovery rooms for post-surgical care', '/doctor-appoinment/uploads/gallery/recovery-room.jpg', '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(5, 'Medical Laboratory', 'Fully equipped laboratory for diagnostic testing', '/doctor-appoinment/uploads/gallery/laboratory.jpg', '2025-09-15 09:56:02', '2025-09-15 09:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` enum('admin','doctor','patient','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'admin'),
(2, 'doctor'),
(3, 'patient'),
(4, 'staff');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 'General Consultation', 'Basic medical consultation with a doctor', 50.00, NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(2, 'Specialist Consultation', 'Consultation with a specialist doctor', 100.00, NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(3, 'Laboratory Tests', 'Blood tests and other laboratory examinations', 75.00, NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(4, 'X-Ray Imaging', 'X-ray examination and diagnosis', 120.00, NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(5, 'Physical Therapy', 'Rehabilitation and physical therapy sessions', 80.00, NULL, '2025-09-15 09:56:02', '2025-09-15 09:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin@hospital.com', '0192023a7bbd73250516f069df18b500', 1, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(2, 'Dr. John Smith', 'john.smith@hospital.com', 'c7ef8fc860e6b06ce37526b3e361700d', 2, '2025-09-15 09:56:02', '2025-09-15 09:56:02'),
(3, 'Dr. Sarah Johnson', 'sarah.johnson@hospital.com', 'c7ef8fc860e6b06ce37526b3e361700d', 2, '2025-09-15 09:56:02', '2025-09-15 09:56:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
