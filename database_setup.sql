-- Doctor Appointment System with Role-Based Access
-- Complete Database Setup - Consolidated Version
-- Includes all tables, sample data, and doctor schedule management system

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

-- Create users table
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

-- Insert administrator and sample users
INSERT IGNORE INTO users (name, email, phone, password, role_id) VALUES
-- Admin
('System Admin', 'admin@spchospital.com', '+94770000000', MD5('admin@SPC2024'), 1),
-- Doctors
('Dr. Sarah Anderson', 'sarah.anderson@spchospital.com', '+94771234567', MD5('DrAnderson@2024'), 2),
('Dr. James Chen', 'james.chen@spchospital.com', '+94772345678', MD5('DrChen@2024'), 2),
('Dr. Emily Roberts', 'emily.roberts@spchospital.com', '+94773456789', MD5('DrRoberts@2024'), 2),
('Dr. Michael Thompson', 'michael.thompson@spchospital.com', '+94774567890', MD5('DrThompson@2024'), 2),
-- Patients
('Kasun Perera', 'kasun.p@gmail.com', '+94775678901', MD5('Patient@2024'), 3),
('Chamari Silva', 'chamari.s@gmail.com', '+94776789012', MD5('Patient@2024'), 3),
('Nimal Fernando', 'nimal.f@gmail.com', '+94777890123', MD5('Patient@2024'), 3),
('Kumari Bandara', 'kumari.b@gmail.com', '+94778901234', MD5('Patient@2024'), 3),
('Rajitha Dissanayake', 'rajitha.d@gmail.com', '+94779012345', MD5('Patient@2024'), 3),
-- Staff
('Malini Seneviratne', 'malini.reception@spchospital.com', '+94761234567', MD5('Staff@2024'), 4),
('Pradeep Kumara', 'pradeep.nurse@spchospital.com', '+94762345678', MD5('Staff@2024'), 4),
('Dilrukshi Peris', 'dilrukshi.lab@spchospital.com', '+94763456789', MD5('Staff@2024'), 4);

-- Create departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(500) NULL,
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

-- Create doctors table
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

-- Create services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    image VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample services
INSERT IGNORE INTO services (name, description, price, image) VALUES
('General Health Checkup', 'Comprehensive health screening including blood tests, ECG, and physician consultation', 7500.00, 'services/general-checkup.jpg'),
('Cardiac Evaluation', 'Complete heart health assessment with ECG, echo, and specialist consultation', 15000.00, 'services/cardiac-eval.jpg'),
('Pediatric Consultation', 'Child health check-up with growth monitoring and vaccination review', 5000.00, 'services/pediatric.jpg'),
('Laboratory Services', 'Wide range of diagnostic tests with quick reporting', 2500.00, 'services/lab-services.jpg');

-- Create events table
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(255),
    image VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample events
INSERT IGNORE INTO events (title, description, date, time, location, image) VALUES
('Health Awareness Seminar', 'Join us for an informative session on cardiovascular health', '2025-10-15', '10:00:00', 'Main Auditorium', 'events/seminar.jpg'),
('Free Dental Clinic', 'Free dental checkup and cleaning for children under 12', '2025-10-20', '09:00:00', 'Dental Department', 'events/dental-clinic.jpg'),
('Blood Donation Camp', 'Voluntary blood donation drive in association with National Blood Bank', '2025-10-25', '08:00:00', 'Hospital Ground Floor', 'events/blood-donation.jpg'),
('World Diabetes Day', 'Free diabetes screening and consultation with our endocrinologists', '2025-11-14', '09:00:00', 'Diabetes Care Center', 'events/diabetes-day.jpg'),
('Mental Health Workshop', 'Understanding and managing stress in modern life', '2025-11-20', '14:00:00', 'Conference Hall', 'events/mental-health.jpg');

-- Create gallery table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample gallery items
INSERT IGNORE INTO
('Modern Operating Theater', 'State-of-the-art operating facility with latest equipment', 'gallery/modern-ot.jpg'),
('Emergency Department', '24/7 emergency care unit with skilled medical staff', 'gallery/emergency.jpg'),
('Medical Conference 2025', 'Annual medical conference with international speakers', 'gallery/conference.jpg'),
('Community Health Camp', 'Free health checkup camp for local community', 'gallery/health-camp.jpg'),
('Medical Team', 'Our dedicated team of healthcare professionals', 'gallery/medical-team.jpg');

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

-- Insert sample contact messages
INSERT IGNORE INTO contact_messages (name, email, phone, subject, message) VALUES
('Amal Perera', 'amal.perera@gmail.com', '+94771234567', 'General Inquiry', 'I would like to know more about your cardiology services and available time slots.'),
('Malini Silva', 'malini.silva@yahoo.com', '+94772345678', 'Appointment Request', 'Need urgent appointment with a pediatrician for my child''s vaccination.'),
('Rohitha Fernando', 'rohitha.f@gmail.com', '+94773456789', 'Feedback', 'Excellent service received from Dr. Sarah Anderson. Very professional and caring.'),
('Priyanka Bandara', 'priyanka.b@gmail.com', '+94774567890', 'Insurance Query', 'Do you accept Softlogic Life insurance for treatments?'),
('Dinesh Kumar', 'dinesh.k@gmail.com', '+94775678901', 'Facility Information', 'What are your laboratory operating hours during weekends?');

-- Create indexes for better performance
CREATE INDEX idx_appointment_schedule ON appointments(schedule_id);
CREATE INDEX idx_appointment_patient ON appointments(patient_id);
CREATE INDEX idx_doctor_department ON doctors(department_id);
CREATE INDEX idx_user_role ON users(role_id);
CREATE INDEX idx_schedule_doctor ON schedules(doctor_id);