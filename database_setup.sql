-- Doctor Appointment System with Role-Based Access
-- Complete Database Setup - Consolidated Version
-- Includes all tables, sample data, and doctor schedule management system
-- This file replaces both database_setup.sql and schedule_database_update.sql

-- Create database
CREATE DATABASE IF NOT EXISTS doctor;
USE doctor;

-- Create roles table
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_name ENUM('admin', 'doctor', 'patient', 'staff') NOT NULL UNIQUE
);

-- Create users table (linked with roles)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Create doctors table
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Made nullable to allow admin-created doctors without linked users
    name VARCHAR(255) NOT NULL, -- Doctor's name (can be different from user name)
    email VARCHAR(255) NOT NULL, -- Doctor's email (can be different from user email)
    specialization VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    image_path VARCHAR(500) NULL, -- Path to doctor's profile image
    description TEXT NULL, -- Doctor's bio/description
    department_id INT NULL, -- Link to departments table
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);


CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,   -- patient (from users table)
    doctor_id INT NOT NULL, -- doctor (from doctors table)
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
);

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500) NULL, -- Path to department image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    image_path VARCHAR(500) NULL, -- Path to service image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(255),
    image_path VARCHAR(500) NULL, -- Path to event image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create gallery table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    image_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert roles
INSERT INTO roles (role_name) VALUES 
('admin'),
('doctor'),
('patient'),
('staff');

-- Insert an admin user
INSERT INTO users (name, email, password, role_id) VALUES
('System Admin', 'admin@hospital.com', MD5('admin123'), 1);

-- Insert sample doctor users
INSERT INTO users (name, email, password, role_id) VALUES
('Dr. John Smith', 'john.smith@hospital.com', MD5('doc123'), 2),
('Dr. Sarah Johnson', 'sarah.johnson@hospital.com', MD5('doc123'), 2);

-- Insert sample patient users
INSERT INTO users (name, email, password, role_id) VALUES
('John Doe', 'john.doe@example.com', MD5('patient123'), 3),
('Jane Smith', 'jane.smith@example.com', MD5('patient123'), 3);

-- Insert sample doctors (with new structure)
INSERT INTO doctors (user_id, name, email, specialization, phone, description, department_id) VALUES
(2, 'Dr. John Smith', 'john.smith@hospital.com', 'Cardiology', '+1234567890', 'Experienced cardiologist with 15 years of practice in heart disease treatment and prevention.', 1),
(3, 'Dr. Sarah Johnson', 'sarah.johnson@hospital.com', 'Pediatrics', '+1234567891', 'Pediatric specialist focused on child health and development with expertise in preventive care.', 2),
(NULL, 'Dr. Michael Brown', 'michael.brown@hospital.com', 'Orthopedics', '+1234567892', 'Orthopedic surgeon specializing in joint replacement and sports medicine.', 3),
(NULL, 'Dr. Emily Davis', 'emily.davis@hospital.com', 'Neurology', '+1234567893', 'Neurologist with expertise in treating brain and nervous system disorders.', 4),
(NULL, 'Dr. Robert Wilson', 'robert.wilson@hospital.com', 'Dermatology', '+1234567894', 'Dermatologist specializing in skin conditions and cosmetic dermatology.', 5);

-- Insert sample departments
INSERT INTO departments (name, description, image_path) VALUES
('Cardiology', 'Heart and cardiovascular system treatment', NULL),
('Pediatrics', 'Medical care for infants, children, and adolescents', NULL),
('Orthopedics', 'Bones, joints, and musculoskeletal system', NULL),
('Neurology', 'Nervous system and brain disorders', NULL),
('Dermatology', 'Skin, hair, and nail conditions', NULL);

