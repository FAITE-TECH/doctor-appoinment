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

-- Insert roles
INSERT IGNORE INTO roles (id, role_name) VALUES
(1, 'admin'),
(2, 'doctor'),
(3, 'patient'),
(4, 'staff');

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

-- Create schedules table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    max_appointments INT DEFAULT 20,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id),
    UNIQUE KEY unique_doctor_schedule (doctor_id, day_of_week)
);

-- Insert sample schedules
INSERT IGNORE INTO schedules (doctor_id, day_of_week, start_time, end_time, max_appointments) VALUES
(1, 'Monday', '09:00:00', '17:00:00', 20),
(1, 'Wednesday', '09:00:00', '17:00:00', 20),
(1, 'Friday', '09:00:00', '17:00:00', 20),
(2, 'Tuesday', '10:00:00', '18:00:00', 15),
(2, 'Thursday', '10:00:00', '18:00:00', 15),
(2, 'Saturday', '10:00:00', '16:00:00', 10),
(3, 'Monday', '08:00:00', '16:00:00', 18),
(3, 'Thursday', '08:00:00', '16:00:00', 18),
(3, 'Saturday', '09:00:00', '15:00:00', 12),
(4, 'Tuesday', '11:00:00', '19:00:00', 16),
(4, 'Wednesday', '11:00:00', '19:00:00', 16),
(4, 'Friday', '11:00:00', '19:00:00', 16);

-- Create appointments table
CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    patient_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id),
    FOREIGN KEY (patient_id) REFERENCES users(id)
);

-- Insert sample appointments
INSERT IGNORE INTO appointments (schedule_id, patient_id, appointment_date, appointment_time, reason, status) VALUES
(1, 6, '2025-10-06', '09:30:00', 'Regular cardiac checkup', 'confirmed'),
(1, 7, '2025-10-06', '10:30:00', 'Blood pressure monitoring', 'confirmed'),
(2, 8, '2025-10-08', '14:00:00', 'Follow-up consultation', 'pending'),
(4, 9, '2025-10-07', '11:00:00', 'Pediatric vaccination', 'confirmed'),
(7, 10, '2025-10-06', '09:00:00', 'Neurological assessment', 'confirmed');

-- Create contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create gallery table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(500) NOT NULL,
    category ENUM('facilities', 'events', 'staff', 'other') DEFAULT 'other',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample gallery items
INSERT IGNORE INTO gallery (title, description, image, category) VALUES
('Modern Operating Theater', 'State-of-the-art operating facility with latest equipment', 'gallery/modern-ot.jpg', 'facilities'),
('Emergency Department', '24/7 emergency care unit with skilled medical staff', 'gallery/emergency.jpg', 'facilities'),
('Medical Conference 2025', 'Annual medical conference with international speakers', 'gallery/conference.jpg', 'events'),
('Community Health Camp', 'Free health checkup camp for local community', 'gallery/health-camp.jpg', 'events'),
('Medical Team', 'Our dedicated team of healthcare professionals', 'gallery/medical-team.jpg', 'staff');

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(255),
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample events
INSERT IGNORE INTO events (title, description, date, time, location, image) VALUES
('Health Awareness Seminar', 'Join us for an informative session on cardiovascular health', '2025-10-15', '10:00:00', 'Main Auditorium', 'events/seminar.jpg'),
('Free Dental Clinic', 'Free dental checkup and cleaning for children under 12', '2025-10-20', '09:00:00', 'Dental Department', 'events/dental-clinic.jpg'),
('Blood Donation Camp', 'Voluntary blood donation drive in association with National Blood Bank', '2025-10-25', '08:00:00', 'Hospital Ground Floor', 'events/blood-donation.jpg'),
('World Diabetes Day', 'Free diabetes screening and consultation with our endocrinologists', '2025-11-14', '09:00:00', 'Diabetes Care Center', 'events/diabetes-day.jpg'),
('Mental Health Workshop', 'Understanding and managing stress in modern life', '2025-11-20', '14:00:00', 'Conference Hall', 'events/mental-health.jpg');

