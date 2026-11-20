-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 04:12 AM
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
-- Database: `volunteer_state`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` date DEFAULT NULL,
  `priority` enum('low','normal','high') DEFAULT 'normal',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `posted_by`, `posted_at`, `expires_at`, `priority`, `active`) VALUES
(4, 'Summer 2026 Registration is Open!', 'Registration for Summer 2026 courses is now open. Log in to your student portal to register. Priority registration ends March 15th.', 1, '2026-04-02 15:13:01', '2026-05-20', 'high', 1),
(5, 'Library Extended Hours for Finals', 'The campus library will be open until midnight during finals week (May 4-9).', 1, '2026-04-02 15:13:01', '2026-05-10', 'normal', 1),
(6, 'Campus Closed - Spring Break', 'Campus offices will be closed March 17-21 for Spring Break. Online classes continue as scheduled.', 1, '2026-04-02 15:13:01', '2026-03-22', 'normal', 1);

-- --------------------------------------------------------

--
-- Table structure for table `class_sections`
--

CREATE TABLE `class_sections` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester_id` int(11) NOT NULL,
  `section_number` varchar(5) DEFAULT '001',
  `instructor_name` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT 30,
  `enrolled` int(11) DEFAULT 0,
  `waitlist_count` int(11) DEFAULT 0,
  `schedule_days` varchar(20) DEFAULT NULL,
  `schedule_time` varchar(30) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `delivery` enum('In-Person','Online','Hybrid') DEFAULT 'In-Person',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_sections`
--

INSERT INTO `class_sections` (`id`, `course_id`, `semester_id`, `section_number`, `instructor_name`, `capacity`, `enrolled`, `waitlist_count`, `schedule_days`, `schedule_time`, `location`, `delivery`, `active`) VALUES
(1, 1, 1, 'OL01', 'Dr. Robert Smith', 10, 10, 1, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(2, 2, 1, 'OL01', 'Dr. Alice Jones', 10, 7, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(3, 3, 1, 'OL01', 'Prof. Marcus Tech', 10, 10, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(4, 4, 1, 'OL01', 'Dr. Sarah Lara', 10, 10, 1, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(5, 5, 1, 'OL01', 'Prof. Kevin Davis', 10, 10, 1, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(6, 6, 1, 'OL01', 'TBD', 10, 0, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(7, 7, 1, 'OL01', 'TBD', 10, 0, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(8, 8, 1, 'OL01', 'TBD', 10, 1, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(9, 9, 1, 'OL01', 'TBD', 10, 0, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(10, 10, 1, 'OL01', 'TBD', 10, 0, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1),
(11, 11, 1, 'OL01', 'TBD', 10, 0, 0, 'Asynchronous', 'Online 24/7', 'UAGC Canvas Portal', 'Online', 1);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(15) NOT NULL,
  `title` varchar(150) NOT NULL,
  `credits` int(11) DEFAULT 3,
  `degree_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `title`, `credits`, `degree_id`, `description`, `active`) VALUES
