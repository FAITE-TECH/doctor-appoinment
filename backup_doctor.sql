-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 30, 2025 at 03:58 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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

CREATE DATABASE IF NOT EXISTS doctor;
USE doctor;


CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `schedule_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`, `schedule_id`) VALUES
(8, 8, 7, '2025-09-21', '14:30:00', 'cancelled', 'Follow-up appointment', '2025-09-16 06:28:16', NULL),
(9, 7, 8, '2025-09-22', '09:15:00', 'confirmed', 'Initial consultation', '2025-09-16 06:28:16', NULL),
(10, 1, 20, '2025-09-17', '09:00:00', 'pending', 'sdacdsacdsac', '2025-09-16 09:56:29', NULL),
(11, 1, 21, '2025-09-17', '17:57:00', 'confirmed', 'cescwe wcwdcedc', '2025-09-17 02:31:28', NULL),
(12, 1, 21, '2025-09-18', '07:42:00', 'confirmed', 'cdwscdwc dcwdcscwcwec', '2025-09-17 02:34:20', NULL),
(13, 1, 20, '2025-09-17', '07:18:00', 'pending', 'cwcececec re', '2025-09-17 02:38:08', NULL),
(14, 1, 15, '2025-09-18', '11:47:00', 'pending', 'cccccccccccccc cccccc', '2025-09-17 03:18:33', NULL),
(15, 1, 20, '2025-09-17', '10:00:00', 'pending', 'mmmmmmmmmmm', '2025-09-17 03:25:41', NULL),
(16, 1, 21, '2025-09-19', '13:03:00', 'pending', 'nbbbbnnn', '2025-09-17 05:34:28', NULL),
(17, 1, 21, '2025-09-20', '11:03:00', 'pending', 'cjkenc vakcm as.sac akmc ,.asc as', '2025-09-17 05:38:01', NULL),
(18, 1, 21, '2025-09-21', '11:08:00', 'confirmed', 'cvdfvcdfvdf', '2025-09-17 05:39:02', NULL),
(19, 10, 21, '2025-09-17', '17:57:00', 'pending', 'cwecewcfe', '2025-09-17 05:52:45', NULL),
(20, 10, 21, '2025-09-21', '11:08:00', 'pending', 'crecfddd', '2025-09-17 05:53:17', NULL),
(21, 1, 23, '2025-09-18', '10:24:00', 'pending', 'test appoinmnet', '2025-09-18 03:58:23', NULL),
(22, 12, 15, '2025-09-23', '22:06:00', 'pending', 'bbbbbbb', '2025-09-22 15:37:49', NULL),
(23, 8, 20, '2025-09-25', '15:10:00', 'pending', 'this is a new test', '2025-09-24 09:41:04', NULL),
(24, 14, 20, '2025-09-25', '15:10:00', 'pending', 'mmmmmmmmmmmm', '2025-09-24 09:43:15', NULL),
(25, 15, 20, '2025-09-25', '15:10:00', 'pending', 'uuuuuuuuuuuuuu', '2025-09-24 09:46:16', NULL),
(26, 16, 20, '2025-09-25', '15:10:00', 'pending', 'ppppppp', '2025-09-24 09:51:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','closed') DEFAULT 'new',
  `admin_reply` text DEFAULT NULL,
  `replied_by` int(11) DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `subject`, `message`, `status`, `admin_reply`, `replied_by`, `replied_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'new', 'nw', 'admin@hospital.com', NULL, 'rfcvrfecrefc', 'fcdc fcf rfecvrfeff', 'closed', NULL, NULL, NULL, '2025-09-16 06:18:09', '2025-09-16 06:38:36'),
(2, 7, 'Test', 'User', 'test@example.com', NULL, 'Test Message', 'This is a test message to check notifications', 'closed', NULL, NULL, NULL, '2025-09-16 06:35:30', '2025-09-16 06:38:30'),
(3, 8, 'New', 'Customer', 'customer@example.com', NULL, 'Urgent Question', 'I have an urgent question about my appointment', 'closed', NULL, NULL, NULL, '2025-09-16 06:39:40', '2025-09-16 06:45:17'),
(4, 7, 'Test', 'Customer', 'test@example.com', NULL, 'Question about Services', 'I would like to know more about your cardiology services', 'replied', 'Thank you for your inquiry! Our cardiology department offers comprehensive heart care services including consultations, diagnostic tests, and treatment plans. Please call us at (555) 123-4567 to schedule an appointment.', 1, '2025-09-16 06:47:29', '2025-09-16 06:47:29', '2025-09-16 06:47:29'),
(5, 11, 'Test', 'User', 'testuser@example.com', NULL, 'Appointment Question', 'I would like to schedule an appointment with a cardiologist', 'read', 'Thank you for your interest in our cardiology services! We have several excellent cardiologists available. Please call our appointment line at (555) 123-4567 to schedule your consultation. We look forward to helping you with your heart health needs.', 1, '2025-09-16 06:48:58', '2025-09-16 06:48:58', '2025-09-16 07:15:58'),
(6, 1, 'dxeswdx', 'wesdx', 'admin@hospital.com', NULL, 'cseawcxdsae', 'seaxdwsedx edwedxeswa rrrrrrr', 'replied', 'this is new repply hope you find this repply', 1, '2025-09-16 06:57:58', '2025-09-16 06:53:09', '2025-09-16 06:57:58'),
(7, 11, 'Test', 'User', 'testuser@example.com', NULL, 'New Question', 'I have a new question about my treatment plan', 'read', NULL, NULL, NULL, '2025-09-16 06:58:41', '2025-09-16 07:20:48'),
(8, 1, 'System', 'Admin', 'admin@hospital.com', NULL, 'csadcasc', 'adc scascas', 'read', 'ne test of repply', 1, '2025-09-17 05:40:07', '2025-09-16 12:01:00', '2025-09-17 05:40:23'),
(9, 1, 'System', 'Admin', 'admin@hospital.com', NULL, 'test ing massges', 'this is for the test purpose', 'replied', 'i got the massge', 1, '2025-09-18 04:02:36', '2025-09-18 04:02:03', '2025-09-18 04:02:36'),
(10, NULL, 'tcj', 'test', 'new@gmail.com', NULL, 'ccccccccc', 'ccccccccccccc', 'read', NULL, NULL, NULL, '2025-09-22 15:34:23', '2025-09-22 15:51:08'),
(11, NULL, 'bbb', 'bbbb', 'bbbb@gmail.com', NULL, 'bbbbb', 'bbbbbbbbbbbb', 'read', NULL, NULL, NULL, '2025-09-22 15:35:47', '2025-09-22 15:51:10'),
(12, NULL, 'phone', 'check', 'phone@gmail.com', NULL, 'cdscdscdsc', 'sd s dscdxcsdcsdfffffffgggg', 'read', NULL, NULL, NULL, '2025-09-26 02:54:56', '2025-09-26 02:55:31'),
(13, NULL, 'bbb', 'bb', 'bb@gmail.com', '1111111111', 'escewxhhh', 'cewdcxewcxwecx wwe', 'read', NULL, NULL, NULL, '2025-09-26 03:36:02', '2025-09-26 03:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `image_path`, `created_at`) VALUES
(51, 'new department', 'new discreption for department', '68c0d834476f82.50421784.png', '2025-09-10 01:45:24'),
(53, '3rd department', 'ncc. ecec theis is a new edit xs', '68ca7ea5c8c1e7.29307730.png', '2025-09-17 09:25:57'),
(54, 'test depart ment', 'test discreption for depart ment', '68cb80a099bdb3.86796387.png', '2025-09-18 03:46:40'),
(55, 'bbbb', 'bbbbb it is no longer need ed', '68d21513954da1.33412325.png', '2025-09-23 03:33:39'),
(56, '4 th test', 'this is the 4th test', '68d5e964e2d704.72367238.png', '2025-09-26 01:16:20');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `specialization` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `name`, `email`, `specialization`, `phone`, `department_id`, `description`, `image_path`, `created_at`) VALUES
(7, NULL, 'name', 'name@gmail.com', 'child', '1234567890', NULL, 'new', '/doctor-appoinment/uploads/doctors/68bfa9f1ed0527.23046499.png', '2025-09-09 04:15:45'),
(8, NULL, 'test 10', 'test10@gmail.com', 'child', '1234123412', NULL, 'new', '/doctor-appoinment/uploads/doctors/68bfb5bbbed6e7.54568502.png', '2025-09-09 05:06:03'),
(11, NULL, 'Test Doctor', 'test@example.com', 'Test Specialization', '123-456-7890', NULL, 'Test Description', NULL, '2025-09-09 05:52:16'),
(15, NULL, 'admin', 'admi@gmail.com', 'new', '1231231231', 51, 'xwx', '/doctor-appoinment/uploads/doctors/68c0d8b0ed3824.85548853.png', '2025-09-10 01:47:28'),
(19, NULL, 'test', 'test@gmail.com', 'new', '1234123412', 51, 'cecer', '/doctor-appoinment/uploads/doctors/68c0de8ee734f6.04008813.png', '2025-09-10 02:12:30'),
(20, NULL, 'this is new', 'thisisne@gmail.com', 'skin', '1234123412', 51, 'xwxwsax', '/doctor-appoinment/uploads/doctors/68c933a1089b93.19774943.png', '2025-09-16 09:53:37'),
(21, NULL, 'vvvvvvv', 'vvv@gamil.com', 'vvvvv', '1234512345', NULL, 'vvvvvv', '/doctor-appoinment/uploads/doctors/68c93d3f660811.00254476.png', '2025-09-16 10:34:39'),
(22, NULL, 'mmmmm', 'm@gmail.com', 'child', '0777123456', NULL, 'cdscew', '/doctor-appoinment/uploads/doctors/68ca72d48d4171.63518274.png', '2025-09-17 08:35:32'),
(23, NULL, 'new test 22', 'newtest11@hospital.com', 'skin and', '07771234555', 53, 'test discreption', '/doctor-appoinment/uploads/doctors/68cb7fff0c85e4.38348273.png', '2025-09-18 03:43:59');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `max_appointments` int(11) DEFAULT 1,
  `current_appointments` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctor_schedules`