-- Insert sample services
INSERT INTO services (name, description, price, image_path) VALUES
('General Consultation', 'Basic medical consultation with a doctor', 50.00, NULL),
('Specialist Consultation', 'Consultation with a specialist doctor', 100.00, NULL),
('Laboratory Tests', 'Blood tests and other laboratory examinations', 75.00, NULL),
('X-Ray Imaging', 'X-ray examination and diagnosis', 120.00, NULL),
('Physical Therapy', 'Rehabilitation and physical therapy sessions', 80.00, NULL);

-- Insert sample events
INSERT INTO events (title, description, event_date, event_time, location, image_path) VALUES
('Health Awareness Seminar', 'Learn about preventive healthcare and wellness', '2025-09-10', '14:00:00', 'Main Conference Hall', NULL),
('Blood Donation Drive', 'Community blood donation event', '2025-09-15', '10:00:00', 'Hospital Lobby', NULL),
('Diabetes Management Workshop', 'Educational workshop for diabetes patients', '2025-09-20', '15:30:00', 'Seminar Room A', NULL);

-- Create contact_messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Link to users table if user is logged in
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(500) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    admin_reply TEXT NULL,
    replied_by INT NULL, -- Admin user who replied
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample gallery images
-- Note: These are placeholder paths. Actual images should be uploaded through the admin panel
-- The uploads directory structure should be:
-- /doctor-appoinment/uploads/doctors/ (for doctor profile images)
-- /doctor-appoinment/uploads/departments/ (for department images)
-- /doctor-appoinment/uploads/services/ (for service images)
-- /doctor-appoinment/uploads/events/ (for event images)
-- /doctor-appoinment/uploads/gallery/ (for gallery images)
INSERT INTO gallery (title, description, image_path) VALUES
('Modern Hospital Facility', 'Our state-of-the-art hospital building with advanced medical equipment', '/doctor-appoinment/uploads/gallery/hospital-building.jpg'),
('Emergency Department', '24/7 emergency care facility with dedicated medical staff', '/doctor-appoinment/uploads/gallery/emergency-dept.jpg'),
('Surgery Suite', 'Advanced operating rooms equipped with latest surgical technology', '/doctor-appoinment/uploads/gallery/surgery-suite.jpg'),
('Patient Recovery Room', 'Comfortable recovery rooms for post-surgical care', '/doctor-appoinment/uploads/gallery/recovery-room.jpg'),
('Medical Laboratory', 'Fully equipped laboratory for diagnostic testing', '/doctor-appoinment/uploads/gallery/laboratory.jpg');

-- ========================================
-- DOCTOR SCHEDULE MANAGEMENT SYSTEM
-- ========================================

-- Create doctor_schedules table for managing doctor availability
CREATE TABLE IF NOT EXISTS doctor_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    schedule_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    max_appointments INT DEFAULT 1,
    current_appointments INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_schedule (doctor_id, schedule_date, start_time, end_time)
);

-- Create indexes for better performance
CREATE INDEX idx_doctor_schedule_date ON doctor_schedules(doctor_id, schedule_date);
CREATE INDEX idx_schedule_availability ON doctor_schedules(schedule_date, is_available);

-- Update appointments table to link with doctor_schedules
-- Add a new column to track which schedule slot the appointment uses
ALTER TABLE appointments 
ADD COLUMN schedule_id INT NULL,
ADD FOREIGN KEY (schedule_id) REFERENCES doctor_schedules(id) ON DELETE SET NULL;

-- Add index for better performance
CREATE INDEX idx_appointment_schedule ON appointments(schedule_id);
CREATE INDEX idx_appointment_doctor_date ON appointments(doctor_id, appointment_date);

-- Insert sample schedule data for testing (using existing doctor IDs)
-- Sample schedules for the next 7 days starting from today
INSERT INTO doctor_schedules (doctor_id, schedule_date, start_time, end_time, is_available, max_appointments) VALUES
-- Sample schedules for Doctor ID 6 (Dr. Michael Brown - Orthopedics)
(6, CURDATE(), '09:00:00', '10:00:00', TRUE, 1),
(6, CURDATE(), '10:00:00', '11:00:00', TRUE, 1),
(6, CURDATE(), '11:00:00', '12:00:00', TRUE, 1),
(6, CURDATE(), '14:00:00', '15:00:00', TRUE, 1),
(6, CURDATE(), '15:00:00', '16:00:00', TRUE, 1),

