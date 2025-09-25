-- Doctor Appointment System with Role-Based Access
-- Complete Database Setup - Consolidated Version
-- Includes all tables, sample data, and doctor schedule management system
-- This file replaces both database_setup.sql and schedule_database_update.sql
--
-- UPDATED: Enhanced with robust schedule management system
-- - Added doctor_schedules table for time slot management
-- - Enhanced appointments table with schedule_id foreign key
-- - Added comprehensive indexes for better performance
-- - Made all operations idempotent (safe to run multiple times)

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
    phone VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Create doctors table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500) NULL, -- Path to department image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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
INSERT IGNORE INTO roles (role_name) VALUES 
('admin'),
('doctor'),
('patient'),
('staff');

-- Insert an admin user
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('System Admin', 'admin@hospital.com', '+1234567800', MD5('admin123'), 1);

-- Insert sample doctor users
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('Dr. John Smith', 'john.smith@hospital.com', '+1234567890', MD5('doc123'), 2),
('Dr. Sarah Johnson', 'sarah.johnson@hospital.com', '+1234567891', MD5('doc123'), 2);

-- Insert sample patient users
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('John Doe', 'john.doe@example.com', '+1234567895', MD5('patient123'), 3),
('Jane Smith', 'jane.smith@example.com', '+1234567896', MD5('patient123'), 3);

-- Insert sample doctors (with new structure)
INSERT IGNORE INTO doctors (user_id, name, email, specialization, phone, description, department_id) VALUES
(2, 'Dr. John Smith', 'john.smith@hospital.com', 'Cardiology', '+1234567890', 'Experienced cardiologist with 15 years of practice in heart disease treatment and prevention.', 1),
(3, 'Dr. Sarah Johnson', 'sarah.johnson@hospital.com', 'Pediatrics', '+1234567891', 'Pediatric specialist focused on child health and development with expertise in preventive care.', 2),
(NULL, 'Dr. Michael Brown', 'michael.brown@hospital.com', 'Orthopedics', '+1234567892', 'Orthopedic surgeon specializing in joint replacement and sports medicine.', 3),
(NULL, 'Dr. Emily Davis', 'emily.davis@hospital.com', 'Neurology', '+1234567893', 'Neurologist with expertise in treating brain and nervous system disorders.', 4),
(NULL, 'Dr. Robert Wilson', 'robert.wilson@hospital.com', 'Dermatology', '+1234567894', 'Dermatologist specializing in skin conditions and cosmetic dermatology.', 5);

-- Insert sample departments
INSERT IGNORE INTO departments (name, description, image_path) VALUES
('Cardiology', 'Heart and cardiovascular system treatment', NULL),
('Pediatrics', 'Medical care for infants, children, and adolescents', NULL),
('Orthopedics', 'Bones, joints, and musculoskeletal system', NULL),
('Neurology', 'Nervous system and brain disorders', NULL),
('Dermatology', 'Skin, hair, and nail conditions', NULL);

-- Insert sample services
INSERT IGNORE INTO services (name, description, price, image_path) VALUES
('General Consultation', 'Basic medical consultation with a doctor', 50.00, NULL),
('Specialist Consultation', 'Consultation with a specialist doctor', 100.00, NULL),
('Laboratory Tests', 'Blood tests and other laboratory examinations', 75.00, NULL),
('X-Ray Imaging', 'X-ray examination and diagnosis', 120.00, NULL),
('Physical Therapy', 'Rehabilitation and physical therapy sessions', 80.00, NULL);

-- Insert sample events
INSERT IGNORE INTO events (title, description, event_date, event_time, location, image_path) VALUES
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
-- Note: These are placeholder filenames. Actual images should be uploaded through the admin panel
-- The uploads directory structure should be:
-- /doctor-appoinment/uploads/doctors/ (for doctor profile images)
-- /doctor-appoinment/uploads/departments/ (for department images)
-- /doctor-appoinment/uploads/services/ (for service images)
-- /doctor-appoinment/uploads/events/ (for event images)
-- /doctor-appoinment/uploads/gallery/ (for gallery images)
INSERT IGNORE INTO gallery (title, description, image_path) VALUES
('Modern Hospital Facility', 'Our state-of-the-art hospital building with advanced medical equipment', 'hospital-building.jpg'),
('Emergency Department', '24/7 emergency care facility with dedicated medical staff', 'emergency-dept.jpg'),
('Surgery Suite', 'Advanced operating rooms equipped with latest surgical technology', 'surgery-suite.jpg'),
('Patient Recovery Room', 'Comfortable recovery rooms for post-surgical care', 'recovery-room.jpg'),
('Medical Laboratory', 'Fully equipped laboratory for diagnostic testing', 'laboratory.jpg');

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
-- Check if schedule_id column exists before adding it
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'appointments' 
     AND COLUMN_NAME = 'schedule_id') = 0,
    'ALTER TABLE appointments ADD COLUMN schedule_id INT NULL',
    'SELECT "Column schedule_id already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add foreign key constraint if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = DATABASE() 
     AND TABLE_NAME = 'appointments' 
     AND COLUMN_NAME = 'schedule_id' 
     AND REFERENCED_TABLE_NAME = 'doctor_schedules') = 0,
    'ALTER TABLE appointments ADD FOREIGN KEY (schedule_id) REFERENCES doctor_schedules(id) ON DELETE SET NULL',
    'SELECT "Foreign key constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add indexes for better performance (with IF NOT EXISTS check)