-- Insert sample contact messages
INSERT IGNORE INTO contact_messages (name, email, phone, subject, message) VALUES
('Amal Perera', 'amal.perera@gmail.com', '+94771234567', 'General Inquiry', 'I would like to know more about your cardiology services and available time slots.'),
('Malini Silva', 'malini.silva@yahoo.com', '+94772345678', 'Appointment Request', 'Need urgent appointment with a pediatrician for my child''s vaccination.'),
('Rohitha Fernando', 'rohitha.f@gmail.com', '+94773456789', 'Feedback', 'Excellent service received from Dr. Sarah Anderson. Very professional and caring.'),
('Priyanka Bandara', 'priyanka.b@gmail.com', '+94774567890', 'Insurance Query', 'Do you accept Softlogic Life insurance for treatments?'),
('Dinesh Kumar', 'dinesh.k@gmail.com', '+94775678901', 'Facility Information', 'What are your laboratory operating hours during weekends?');

-- Create doctors table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(500) NULL, -- Path to department image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample departments
INSERT IGNORE INTO departments (name, description, image) VALUES
('Cardiology', 'Specialized in heart and cardiovascular system treatment with state-of-the-art facilities', 'departments/68d21513954da1.33412325.png'),
('Pediatrics', 'Expert care for children from newborns to adolescents with child-friendly environment', 'departments/68d5e964e2d704.72367238.png'),
('Neurology', 'Specialized treatment for brain and nervous system disorders using advanced diagnostic tools', 'departments/68ca7ea5c8c1e7.29307730.png'),
('Orthopedics', 'Treatment for bone and joint related conditions with modern rehabilitation facilities', 'departments/68cb80a099bdb3.86796387.png'),
('Dermatology', 'Comprehensive skin care and treatment with latest laser technology', 'departments/68c0d834476f82.50421784.png'),
('ENT', 'Expert ear, nose, and throat care with advanced surgical capabilities', 'departments/68c0e4ceb496c7.84744770.png');

CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    department_id INT NOT NULL,
    description TEXT,
    achievements TEXT,
    experience VARCHAR(255),
    qualification TEXT,
    languages VARCHAR(255),
    booking_time VARCHAR(255),
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Insert sample doctors
INSERT IGNORE INTO doctors (user_id, department_id, description, achievements, experience, qualification, languages, booking_time, image) VALUES 
(2, 1, 'Specializing in cardiac care with expertise in interventional cardiology', 'Best Cardiac Surgeon Award 2020, Published in leading medical journals', '15 years', 'MBBS, MD (Cardiology), FRCP (UK)', 'English, Sinhala, Tamil', '9:00 AM - 5:00 PM', 'doctors/68bfa70da49150.89373199.png'),
(3, 2, 'Expert in pediatric care and child development', 'Child Healthcare Excellence Award 2021, International certifications', '12 years', 'MBBS, MD (Pediatrics), DCH (London)', 'English, Sinhala', '10:00 AM - 6:00 PM', 'doctors/68bfa75a88dc22.52333983.png'),
(4, 3, 'Experienced in treating various neurological conditions', 'Neurology Research Award 2019, Multiple publications', '18 years', 'MBBS, MD (Neurology), PhD (Neuroscience)', 'English, Sinhala', '8:00 AM - 4:00 PM', 'doctors/68bfa779177289.95744027.png'),
(5, 4, 'Specialist in orthopedic surgery and sports medicine', 'Sports Medicine Achievement Award 2022', '10 years', 'MBBS, MD (Orthopedics), FRCS (Ortho)', 'English, Sinhala', '11:00 AM - 7:00 PM', 'doctors/68bfa7e954d607.09177931.png');
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

-- Insert roles (essential data, not sample)
INSERT IGNORE INTO roles (role_name) VALUES 
('admin'),
('doctor'),
('patient'),
('staff');

-- Insert administrator account (essential data, not sample)
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('System Admin', 'admin@spchospital.com', '+94770000000', MD5('admin@SPC2024'), 1);

