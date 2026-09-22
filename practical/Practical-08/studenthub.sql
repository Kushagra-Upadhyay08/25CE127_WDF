-- =======================================================================
-- StudentHub Database Schema & Seed Data (Practical 8)
-- Subject: ITUE203 Web Development Frameworks
-- Student: Kushagra Upadhyay (25CE127)
-- Database Engine: MySQL / MariaDB (InnoDB)
-- =======================================================================
CREATE DATABASE IF NOT EXISTS `studenthub`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `studenthub`;
-- -----------------------------------------------------------------------
-- Drop existing tables (in reverse foreign key dependency order)
-- -----------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
-- -----------------------------------------------------------------------
-- 1. Table: students
-- Stores comprehensive student profile and enrollment details
-- -----------------------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `roll_no` VARCHAR(20) NOT NULL UNIQUE,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(15) NOT NULL,
  `gender` ENUM('male', 'female', 'other') NOT NULL,
  `course` VARCHAR(50) NOT NULL,
  `year` TINYINT UNSIGNED NOT NULL,
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_course_year` (`course`, `year`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- -----------------------------------------------------------------------
-- 2. Table: events
-- Stores campus event announcements, workshops, and hackathons
-- -----------------------------------------------------------------------
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `event_date` DATE NOT NULL,
  `venue` VARCHAR(100) NOT NULL,
  `max_participants` INT UNSIGNED NOT NULL DEFAULT 100,

  `poster_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('upcoming', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_event_date` (`event_date`),
  INDEX `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- -----------------------------------------------------------------------
-- 3. Table: registrations
-- Associative table resolving M:N relationship between students & events
-- -----------------------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('registered', 'attended', 'cancelled') NOT NULL DEFAULT 'registered',
  UNIQUE KEY `uq_student_event` (`student_id`, `event_id`),
  CONSTRAINT `fk_reg_student` FOREIGN KEY (`student_id`)
    REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reg_event` FOREIGN KEY (`event_id`)
    REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- -----------------------------------------------------------------------
-- 4. Table: users
-- User credentials for authentication and Role-Based Access Control (RBAC)
-- -----------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NULL DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'admin') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `last_login` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_user_student` FOREIGN KEY (`student_id`)
    REFERENCES `students` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- -----------------------------------------------------------------------
-- 5. Table: remember_tokens
-- Secure token storage for persistent login (Remember-Me)
-- -----------------------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `selector` VARCHAR(32) NOT NULL UNIQUE,
  `token_hash` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_selector` (`selector`),
  CONSTRAINT `fk_token_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- -----------------------------------------------------------------------
-- 6. Table: audit_logs
-- Security audit logging for registration, logins, and CRUD events
-- -----------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_action` (`action`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- -----------------------------------------------------------------------
-- STORED PROCEDURES (Advanced Extension)
-- -----------------------------------------------------------------------
DELIMITER //
DROP PROCEDURE IF EXISTS `sp_register_student_event` //
CREATE PROCEDURE `sp_register_student_event`(
    IN p_student_id INT,
    IN p_event_id INT,
    OUT p_result VARCHAR(100)
)
BEGIN
    DECLARE v_current_count INT;
    DECLARE v_max_count INT;
    DECLARE v_already_registered INT;
    -- Check if student already registered
    SELECT COUNT(*) INTO v_already_registered
    FROM `registrations`
    WHERE `student_id` = p_student_id AND `event_id` = p_event_id;
    IF v_already_registered > 0 THEN
        SET p_result = 'ALREADY_REGISTERED';
    ELSE
        -- Check event capacity
        SELECT COUNT(*), e.`max_participants`
        INTO v_current_count, v_max_count
        FROM `events` e

        LEFT JOIN `registrations` r ON e.`id` = r.`event_id` AND r.`status` = 'registered'
        WHERE e.`id` = p_event_id
        GROUP BY e.`id`, e.`max_participants`;
        IF v_current_count >= v_max_count THEN
            SET p_result = 'EVENT_FULL';
        ELSE
            INSERT INTO `registrations` (`student_id`, `event_id`, `status`)
            VALUES (p_student_id, p_event_id, 'registered');
            SET p_result = 'SUCCESS';
        END IF;
    END IF;
END //
DROP PROCEDURE IF EXISTS `sp_get_student_summary` //
CREATE PROCEDURE `sp_get_student_summary`(IN p_student_id INT)
BEGIN
    SELECT 
        s.`id`,
        s.`roll_no`,
        s.`fullname`,
        s.`email`,
        s.`course`,
        s.`year`,
        s.`status`,
        COUNT(r.`id`) AS total_events_registered
    FROM `students` s
    LEFT JOIN `registrations` r ON s.`id` = r.`student_id`
    WHERE s.`id` = p_student_id
    GROUP BY s.`id`;
END //
DELIMITER ;
-- -----------------------------------------------------------------------
-- SEED DATA: students (25 realistic records for CHARUSAT FTE)
-- -----------------------------------------------------------------------
INSERT INTO `students` (`roll_no`, `fullname`, `email`, `mobile`, `gender`, `course`, `year`, `status`) VALUES
('25CE127', 'Kushagra Upadhyay', 'ukushagra8@gmail.com', '9876543210', 'male', 'btech-ce', 3, 'active'),
('25CE001', 'Aarav Patel', 'aarav.patel@charusat.edu.in', '9825123456', 'male', 'btech-cs', 2, 'active'),
('25CE002', 'Diya Shah', 'diya.shah@charusat.edu.in', '9898234567', 'female', 'btech-it', 2, 'active'),
('25CE003', 'Rohan Mehta', 'rohan.mehta@charusat.edu.in', '9723345678', 'male', 'btech-ce', 3, 'active'),
('25CE004', 'Ananya Sharma', 'ananya.sharma@charusat.edu.in', '9879456789', 'female', 'btech-cs', 1, 'active'),
('25CE005', 'Kavya Joshi', 'kavya.joshi@charusat.edu.in', '9426567890', 'female', 'btech-ec', 4, 'active'),
('25CE006', 'Siddharth Dave', 'siddharth.dave@charusat.edu.in', '9824678901', 'male', 'btech-it', 2, 'active'),
('25CE007', 'Isha Trivedi', 'isha.trivedi@charusat.edu.in', '9909789012', 'female', 'btech-ce', 2, 'active'),
('25CE008', 'Yash Vora', 'yash.vora@charusat.edu.in', '9825890123', 'male', 'btech-cs', 3, 'active'),
('25CE009', 'Pooja Panchal', 'pooja.panchal@charusat.edu.in', '9898901234', 'female', 'btech-it', 1, 'active'),
('25CE010', 'Devansh Soni', 'devansh.soni@charusat.edu.in', '9724012345', 'male', 'btech-ce', 4, 'active'),
('25CE011', 'Tanvi Barot', 'tanvi.barot@charusat.edu.in', '9879123450', 'female', 'btech-cs', 2, 'active'),
('25CE012', 'Manan Bhatt', 'manan.bhatt@charusat.edu.in', '9427234561', 'male', 'btech-ec', 3, 'active'),
('25CE013', 'Bhavya Prajapati', 'bhavya.prajapati@charusat.edu.in', '9824345672', 'male', 'btech-it', 3, 'active'),
('25CE014', 'Khushi Gandhi', 'khushi.gandhi@charusat.edu.in', '9904456783', 'female', 'btech-ce', 1, 'active'),
('25CE015', 'Dhruv Desai', 'dhruv.desai@charusat.edu.in', '9825567894', 'male', 'btech-cs', 4, 'active'),
('25CE016', 'Riddhi Parikh', 'riddhi.parikh@charusat.edu.in', '9898678905', 'female', 'btech-it', 2, 'active'),
('25CE017', 'Harsh Solanki', 'harsh.solanki@charusat.edu.in', '9725789016', 'male', 'btech-ec', 2, 'active'),
('25CE018', 'Jhanvi Vyas', 'jhanvi.vyas@charusat.edu.in', '9879890127', 'female', 'btech-cs', 3, 'active'),
('25CE019', 'Karan Makwana', 'karan.makwana@charusat.edu.in', '9428901238', 'male', 'btech-ce', 2, 'active'),
('25CE020', 'Vidhi Raval', 'vidhi.raval@charusat.edu.in', '9825012349', 'female', 'btech-it', 4, 'active'),
('25CE021', 'Nirav Chauhan', 'nirav.chauhan@charusat.edu.in', '9909123451', 'male', 'btech-cs', 1, 'active'),
('25CE022', 'Mansi Shukla', 'mansi.shukla@charusat.edu.in', '9824234562', 'female', 'btech-ec', 3, 'inactive'),
('25CE023', 'Jaydeep Rathod', 'jaydeep.rathod@charusat.edu.in', '9898345673', 'male', 'btech-ce', 4, 'inactive'),
('25CE024', 'Prachi Pandya', 'prachi.pandya@charusat.edu.in', '9724456784', 'female', 'btech-it', 2, 'active');
-- -----------------------------------------------------------------------
-- SEED DATA: events
-- -----------------------------------------------------------------------
INSERT INTO `events` (`title`, `category`, `description`, `event_date`, `venue`, `max_participants`, `status`) VALUES
('WebCraft 2026 Hackathon', 'Technical', 'A 24-hour hackathon focused on modern web engineering, APIs, and 
progressive web apps.', '2026-11-15', 'Central Computer Center, CHARUSAT', 80, 'upcoming'),
('Cloud & DevOps Masterclass', 'Workshop', 'Hands-on training covering Docker containers, CI/CD pipelines, and cloud 
deployment on AWS.', '2026-11-20', 'Seminar Hall 3, FTE', 60, 'upcoming'),
('Spoural Annual Sports Fest', 'Sports', 'Inter-department cricket, football, volleyball, and badminton championship.
', '2026-12-05', 'CHARUSAT Sports Complex', 200, 'upcoming'),
('Cognizance Tech Symposium', 'Technical', 'State-level technical paper presentation, coding competitions, and 
project exhibition.', '2026-12-18', 'Auditorium 1', 150, 'upcoming'),
('AI & Machine Learning Bootcamp', 'Workshop', 'Practical immersion in Python, machine learning workflows, and deep 
neural architectures.', '2026-10-05', 'FTE Lab 7', 50, 'completed'),
('Vrund Cultural Night', 'Cultural', 'Annual campus cultural festival celebrating music, dance, theater, and arts.', 
'2026-12-22', 'Main University Grounds', 500, 'upcoming');
-- -----------------------------------------------------------------------
-- SEED DATA: registrations (Associative M:N instances)
-- -----------------------------------------------------------------------
INSERT INTO `registrations` (`student_id`, `event_id`, `status`) VALUES
(1, 1, 'registered'),
(1, 2, 'registered'),
(1, 4, 'registered'),
(2, 1, 'registered'),
(2, 5, 'attended'),
(3, 1, 'registered'),
(3, 3, 'registered'),
(4, 1, 'registered'),
(5, 2, 'registered'),
(6, 4, 'registered'),
(7, 3, 'registered'),
(8, 1, 'registered'),
(9, 2, 'registered'),
(10, 4, 'registered'),
(11, 1, 'registered');
-- -----------------------------------------------------------------------

-- SEED DATA: users
-- Passwords hashed using bcrypt:
-- Admin: 'admin' / 'admin@charusat.edu.in' -> 'Admin@123'
-- Student: 'kushagra' / 'ukushagra8@gmail.com' -> 'Student@123'
-- -----------------------------------------------------------------------
INSERT INTO `users` (`student_id`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(NULL, 'admin', 'admin@charusat.edu.in', '$2y$10$WWLAOuD6jSLhbIV3ch46/u5hU/JQDs/lgMjzagMt23bavVGHbB4J6', 'admin', 
'active'),
(1, 'kushagra', 'ukushagra8@gmail.com', '$2y$10$zfPdpmgPA5X4XFZgyXuu7ef2Bp8RU889ti9tXHnw6Bes5gK4Pgn0W', 'student', 
'active');
-- -----------------------------------------------------------------------
-- SEED DATA: audit_logs
-- -----------------------------------------------------------------------
INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`) VALUES
(1, 'SYSTEM_INIT', 'Initial database schema creation and seed data loading', '127.0.0.1'),
(2, 'ACCOUNT_REGISTER', 'Student Kushagra Upadhyay registered for StudentHub portal', '127.0.0.1');
