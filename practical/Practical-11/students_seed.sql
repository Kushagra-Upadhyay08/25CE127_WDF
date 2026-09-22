-- =======================================================================
-- StudentHub — Student Management Schema & Seed Data (Practical 11)
-- Minimum 20+ realistic student records for CHARUSAT FTE
-- =======================================================================
CREATE DATABASE IF NOT EXISTS `studenthub`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `studenthub`;
CREATE TABLE IF NOT EXISTS `students` (
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
-- 25 Seeded Records
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
('25CE024', 'Prachi Pandya', 'prachi.pandya@charusat.edu.in', '9724456784', 'female', 'btech-it', 2, 'active')
ON DUPLICATE KEY UPDATE `fullname` = VALUES(`fullname`);