-- Insert sample users (doctors)
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('Dr. Sarah Anderson', 'sarah.anderson@spchospital.com', '+94771234567', MD5('DrAnderson@2024'), 2),
('Dr. James Chen', 'james.chen@spchospital.com', '+94772345678', MD5('DrChen@2024'), 2),
('Dr. Emily Roberts', 'emily.roberts@spchospital.com', '+94773456789', MD5('DrRoberts@2024'), 2),
('Dr. Michael Thompson', 'michael.thompson@spchospital.com', '+94774567890', MD5('DrThompson@2024'), 2);

-- Insert sample users (patients)
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('Kasun Perera', 'kasun.p@gmail.com', '+94775678901', MD5('Patient@2024'), 3),
('Chamari Silva', 'chamari.s@gmail.com', '+94776789012', MD5('Patient@2024'), 3),
('Nimal Fernando', 'nimal.f@gmail.com', '+94777890123', MD5('Patient@2024'), 3),
('Kumari Bandara', 'kumari.b@gmail.com', '+94778901234', MD5('Patient@2024'), 3),
('Rajitha Dissanayake', 'rajitha.d@gmail.com', '+94779012345', MD5('Patient@2024'), 3);

-- Insert sample users (staff)
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
('Malini Seneviratne', 'malini.reception@spchospital.com', '+94761234567', MD5('Staff@2024'), 4),
('Pradeep Kumara', 'pradeep.nurse@spchospital.com', '+94762345678', MD5('Staff@2024'), 4),
('Dilrukshi Peris', 'dilrukshi.lab@spchospital.com', '+94763456789', MD5('Staff@2024'), 4);

-- Insert sample departments with actual images
INSERT IGNORE INTO departments (name, description, image_path) VALUES
('Cardiology', 'Specialized in diagnosing and treating heart conditions, including advanced cardiac procedures and preventive cardiology.', '68bfa620cbbc89.80189984.png'),
('Neurology', 'Expert care for disorders of the brain, spine, and nervous system, featuring advanced diagnostic and treatment facilities.', '68bfa6367d0870.91034567.png'),
('Orthopedics', 'Comprehensive care for musculoskeletal conditions, sports injuries, and joint replacements with rehabilitation services.', '68c0d834476f82.50421784.png'),
('Pediatrics', 'Child-focused healthcare from newborn care to adolescent medicine, with specialized pediatric services.', '68c0e4ceb496c7.84744770.png'),
('Dental Care', 'Complete dental services including preventive care, cosmetic dentistry, and oral surgery.', '68ca7ea5c8c1e7.29307730.png'),
('Dermatology', 'Treatment for skin conditions, cosmetic procedures, and advanced dermatological care.', '68cb80a099bdb3.86796387.png'),
('Ophthalmology', 'Comprehensive eye care services including advanced surgical procedures and vision correction.', '68d21513954da1.33412325.png'),
('General Medicine', 'Primary healthcare services, preventive medicine, and management of common medical conditions.', '68d5e964e2d704.72367238.png');

-- Insert sample doctors with actual images and detailed information
INSERT IGNORE INTO doctors (name, email, specialization, phone, description, department_id, image_path) VALUES
('Dr. Sarah Anderson', 'sarah.anderson@spchospital.com', 'Cardiology', '+94771234567', 'Board-certified cardiologist with 15 years of experience in interventional cardiology and heart disease management. Special interest in preventive cardiology.', 1, '68bfa70da49150.89373199.png'),
('Dr. James Chen', 'james.chen@spchospital.com', 'Neurology', '+94772345678', 'Neurologist specializing in stroke treatment and neurodegenerative disorders. Experienced in advanced neurological procedures.', 2, '68bfa75a88dc22.52333983.png'),
('Dr. Emily Roberts', 'emily.roberts@spchospital.com', 'Orthopedics', '+94773456789', 'Orthopedic surgeon focusing on joint replacement and sports medicine. Expert in minimally invasive surgical techniques.', 3, '68bfa779177289.95744027.png'),
('Dr. Michael Thompson', 'michael.thompson@spchospital.com', 'Pediatrics', '+94774567890', 'Pediatrician with expertise in newborn care and childhood development. Advocates for preventive pediatric care.', 4, '68bfa7e954d607.09177931.png'),
('Dr. Lisa Wong', 'lisa.wong@spchospital.com', 'Dental Surgery', '+94775678901', 'Experienced dental surgeon specializing in cosmetic dentistry and complex oral procedures.', 5, '68bfa9f1ed0527.23046499.png'),
('Dr. David Kumar', 'david.kumar@spchospital.com', 'Dermatology', '+94776789012', 'Dermatologist expert in both medical and cosmetic dermatology. Specializes in skin cancer treatment.', 6, '68bfb5bbbed6e7.54568502.png'),
('Dr. Rachel Martinez', 'rachel.martinez@spchospital.com', 'Ophthalmology', '+94777890123', 'Ophthalmologist skilled in cataract surgery and retinal treatments. Pioneer in laser vision correction.', 7, '68bfc00df2ca75.84951850.png'),
('Dr. John Parker', 'john.parker@spchospital.com', 'General Medicine', '+94778901234', 'General physician with extensive experience in managing chronic conditions and preventive care.', 8, '68bfc0235533d6.19808170.png');