(6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE, 1),

(6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE, 1),

-- Sample schedules for Doctor ID 7 (Dr. Emily Davis - Neurology)
(7, CURDATE(), '08:00:00', '09:00:00', TRUE, 1),
(7, CURDATE(), '09:00:00', '10:00:00', TRUE, 1),
(7, CURDATE(), '10:00:00', '11:00:00', TRUE, 1),
(7, CURDATE(), '13:00:00', '14:00:00', TRUE, 1),
(7, CURDATE(), '14:00:00', '15:00:00', TRUE, 1),

(7, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', '09:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', '14:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE, 1),

(7, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00:00', '09:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '14:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE, 1),

-- Sample schedules for Doctor ID 8 (Dr. Robert Wilson - Dermatology)
(8, CURDATE(), '10:00:00', '11:00:00', TRUE, 1),
(8, CURDATE(), '11:00:00', '12:00:00', TRUE, 1),
(8, CURDATE(), '15:00:00', '16:00:00', TRUE, 1),
(8, CURDATE(), '16:00:00', '17:00:00', TRUE, 1),

(8, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', TRUE, 1),

(8, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '16:00:00', '17:00:00', TRUE, 1),

-- Additional schedules for the next 4 days to provide more availability
(6, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE, 1),

(7, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '08:00:00', '09:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '14:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE, 1),

(8, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', '12:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE, 1),

-- Weekend schedules (Saturday and Sunday)
(6, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', '12:00:00', TRUE, 1),
(6, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '14:00:00', '15:00:00', TRUE, 1),

(7, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', '10:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE, 1),
(7, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '13:00:00', '14:00:00', TRUE, 1),

(8, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE, 1),
(8, DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:00:00', '16:00:00', TRUE, 1);

-- ========================================
-- SETUP INSTRUCTIONS
-- ========================================
-- 1. Run this SQL script to create the complete database and all tables
-- 2. Ensure the uploads directory structure exists:
--    mkdir -p /path/to/doctor-appoinment/uploads/{doctors,departments,services,events,gallery}
-- 3. Set proper permissions on uploads directory:
--    chmod 755 /path/to/doctor-appoinment/uploads
--    chmod 755 /path/to/doctor-appoinment/uploads/*
-- 4. Default admin login credentials:
--    Email: admin@hospital.com
--    Password: admin123
-- 5. The system supports both user-linked doctors and standalone doctors
-- 6. All tables include created_at and updated_at timestamps for audit trails
-- 7. Image uploads are handled through the admin panel with automatic path generation
-- 8. Doctor schedule management system is included with comprehensive scheduling
-- 9. Sample schedules are created for 5 days starting from today
-- 10. Use the Schedule Management section in admin panel to create additional doctor schedules
-- 11. The schedule system supports flexible time slots and appointment management
-- 12. This file replaces both database_setup.sql and schedule_database_update.sql

-- ========================================
-- KNOWN API INCONSISTENCIES TO FIX
-- ========================================
-- 1. Backend/api/login.php line 27: 
--    SELECTs 'role' column from users table, but should JOIN with roles table
--    Current: "SELECT id, name, email, password, role FROM users WHERE email = ?"
--    Should be: "SELECT u.id, u.name, u.email, u.password, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?"
--
-- 2. Backend/api/admin.php line 80:
--    JOINs doctors with users table, but doctors.user_id can be NULL
--    Current: "JOIN users du ON d.user_id = du.id"
--    Should be: "LEFT JOIN users du ON d.user_id = du.id"
--    Or use doctors.name directly instead of du.name
--
-- 3. Image path storage inconsistency:
--    Some APIs store full paths (/doctor-appoinment/uploads/...)
--    Others store just filenames
--    Consider standardizing to one approach