(1, 'GEN 101', 'Developing Skills & Strategies for Success', 3, 3, 'Foundations for online learning success.', 1),
(2, 'ENG 121', 'English Composition I', 3, 3, 'Academic writing and research foundations.', 1),
(3, 'MAT 232', 'Statistical Literacy', 3, 3, 'Data interpretation and statistical reasoning.', 1),
(4, 'PHI 208', 'Ethics & Moral Reasoning', 3, 3, 'Ethical frameworks applied to contemporary issues.', 1),
(5, 'INF 231', 'Programming Concepts', 3, 1, 'Introduction to coding logic and OOP.', 1),
(6, 'CPT 310', 'Database Systems & Management', 3, 1, 'Relational database design and SQL.', 1),
(7, 'INT 301', 'Computer Networking', 3, 1, 'Network architectures and OSI model.', 1),
(8, 'CST 301', 'Software Technology & Design', 3, 2, 'Software development life cycle and design.', 1),
(9, 'INT 306', 'Intro to JavaScript Programming', 3, 2, 'Client-side scripting for web apps.', 1),
(10, 'INT 305', 'Mobile App Design & Development', 3, 2, 'Developing for Android and iOS platforms.', 1),
(11, 'CST 499', 'Capstone for Computer Software Tech', 3, 2, 'Final project synthesizing software engineering skills.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `degrees`
--

CREATE TABLE `degrees` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `title` varchar(150) NOT NULL,
  `level` enum('Associate','Bachelor','Master','Doctoral') NOT NULL,
  `area` enum('Business','Criminal Justice','Education','Health Care','Information Technology','Liberal Arts','Social & Behavioral Science') NOT NULL,
  `credits_required` int(11) DEFAULT 120,
  `description` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `degrees`
--

INSERT INTO `degrees` (`id`, `code`, `title`, `level`, `area`, `credits_required`, `description`, `active`) VALUES
(1, 'BS-IT', 'BS in Information Technology', 'Bachelor', 'Information Technology', 120, 'Program focusing on network infrastructure and IT strategy.', 1),
(2, 'BS-CST', 'BS in Computer Software Technology', 'Bachelor', 'Information Technology', 120, 'Focuses on the software development life cycle and design.', 1),
(3, 'GEN-ED', 'General Education Core', 'Bachelor', 'Liberal Arts', 30, 'Foundational competencies required for all UAGC degrees.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Enrolled','Dropped','Completed','Waitlisted') DEFAULT 'Enrolled',
  `grade` varchar(5) DEFAULT NULL,
  `grade_points` decimal(3,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `section_id`, `enrolled_at`, `status`, `grade`, `grade_points`) VALUES
(16, 1, 1, '2026-04-02 15:18:28', 'Enrolled', NULL, NULL),
(17, 2, 2, '2026-04-02 15:18:28', 'Enrolled', NULL, NULL),
(18, 3, 1, '2026-04-02 15:18:28', 'Enrolled', NULL, NULL),
(19, 4, 3, '2026-04-02 15:18:28', 'Enrolled', NULL, NULL),
(20, 5, 5, '2026-04-02 15:18:28', 'Enrolled', NULL, NULL),
(21, 17, 8, '2026-04-21 21:35:45', 'Enrolled', NULL, NULL),
(22, 17, 11, '2026-04-21 21:35:53', 'Dropped', NULL, NULL),
(23, 17, 2, '2026-04-23 00:34:06', 'Dropped', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` varchar(30) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` enum('Academic','Student Life','Sports','Career','Administrative') DEFAULT 'Academic',
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_date`, `event_time`, `location`, `description`, `category`, `active`) VALUES
(11, 'Spring 2026 Finals Week', '2026-05-04', 'All Day', 'Campus-Wide', 'Final examinations for Spring 2026 semester.', 'Academic', 1),
(12, 'Summer 2026 Registration Opens', '2026-03-01', '8:00 AM', 'Online Portal', 'Registration for Summer 2026 semester is now open to all students.', 'Administrative', 1),
(13, 'Career Fair - Tech & Business', '2026-04-08', '10:00 AM - 3:00 PM', 'Student Union Ballroom', 'Meet 40+ employers hiring in IT, Business, and Healthcare fields.', 'Career', 1),
(14, 'Honor Roll Ceremony', '2026-04-15', '6:00 PM', 'Roane Auditorium', 'Celebrating students with GPAs of 3.5 and above for Spring 2026.', 'Academic', 1),
(15, 'Spring Fling & Campus Cookout', '2026-04-22', '11:00 AM - 4:00 PM', 'Campus Lawn', 'Annual spring celebration with food, games, and live music.', 'Student Life', 1),
(16, 'Volunteer State Men\'s Basketball', '2026-03-10', '7:00 PM', 'Caudill Arena', 'Home game vs. Columbia State Community College.', 'Sports', 1),
(17, 'Commencement Ceremony', '2026-05-16', '10:00 AM', 'Municipal Auditorium', 'Graduation ceremony for Spring 2026 graduates.', 'Academic', 1),
(18, 'FAFSA Workshop', '2026-04-05', '1:00 PM - 3:00 PM', 'Financial Aid Office', 'Free workshop for students completing the 2026-2027 FAFSA application.', 'Administrative', 1),
(19, 'Mental Health Awareness Week', '2026-04-14', 'All Week', 'Campus Counseling Center', 'Events, workshops, and resources focused on student mental wellness.', 'Student Life', 1),
(20, 'IT Career Bootcamp', '2026-03-20', '9:00 AM - 5:00 PM', 'Tech Center 200', 'One-day intensive resume and interview prep for IT degree students.', 'Career', 1);

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `registration_open` date NOT NULL,
  `registration_close` date NOT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `is_upcoming` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `name`, `start_date`, `end_date`, `registration_open`, `registration_close`, `is_current`, `is_upcoming`) VALUES
(1, '2026 Academic Year (Online)', '2026-01-01', '2026-12-31', '2025-11-01', '0000-00-00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(30) DEFAULT 'Tennessee',
  `zip` varchar(10) DEFAULT NULL,
  `degree_id` int(11) DEFAULT NULL,
  `gpa` decimal(3,2) DEFAULT 0.00,
  `credits_earned` int(11) DEFAULT 0,
  `enrollment_status` enum('Active','Inactive','Graduated','On Leave','Pending') DEFAULT 'Active',
  `enrolled_date` date DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `first_name`, `last_name`, `date_of_birth`, `email`, `phone`, `address`, `city`, `state`, `zip`, `degree_id`, `gpa`, `credits_earned`, `enrollment_status`, `enrolled_date`, `password_hash`, `photo_url`, `created_at`) VALUES
(1, 'VSS2026001', 'John', 'Doe', '1995-05-15', 'j.doe@email.com', '555-0101', '123 Main St', 'Phoenix', 'AZ', '85001', 1, 3.50, 0, 'Active', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-04-02 15:18:28'),
(2, 'VSS2026002', 'Jane', 'Miller', '1998-11-22', 'j.miller@email.com', '555-0102', '456 Oak Ave', 'San Diego', 'CA', '92101', 2, 3.80, 0, 'Active', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-04-02 15:18:28'),
(3, 'VSS2026003', 'Carlos', 'Ruiz', '2000-01-10', 'c.ruiz@email.com', '555-0103', '789 Pine Rd', 'Denver', 'CO', '80201', 1, 3.20, 0, 'Active', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-04-02 15:18:28'),
(4, 'VSS2026004', 'Emily', 'Chen', '1996-07-30', 'e.chen@email.com', '555-0104', '321 Elm St', 'Seattle', 'WA', '98101', 3, 4.00, 0, 'Active', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-04-02 15:18:28'),
(5, 'VSS2026005', 'Marcus', 'Jordan', '1994-03-05', 'm.jordan@email.com', '555-0105', '654 Maple Dr', 'Austin', 'TX', '73301', 2, 2.90, 0, 'Active', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-04-02 15:18:28'),
(15, 'VSS2026006', 'Jane', 'Doe', '2008-06-06', 'jane.doe@personalemail.com', '6155552000', '123 Broadway', 'Nashville', 'Tennessee', '37122', 2, 0.00, 0, 'Active', '2026-04-16', '$2y$10$bV0NngNMv/0aVw8ELNOVheAKNYyEeFQ1CePlbKbIoDPEm6m9ca84i', NULL, '2026-04-13 20:59:52'),
(17, 'VSS2026007', 'Devon', 'Hight', '1972-12-29', 'dhight@mymail.com', '(615)555-3333', '451 Johnston Ave', 'Nashville', 'Tennessee', '37203', 1, 0.00, 0, 'Active', '2026-04-21', '$2y$10$jttGsa2HztWUmWmBitzR1.tw611fmcNWsVm6s1cemaoRdoz6WU6LK', NULL, '2026-04-21 20:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','faculty','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin@volstate.edu', 'admin', '2026-03-29 22:53:11'),
(3, 'bwilliams', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Williams', 'bwilliams@volstate.edu', 'staff', '2026-03-29 22:53:11'),
(14, 'VSF2026001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Smith', 'r.smith@uagc.edu', 'faculty', '2026-04-02 15:18:28'),
(15, 'VSF2026002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Alice Jones', 'a.jones@uagc.edu', 'faculty', '2026-04-02 15:18:28'),
(16, 'VSF2026003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Marcus Tech', 'm.tech@uagc.edu', 'faculty', '2026-04-02 15:18:28'),
(17, 'VSF2026004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Sarah Lara', 's.lara@uagc.edu', 'faculty', '2026-04-02 15:18:28'),
(18, 'VSF2026005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Kevin Davis', 'k.davis@uagc.edu', 'faculty', '2026-04-02 15:18:28');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE `waitlist` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Waiting','Enrolled','Cancelled') DEFAULT 'Waiting'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waitlist`
--

INSERT INTO `waitlist` (`id`, `student_id`, `section_id`, `position`, `requested_at`, `status`) VALUES
(1, 2, 1, 1, '2026-04-02 16:10:12', 'Waiting'),
(2, 1, 5, 1, '2026-04-07 14:29:39', 'Waiting'),
(3, 17, 4, 1, '2026-04-21 21:36:14', 'Waiting');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `posted_by` (`posted_by`);

--
-- Indexes for table `class_sections`
--
ALTER TABLE `class_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `semester_id` (`semester_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `degree_id` (`degree_id`);

--
-- Indexes for table `degrees`
--
ALTER TABLE `degrees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `degree_id` (`degree_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_waitlist` (`student_id`,`section_id`),
  ADD KEY `section_id` (`section_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `class_sections`
--
ALTER TABLE `class_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `degrees`
--
ALTER TABLE `degrees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `class_sections`
--
ALTER TABLE `class_sections`
  ADD CONSTRAINT `class_sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `class_sections_ibfk_2` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`);

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `class_sections` (`id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`degree_id`) REFERENCES `degrees` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `waitlist_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `class_sections` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