--

INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `schedule_date`, `start_time`, `end_time`, `is_available`, `max_appointments`, `current_appointments`, `created_at`, `updated_at`) VALUES
(39, 7, '2025-01-15', '08:00:00', '09:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(40, 7, '2025-01-15', '09:00:00', '10:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(41, 7, '2025-01-15', '10:00:00', '11:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(42, 7, '2025-01-15', '13:00:00', '14:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(43, 7, '2025-01-15', '14:00:00', '15:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(44, 7, '2025-01-16', '08:00:00', '09:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(45, 7, '2025-01-16', '09:00:00', '10:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(46, 7, '2025-01-16', '10:00:00', '11:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(47, 7, '2025-01-16', '13:00:00', '14:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(48, 7, '2025-01-16', '14:00:00', '15:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(49, 8, '2025-01-15', '10:00:00', '11:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(50, 8, '2025-01-15', '11:00:00', '12:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(51, 8, '2025-01-15', '15:00:00', '16:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(52, 8, '2025-01-15', '16:00:00', '17:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(53, 8, '2025-01-16', '10:00:00', '11:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(54, 8, '2025-01-16', '11:00:00', '12:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(55, 8, '2025-01-16', '15:00:00', '16:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(56, 8, '2025-01-16', '16:00:00', '17:00:00', 1, 1, 0, '2025-09-16 11:13:33', '2025-09-16 11:13:33'),
(65, 21, '2025-09-16', '17:54:00', '18:54:00', 1, 1, 0, '2025-09-16 11:24:30', '2025-09-16 11:24:30'),
(66, 21, '2025-09-16', '21:54:00', '23:54:00', 1, 1, 0, '2025-09-16 11:24:41', '2025-09-16 11:24:41'),
(68, 21, '2025-09-17', '17:57:00', '18:57:00', 1, 1, 0, '2025-09-16 11:27:26', '2025-09-16 11:27:26'),
(69, 20, '2025-09-17', '07:18:00', '10:00:00', 1, 1, 0, '2025-09-17 01:46:29', '2025-09-17 01:46:29'),
(70, 20, '2025-09-17', '10:00:00', '12:00:00', 1, 1, 0, '2025-09-17 01:47:24', '2025-09-17 01:47:24'),
(71, 21, '2025-09-18', '08:42:00', '11:42:00', 1, 1, 0, '2025-09-17 02:12:18', '2025-09-17 12:42:51'),
(72, 15, '2025-09-18', '08:48:00', '09:47:00', 1, 1, 0, '2025-09-17 03:17:33', '2025-09-17 03:17:33'),
(73, 15, '2025-09-18', '11:47:00', '13:47:00', 1, 1, 0, '2025-09-17 03:17:46', '2025-09-17 03:17:46'),
(74, 8, '2025-09-18', '09:53:00', '11:00:00', 1, 1, 0, '2025-09-17 03:24:09', '2025-09-17 03:24:09'),
(90, 21, '2025-09-19', '13:03:00', '16:03:00', 1, 1, 0, '2025-09-17 05:33:36', '2025-09-17 12:43:07'),
(91, 21, '2025-09-20', '11:03:00', '13:03:00', 1, 1, 0, '2025-09-17 05:33:46', '2025-09-17 05:33:46'),
(92, 21, '2025-09-21', '12:08:00', '16:12:00', 1, 1, 0, '2025-09-17 05:38:36', '2025-09-17 12:43:29'),
(93, 7, '2025-12-01', '15:20:00', '17:20:00', 1, 1, 0, '2025-09-17 09:50:54', '2025-09-17 09:50:54'),
(95, 22, '2025-10-15', '11:00:00', '12:00:00', 1, 1, 0, '2025-09-17 09:55:10', '2025-09-17 10:00:00'),
(98, 23, '2025-09-18', '10:24:00', '11:24:00', 1, 1, 0, '2025-09-18 03:54:51', '2025-09-18 03:54:51'),
(100, 23, '2025-09-18', '19:40:00', '22:25:00', 1, 1, 0, '2025-09-18 03:55:39', '2025-09-18 03:55:45'),
(101, 15, '2025-09-23', '22:06:00', '23:06:00', 1, 1, 0, '2025-09-22 15:36:50', '2025-09-22 15:37:09'),
(102, 15, '2025-09-23', '09:06:00', '11:06:00', 1, 1, 0, '2025-09-22 15:37:02', '2025-09-22 15:37:02'),
(103, 20, '2025-09-25', '15:10:00', '17:10:00', 1, 1, 0, '2025-09-24 09:40:19', '2025-09-24 09:40:19');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `image_path`, `created_at`) VALUES
(35, 'nrw', 'cdwscx', '2025-09-11', '07:12:00', 'masx', '68c0d7a17f25e9.83939360.png', '2025-09-10 01:42:57'),
(36, 'tes', 'cedswcedw', '2025-09-11', '07:17:00', 'cwcwd', '68c0d86f51b205.36028639.png', '2025-09-10 01:46:23'),
(37, 'new evet', 'this event is for test', '2025-09-20', '00:23:00', 'kandy', '68cb8190dddb82.42224320.png', '2025-09-18 03:50:40'),
(38, 'new evet', 'this event is for test testing again', '2025-09-20', '00:23:00', 'colombo', '68cb81a779a715.31697567.png', '2025-09-18 03:51:03'),
(39, 'new', 'dwcd dsknc  whet wiil', '2025-09-24', '09:05:00', 'galle', '68d2159cbe82b8.52325951.png', '2025-09-23 03:35:56');

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
(1, 'new', 'new', '68b951444b1bf.png', '2025-09-04 08:43:48', '2025-09-04 08:43:48'),
(3, 'new', 'ngfc', '68b95d04944b5.png', '2025-09-04 09:33:56', '2025-09-04 09:33:56'),
(4, 'new image', 'new', '68bef48216c3e.png', '2025-09-08 15:21:38', '2025-09-08 15:21:38'),
(5, 'new', 'ten vhhhj', '68bf852ef3804.png', '2025-09-09 01:38:54', '2025-09-09 01:38:54'),
(6, 'new image', 'new bhjb', '68bfbfc0452e4.png', '2025-09-09 05:48:48', '2025-09-09 05:48:48'),
(9, 'test gallery', 'this is for the test gallery this is for the test', '68cb80efe6e61.png', '2025-09-18 03:47:59', '2025-09-18 03:47:59');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `image_path`, `created_at`) VALUES
(50, 'aaaaa', 'aaaaaaa', 12.00, '68bfa51e20ed02.61261808.png', '2025-09-09 03:55:10'),
(51, 'cdewcdew', 'wedcwderc', 12.00, '68c0d87e7b95a9.83703029.png', '2025-09-10 01:46:38'),
(53, 'new test service edit', 'new test service test case testing againg', 12000.00, '68cb813cb54e48.13906567.png', '2025-09-18 03:49:16'),
(54, 'nnn', 'nnnnnn', 12.00, '68d218746b3d29.01278961.png', '2025-09-23 03:48:04'),
(55, '5th test', 'this is the 5 th test', 120.00, '68d5ea46ec6376.08539062.png', '2025-09-26 01:20:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL DEFAULT 3,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin@hospital.com', NULL, '0192023a7bbd73250516f069df18b500', 1, '2025-09-02 04:22:18', '2025-09-02 04:22:18'),
(2, 'Dr. John Smith', 'john.smith@hospital.com', NULL, 'c7ef8fc860e6b06ce37526b3e361700d', 2, '2025-09-02 04:22:22', '2025-09-02 04:22:22'),
(3, 'Dr. Sarah Johnson', 'sarah.johnson@hospital.com', NULL, 'c7ef8fc860e6b06ce37526b3e361700d', 2, '2025-09-02 04:22:22', '2025-09-02 04:22:22'),
(7, 'chamod', 'qwe@gmail.com', NULL, 'ed2b1f468c5f915f3f1cf75d7068baae', 3, '2025-09-02 04:45:50', '2025-09-02 04:45:50'),
(8, 'new', 'new@gmail.com', NULL, '4297f44b13955235245b2497399d7a93', 2, '2025-09-02 05:39:46', '2025-09-02 05:39:46'),
(9, 'test2', 'testmail@gmail.com', NULL, 'f5bb0c8de146c67b44babbf4e6584cc0', 3, '2025-09-02 06:02:39', '2025-09-02 06:02:39'),
(10, 'test new today', 'testnewtoday@mail.com', NULL, 'ed2b1f468c5f915f3f1cf75d7068baae', 3, '2025-09-09 07:26:47', '2025-09-09 07:26:47'),
(11, 'Test User', 'testuser@example.com', NULL, '482c811da5d5b4bc6d497ffa98491e38', 3, '2025-09-16 06:48:33', '2025-09-16 06:48:33'),
(12, 'bbbb', 'bbbb@hospital.com', NULL, '$2y$10$HuBYtSMJqc00rtbxQ10zH.l/pLUAojHokcFrobTQayHQmd5iWK/em', 3, '2025-09-22 15:37:49', '2025-09-22 15:37:49'),
(13, 'new', 'admin123@hospital.com', NULL, '8a712ac42cb12f582b07e796fc4fceea', 3, '2025-09-24 08:14:47', '2025-09-24 08:14:47'),
(14, 'zzzz', 'zzz@gmail.com', NULL, '$2y$10$NZCTG3YDNpohLufI0zXLKu1oSEsX.b6R6k/OeFPz3Z5/dMw09Y7KG', 3, '2025-09-24 09:43:15', '2025-09-24 09:43:15'),
(15, 'ttttttttt', 'ttt@gmail.com', NULL, '$2y$10$9JaUUyi4nhsa5pf5XMDxhO5x45hSrCPkOfSxEZvIisIL0JuBYpVr6', 3, '2025-09-24 09:46:16', '2025-09-24 09:46:16'),
(16, 'oooo', 'ooo@gmail.com', '1111111111', '$2y$10$RnJmmnQExrDr6vb8JXqjdOn6R67lYu1rBxOVRUqjflBtdW.XT7awm', 3, '2025-09-24 09:51:41', '2025-09-24 09:51:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_appointment_schedule` (`schedule_id`),
  ADD KEY `idx_appointment_doctor_date` (`doctor_id`,`appointment_date`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `replied_by` (`replied_by`);

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
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule` (`doctor_id`,`schedule_date`,`start_time`,`end_time`),
  ADD KEY `idx_doctor_schedule_date` (`doctor_id`,`schedule_date`),
  ADD KEY `idx_schedule_availability` (`schedule_date`,`is_available`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `doctor_schedules` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `contact_messages_ibfk_2` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