-- Insert sample services with realistic pricing and descriptions
INSERT IGNORE INTO services (name, description, price, image_path) VALUES
('General Health Checkup', 'Comprehensive health screening including blood tests, ECG, and physician consultation.', 7500.00, '68c0d850d4bd34.36033583.png'),
('Cardiac Evaluation', 'Complete heart health assessment with ECG, echo, and specialist consultation.', 15000.00, '68c0d8b0ed3824.85548853.png'),
('Pediatric Consultation', 'Child health check-up with growth monitoring and vaccination review.', 5000.00, '68c0d90150f902.55963440.png'),
('Dental Cleaning', 'Professional dental cleaning and oral health assessment.', 4500.00, '68c0d98b0ed382.85548853.png'),
('Eye Examination', 'Comprehensive vision test and eye health screening.', 3500.00, '68c0d9a150f902.55963440.png'),
('Skin Consultation', 'Dermatological assessment and treatment planning.', 4000.00, '68c0d9b0ed3824.85548853.png'),
('Orthopedic Assessment', 'Musculoskeletal evaluation with specialist consultation.', 6000.00, '68c0d9c150f902.55963440.png'),
('Laboratory Services', 'Wide range of diagnostic tests with quick reporting.', 2500.00, '68c0d9d0ed3824.85548853.png');

-- Insert upcoming events with actual dates and detailed information
INSERT IGNORE INTO events (title, description, event_date, event_time, location, image_path) VALUES
('Free Medical Camp', 'Community health screening including basic health checks and consultations.', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'SPC Hospital Main Lobby', '68c0da50d4bd34.36033583.png'),
('Diabetes Awareness Workshop', 'Educational session on diabetes management with expert speakers and free blood sugar testing.', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '14:00:00', 'Conference Hall A', '68c0da8b0ed382.85548853.png'),
('Womens Health Seminar', 'Comprehensive discussion on womens health issues and preventive care.', DATE_ADD(CURDATE(), INTERVAL 21 DAY), '10:00:00', 'Seminar Room 1', '68c0da9150f902.55963440.png'),
('Childrens Health Day', 'Pediatric health check-up and fun health education activities for kids.', DATE_ADD(CURDATE(), INTERVAL 28 DAY), '09:00:00', 'Pediatric Wing', '68c0daa0ed3824.85548853.png');

-- Insert sample gallery images with meaningful descriptions
INSERT IGNORE INTO gallery (title, description, image_path) VALUES
('Modern Hospital Facilities', 'State-of-the-art medical facilities and equipment at SPC Hospital.', '68c0db50d4bd34.36033583.png'),
('Dedicated Medical Team', 'Our team of experienced healthcare professionals providing quality care.', '68c0db8b0ed382.85548853.png'),
('Advanced Surgical Suite', 'Modern operating theaters equipped with the latest medical technology.', '68c0db9150f902.55963440.png'),
('Patient Care Excellence', 'Comfortable patient rooms and caring nursing staff.', '68c0dba0ed3824.85548853.png'),
('Emergency Department', '24/7 emergency care facility with rapid response capabilities.', '68c0dbb0ed3824.85548853.png'),
('Pediatric Care Center', 'Child-friendly environment in our pediatric department.', '68c0dbc150f902.55963440.png');

-- Create contact_messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Link to users table if user is logged in
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
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