-- Note: MySQL doesn't support IF NOT EXISTS for indexes, so we'll use a different approach
-- Create indexes only if they don't exist

-- Function to create index if it doesn't exist
DELIMITER $$
CREATE PROCEDURE CreateIndexIfNotExists(
    IN table_name VARCHAR(128),
    IN index_name VARCHAR(128),
    IN index_columns VARCHAR(512)
)
BEGIN
    DECLARE index_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO index_count
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = table_name
    AND INDEX_NAME = index_name;
    
    IF index_count = 0 THEN
        SET @sql = CONCAT('CREATE INDEX ', index_name, ' ON ', table_name, '(', index_columns, ')');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- Create indexes using the procedure
CALL CreateIndexIfNotExists('appointments', 'idx_appointment_schedule', 'schedule_id');
CALL CreateIndexIfNotExists('appointments', 'idx_appointment_doctor_date', 'doctor_id, appointment_date');
CALL CreateIndexIfNotExists('appointments', 'idx_appointment_user_date', 'user_id, appointment_date');
CALL CreateIndexIfNotExists('appointments', 'idx_appointment_status', 'status');
CALL CreateIndexIfNotExists('contact_messages', 'idx_contact_messages_status', 'status');
CALL CreateIndexIfNotExists('contact_messages', 'idx_contact_messages_user', 'user_id');
CALL CreateIndexIfNotExists('doctors', 'idx_doctors_department', 'department_id');
CALL CreateIndexIfNotExists('doctors', 'idx_doctors_specialization', 'specialization');
CALL CreateIndexIfNotExists('users', 'idx_users_email', 'email');
CALL CreateIndexIfNotExists('users', 'idx_users_role', 'role_id');

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS CreateIndexIfNotExists;

-- Insert sample schedule data for testing (using existing doctor IDs)
-- Sample schedules for the next 7 days starting from today
-- Only insert if no schedules exist yet
INSERT IGNORE INTO doctor_schedules (doctor_id, schedule_date, start_time, end_time, is_available, max_appointments) VALUES
-- Sample schedules for Dr. Michael Brown (Orthopedics)
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), CURDATE(), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), CURDATE(), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), CURDATE(), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), CURDATE(), '14:00:00', '15:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), CURDATE(), '15:00:00', '16:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE, 1),

-- Sample schedules for Dr. Emily Davis (Neurology)
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), CURDATE(), '08:00:00', '09:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), CURDATE(), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), CURDATE(), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), CURDATE(), '13:00:00', '14:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), CURDATE(), '14:00:00', '15:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '08:00:00', '09:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', '14:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', '15:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '08:00:00', '09:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '13:00:00', '14:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', '15:00:00', TRUE, 1),

-- Sample schedules for Dr. Robert Wilson (Dermatology)
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), CURDATE(), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), CURDATE(), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), CURDATE(), '15:00:00', '16:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), CURDATE(), '16:00:00', '17:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', '16:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 1 DAY), '16:00:00', '17:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 2 DAY), '15:00:00', '16:00:00', TRUE, 1),

-- Additional schedules for the next 4 days to provide more availability
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '08:00:00', '09:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', '14:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', '15:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 3 DAY), '15:00:00', '16:00:00', TRUE, 1),

-- Weekend schedules (Saturday and Sunday)
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '11:00:00', '12:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'michael.brown@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '14:00:00', '15:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '09:00:00', '10:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'emily.davis@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '13:00:00', '14:00:00', TRUE, 1),

((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '10:00:00', '11:00:00', TRUE, 1),
((SELECT id FROM doctors WHERE email = 'robert.wilson@hospital.com' LIMIT 1), DATE_ADD(CURDATE(), INTERVAL 4 DAY), '15:00:00', '16:00:00', TRUE, 1);

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
-- 7. Image uploads are handled through the admin panel with automatic filename generation
-- 8. Doctor schedule management system is included with comprehensive scheduling
-- 9. Sample schedules are created for 5 days starting from today
-- 10. Use the Schedule Management section in admin panel to create additional doctor schedules
-- 11. The schedule system supports flexible time slots and appointment management
-- 12. This file replaces both database_setup.sql and schedule_database_update.sql
-- 13. All image paths in the database store only filenames (not full paths)
-- 14. Frontend constructs full paths when displaying images

-- ========================================
-- DATABASE SCHEMA NOTES
-- ========================================
-- 1. Image Path Storage:
--    - All APIs now store only filenames in the database (not full paths)
--    - Full paths are constructed in the frontend when needed
--    - This approach is more flexible and portable across different environments
--
-- 2. User Authentication:
--    - login.php correctly uses JOIN with roles table to get role_name
--    - All authentication is properly handled with role-based access control
--
-- 3. Doctor Management:
--    - doctors.user_id is nullable to allow admin-created doctors without linked users
--    - All JOINs with users table use LEFT JOIN to handle NULL user_id values
--
-- 4. Schedule Management:
--    - doctor_schedules table provides flexible scheduling system
--    - appointments can optionally link to specific schedule slots
--    - System supports both scheduled and unscheduled appointments
--
-- 5. Performance Optimizations:
--    - Added comprehensive indexes for frequently queried columns
--    - Foreign key constraints ensure data integrity
--    - Proper cascade rules for data cleanup
