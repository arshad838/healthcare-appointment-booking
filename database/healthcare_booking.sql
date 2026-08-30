-- Healthcare Appointment Booking System SQL Database Schema
-- Database name: healthcare_booking

CREATE DATABASE IF NOT EXISTS `healthcare_booking` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `healthcare_booking`;

-- Table 1: users (Authentication and Base accounts)
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `doctor_schedules`;
DROP TABLE IF EXISTS `doctors`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `patients`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'doctor', 'patient') NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: patients (Profile details for patients)
CREATE TABLE `patients` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `date_of_birth` DATE NOT NULL,
  `address` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: departments (Clinical departments)
CREATE TABLE `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: doctors (Profile details for doctors)
CREATE TABLE `doctors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `department_id` INT NOT NULL,
  `specialization` VARCHAR(100) NOT NULL,
  `qualification` VARCHAR(255) NOT NULL,
  `experience` INT NOT NULL COMMENT 'Experience in years',
  `consultation_fee` DECIMAL(10, 2) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `bio` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: doctor_schedules (Weekly shifts & availability)
CREATE TABLE `doctor_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `doctor_id` INT NOT NULL,
  `day_of_week` ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `slot_duration` INT NOT NULL DEFAULT 30 COMMENT 'Slot duration in minutes',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_doctor_day` (`doctor_id`, `day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: appointments (Bookings made by patients with doctors)
CREATE TABLE `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `doctor_id` INT NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  INDEX `idx_app_datetime` (`appointment_date`, `appointment_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================================
-- SEED DATA (All passwords are 'password123' hashed with bcrypt)
-- HASH: $2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG
-- =========================================================================

-- 1. Insert Users (Admin, 5 Doctors, 5 Patients)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin@healthcare.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'admin', 'active'),
-- Doctors
(2, 'Dr. John Smith', 'john.smith@healthcare.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'doctor', 'active'),
(3, 'Dr. Sarah Johnson', 'sarah.johnson@healthcare.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'doctor', 'active'),
(4, 'Dr. Michael Lee', 'michael.lee@healthcare.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'doctor', 'active'),
(5, 'Dr. Emily Davis', 'emily.davis@healthcare.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'doctor', 'active'),
(6, 'Dr. David Wilson', 'david.wilson@healthcare.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'doctor', 'active'),
-- Patients
(7, 'Alice Brown', 'alice.brown@example.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'patient', 'active'),
(8, 'Bob Miller', 'bob.miller@example.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'patient', 'active'),
(9, 'Charlie Davis', 'charlie.davis@example.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'patient', 'active'),
(10, 'Diana Evans', 'diana.evans@example.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'patient', 'active'),
(11, 'Ethan Wright', 'ethan.wright@example.com', '$2y$10$Ye44QKtH/g6G6PlITs3Xc.EKisAYI4aMDyIaHeWbQBtwPybCqxlDG', 'patient', 'active');

-- 2. Insert Patients Profile Details
INSERT INTO `patients` (`id`, `user_id`, `phone`, `gender`, `date_of_birth`, `address`) VALUES
(1, 7, '+15550100201', 'Female', '1992-04-12', '123 Maple Street, New York, NY'),
(2, 8, '+15550100202', 'Male', '1985-08-23', '456 Oak Avenue, Brooklyn, NY'),
(3, 9, '+15550100203', 'Male', '1998-11-05', '789 Pine Road, Queens, NY'),
(4, 10, '+15550100204', 'Female', '1990-01-30', '321 Elm Boulevard, Staten Island, NY'),
(5, 11, '+15550100205', 'Male', '2001-07-15', '567 Birch Lane, Bronx, NY');

-- 3. Insert Departments
INSERT INTO `departments` (`id`, `name`, `description`, `status`) VALUES
(1, 'Cardiology', 'Specialized care for heart conditions, cardiovascular disease prevention, diagnostics, and cardiac rehabilitation.', 'active'),
(2, 'Pediatrics', 'Comprehensive medical care for infants, children, and adolescents, including wellness checks and immunizations.', 'active'),
(3, 'Orthopedics', 'Treatment for musculoskeletal injuries, joint pain, spine disorders, sports medicine, and physical therapy.', 'active'),
(4, 'Dermatology', 'Expert diagnosis and management of skin, hair, and nail disorders, including skin cancer screenings and acne treatment.', 'active'),
(5, 'Neurology', 'Diagnosis and treatment of brain, spinal cord, and nervous system disorders, such as migraines, epilepsy, and neuropathy.', 'active');

-- 4. Insert Doctors Profile Details
INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `specialization`, `qualification`, `experience`, `consultation_fee`, `phone`, `bio`, `image`, `status`) VALUES
(1, 2, 1, 'Cardiovascular Disease', 'MD in Cardiology, FACC (Harvard Medical School)', 15, 150.00, '+15550200101', 'Dr. John Smith is a board-certified cardiologist with over 15 years of experience in preventative medicine, coronary artery disease, and advanced cardiac diagnostics.', 'doctor1.jpg', 'active'),
(2, 3, 2, 'General Pediatrics & Neonatology', 'MD in Pediatrics (Johns Hopkins University)', 12, 100.00, '+15550200102', 'Dr. Sarah Johnson focuses on pediatric wellness, neonatal care, developmental milestones, and chronic childhood illnesses.', 'doctor2.jpg', 'active'),
(3, 4, 3, 'Joint Replacement & Sports Medicine', 'MD, MS in Orthopedic Surgery (Stanford University)', 18, 180.00, '+15550200103', 'Dr. Michael Lee specializes in minimally invasive joint replacements, sports injuries, and orthopedic reconstructive surgery.', 'doctor3.jpg', 'active'),
(4, 5, 4, 'Medical and Cosmetic Dermatology', 'MD in Dermatology (Yale School of Medicine)', 8, 120.00, '+15550200104', 'Dr. Emily Davis is passionate about dermatological health, covering everything from skin cancer detection to custom acne treatment plans.', 'doctor4.jpg', 'active'),
(5, 6, 5, 'Clinical Neurology', 'MD, PhD in Neurological Sciences (University of Pennsylvania)', 20, 200.00, '+15550200105', 'Dr. David Wilson is an expert in neurological disorders, specializing in Alzheimer disease management, sleep medicine, and stroke care.', 'doctor5.jpg', 'active');

-- 5. Insert Doctor Schedules (Availability)
INSERT INTO `doctor_schedules` (`doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`, `status`) VALUES
(1, 'Monday', '09:00:00', '13:00:00', 30, 'active'),
(1, 'Wednesday', '09:00:00', '13:00:00', 30, 'active'),
(2, 'Tuesday', '10:00:00', '14:00:00', 30, 'active'),
(2, 'Thursday', '10:00:00', '14:00:00', 30, 'active'),
(3, 'Monday', '13:00:00', '17:00:00', 30, 'active'),
(3, 'Friday', '13:00:00', '17:00:00', 30, 'active'),
(4, 'Tuesday', '14:00:00', '18:00:00', 30, 'active'),
(4, 'Wednesday', '14:00:00', '18:00:00', 30, 'active'),
(5, 'Thursday', '09:00:00', '12:00:00', 30, 'active'),
(5, 'Friday', '09:00:00', '12:00:00', 30, 'active');

-- 6. Insert Sample Appointments
INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason`, `status`, `notes`) VALUES
(1, 1, '2026-09-07', '09:30:00', 'Routine cardiac wellness checkup.', 'Approved', 'Patient to bring latest lab results.'),
(2, 2, '2026-09-08', '10:30:00', 'Annual physical check for my son.', 'Pending', NULL),
(3, 3, '2026-09-07', '14:00:00', 'Chronic knee pain consultation.', 'Completed', 'Patient advised to start physical therapy and schedule follow up.'),
(4, 4, '2026-09-08', '15:00:00', 'Skin rash assessment.', 'Rejected', 'Doctor unavailable due to emergency hospital surgery.'),
(5, 5, '2026-09-10', '10:00:00', 'Migraine follow up examination.', 'Cancelled', 'Patient cancelled due to scheduling conflict.');
