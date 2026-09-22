-- =======================================================================
-- StudentHub — Event Management Schema & Seed Data (Practical 12)
-- =======================================================================
CREATE DATABASE IF NOT EXISTS `studenthub`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `studenthub`;
CREATE TABLE IF NOT EXISTS `events` (
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
  INDEX `idx_category` (`category`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `events` (`id`, `title`, `category`, `description`, `event_date`, `venue`, `max_participants`, 
`poster_url`, `status`) VALUES
(1, 'WebCraft 2026 Hackathon', 'Technical', 'A 24-hour hackathon focused on modern web engineering, APIs, responsive 
designs, and progressive web apps at CHARUSAT FTE.', '2026-11-15', 'Central Computer Center, CHARUSAT', 80, 'uploads/
event_sample_hackathon.jpg', 'upcoming'),
(2, 'Cloud & DevOps Masterclass', 'Workshop', 'Hands-on intensive masterclass covering Docker containers, CI/CD 
pipelines, and cloud infrastructure deployment.', '2026-11-20', 'Seminar Hall 3, FTE', 60, 'uploads/
event_sample_workshop.jpg', 'upcoming'),
(3, 'Spoural Annual Sports Fest', 'Sports', 'Annual inter-department championship covering cricket, football, 
volleyball, table tennis, and badminton.', '2026-12-05', 'CHARUSAT Sports Complex', 200, 'uploads/event_sample_sports.
jpg', 'upcoming'),
(4, 'Cognizance Tech Symposium', 'Technical', 'State-level technical paper presentation, coding challenges, project 
exhibition, and robotics arena.', '2026-12-18', 'Auditorium 1', 150, 'uploads/event_sample_symposium.jpg', 
'upcoming'),
(5, 'AI & Machine Learning Bootcamp', 'Workshop', 'Practical immersion in Python machine learning workflows, Scikit-
learn algorithms, and neural architectures.', '2026-10-05', 'FTE Lab 7', 50, NULL, 'completed'),
(6, 'Vrund Cultural Night', 'Cultural', 'Annual campus cultural festival celebrating Gujarati folk dance, musical 
concerts, drama, and fine arts.', '2026-12-22', 'Main University Grounds', 500, NULL, 'upcoming')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `poster_url` = VALUES(`poster_url`);
