-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: ocean_hrms
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `module` varchar(80) NOT NULL,
  `action` varchar(80) NOT NULL,
  `record_id` int(10) unsigned DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'auth','logout',1,NULL,NULL,'::1','2026-10-07 02:35:21'),(2,NULL,'auth','login_failed',NULL,NULL,'{\"login\":\"admin\",\"portal\":\"admin\"}','::1','2026-10-07 02:35:26'),(3,1,'auth','login_success',1,NULL,NULL,'::1','2026-10-07 02:35:48'),(4,1,'auth','password_changed',1,NULL,NULL,'::1','2026-10-07 02:36:05'),(5,1,'auth','logout',1,NULL,NULL,'::1','2026-10-07 02:36:10'),(6,NULL,'auth','login_failed',NULL,NULL,'{\"login\":\"admin\",\"portal\":\"admin\"}','::1','2026-10-07 02:36:14'),(7,1,'auth','login_success',1,NULL,NULL,'::1','2026-10-07 02:36:20'),(8,NULL,'auth','login_failed',NULL,NULL,'{\"login\":\"admin\",\"portal\":\"admin\"}','::1','2026-10-07 11:21:09'),(9,1,'auth','login_success',1,NULL,NULL,'::1','2026-10-07 11:21:15'),(10,NULL,'salary','updated',1,NULL,'{\"monthly_salary\":25000}',NULL,'2026-10-07 11:22:31'),(11,NULL,'employee','login_created',1,NULL,NULL,NULL,'2026-10-07 11:22:31'),(12,3,'auth','login_success',3,NULL,NULL,'::1','2026-10-07 11:22:38'),(13,3,'auth','login_success',3,NULL,NULL,'::1','2026-10-07 11:23:24'),(14,3,'auth','logout',3,NULL,NULL,'::1','2026-10-07 18:59:53'),(15,3,'auth','login_success',3,NULL,NULL,'::1','2026-10-08 17:50:44'),(16,3,'auth','login_success',3,NULL,NULL,'::1','2026-10-16 17:32:16'),(17,1,'auth','login_success',1,NULL,NULL,'::1','2026-10-16 17:44:18'),(18,1,'auth','login_success',1,NULL,NULL,'::1','2026-10-16 18:18:56'),(19,1,'letters','generated',1,NULL,'{\"type\":\"offer\",\"ref\":\"OI\\/HR\\/OFFER\\/26-27\\/0001\"}','::1','2026-10-16 18:18:56'),(20,1,'auth','logout',1,NULL,NULL,'::1','2026-10-16 19:16:57'),(21,1,'auth','login_success',1,NULL,NULL,'::1','2026-10-16 19:17:09');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `announcements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `target_department_id` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `target_department_id` (`target_department_id`),
  CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`target_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Welcome to Ocean HRMS','Ocean Infotech HRMS is now live. Update your profile and explore the employee portal.',NULL,'2026-10-06','2026-12-05',NULL,1,1,'2026-10-06 17:52:25');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `shift_id` int(10) unsigned DEFAULT NULL,
  `punch_in` datetime DEFAULT NULL,
  `punch_out` datetime DEFAULT NULL,
  `working_minutes` int(11) DEFAULT 0,
  `break_minutes` int(11) NOT NULL DEFAULT 0,
  `late_minutes` int(11) DEFAULT 0,
  `early_leave_minutes` int(11) NOT NULL DEFAULT 0,
  `overtime_minutes` int(11) DEFAULT 0,
  `status` enum('present','absent','half_day','late','wfh','leave','holiday','week_off') DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_emp_date` (`employee_id`,`attendance_date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (1,1,'2026-10-07',1,'2026-10-07 11:23:26','2026-10-07 16:24:06',300,0,113,125,0,'half_day',NULL,'2026-10-07 11:23:26','2026-10-07 16:24:06'),(2,1,'2026-10-08',1,'2026-10-08 18:27:03','2026-10-08 18:35:01',7,0,537,0,0,'absent',NULL,'2026-10-08 18:27:03','2026-10-08 18:35:01');
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_breaks`
--

DROP TABLE IF EXISTS `attendance_breaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_breaks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `break_out` datetime NOT NULL,
  `break_in` datetime DEFAULT NULL,
  `minutes` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_id` (`attendance_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `attendance_breaks_att` FOREIGN KEY (`attendance_id`) REFERENCES `attendance` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_breaks_emp` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_breaks`
--

LOCK TABLES `attendance_breaks` WRITE;
/*!40000 ALTER TABLE `attendance_breaks` DISABLE KEYS */;
INSERT INTO `attendance_breaks` VALUES (1,1,1,'2026-10-07 11:24:05','2026-10-07 11:24:49',0,'2026-10-07 11:24:05'),(2,1,1,'2026-10-07 16:23:16','2026-10-07 16:24:04',0,'2026-10-07 16:23:16'),(3,2,1,'2026-10-08 18:34:56','2026-10-08 18:35:01',0,'2026-10-08 18:34:56');
/*!40000 ALTER TABLE `attendance_breaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_logs`
--

DROP TABLE IF EXISTS `attendance_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `punch_type` enum('in','out','break_out','break_in') NOT NULL,
  `punched_at` datetime NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_logs`
--

LOCK TABLES `attendance_logs` WRITE;
/*!40000 ALTER TABLE `attendance_logs` DISABLE KEYS */;
INSERT INTO `attendance_logs` VALUES (1,1,'in','2026-10-07 11:23:26','::1','2026-10-07 11:23:26'),(2,1,'break_out','2026-10-07 11:24:05','::1','2026-10-07 11:24:05'),(3,1,'break_in','2026-10-07 11:24:49','::1','2026-10-07 11:24:49'),(4,1,'break_out','2026-10-07 16:23:16','::1','2026-10-07 16:23:16'),(5,1,'break_in','2026-10-07 16:24:04','::1','2026-10-07 16:24:04'),(6,1,'out','2026-10-07 16:24:06','::1','2026-10-07 16:24:06'),(7,1,'in','2026-10-08 18:27:03','::1','2026-10-08 18:27:03'),(8,1,'break_out','2026-10-08 18:34:56','::1','2026-10-08 18:34:56'),(9,1,'break_in','2026-10-08 18:35:01','::1','2026-10-08 18:35:01'),(10,1,'out','2026-10-08 18:35:01','::1','2026-10-08 18:35:01');
/*!40000 ALTER TABLE `attendance_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `branches` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `city` varchar(80) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `branches`
--

LOCK TABLES `branches` WRITE;
/*!40000 ALTER TABLE `branches` DISABLE KEYS */;
INSERT INTO `branches` VALUES (1,'Head Office','Ahmedabad',1,'2026-10-06 17:52:25');
/*!40000 ALTER TABLE `branches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_education`
--

DROP TABLE IF EXISTS `candidate_education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_education` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` int(10) unsigned NOT NULL,
  `degree` varchar(120) DEFAULT NULL,
  `institute` varchar(150) DEFAULT NULL,
  `specialization` varchar(120) DEFAULT NULL,
  `passing_year` varchar(10) DEFAULT NULL,
  `percentage_grade` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_id` (`candidate_id`),
  CONSTRAINT `candidate_education_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_education`
--

LOCK TABLES `candidate_education` WRITE;
/*!40000 ALTER TABLE `candidate_education` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_education` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_experience`
--

DROP TABLE IF EXISTS `candidate_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_experience` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` int(10) unsigned NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `designation` varchar(120) DEFAULT NULL,
  `from_month` char(7) DEFAULT NULL,
  `to_month` char(7) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `tech_stack` varchar(255) DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_id` (`candidate_id`),
  CONSTRAINT `candidate_experience_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_experience`
--

LOCK TABLES `candidate_experience` WRITE;
/*!40000 ALTER TABLE `candidate_experience` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_experience` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidate_interviews`
--

DROP TABLE IF EXISTS `candidate_interviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidate_interviews` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `candidate_id` int(10) unsigned NOT NULL,
  `round` enum('screening','technical','machine_test','managerial','hr') NOT NULL DEFAULT 'technical',
  `interview_at` datetime DEFAULT NULL,
  `mode` enum('in_person','video','phone') DEFAULT 'in_person',
  `interviewer` varchar(120) DEFAULT NULL,
  `technical_score` tinyint(4) DEFAULT NULL,
  `communication_score` tinyint(4) DEFAULT NULL,
  `problem_solving_score` tinyint(4) DEFAULT NULL,
  `attitude_score` tinyint(4) DEFAULT NULL,
  `result` enum('pending','pass','fail','hold') NOT NULL DEFAULT 'pending',
  `feedback` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `candidate_id` (`candidate_id`),
  CONSTRAINT `candidate_interviews_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidate_interviews`
--

LOCK TABLES `candidate_interviews` WRITE;
/*!40000 ALTER TABLE `candidate_interviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidate_interviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `candidates`
--

DROP TABLE IF EXISTS `candidates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `application_no` varchar(30) NOT NULL,
  `public_token` char(32) NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `designation_id` int(10) unsigned DEFAULT NULL,
  `position_other` varchar(150) DEFAULT NULL,
  `employment_type` enum('full_time','intern','contract','part_time') DEFAULT 'full_time',
  `work_mode` enum('office','hybrid','remote') DEFAULT 'office',
  `source` varchar(60) DEFAULT NULL,
  `referred_by` varchar(120) DEFAULT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `alt_mobile` varchar(20) DEFAULT NULL,
  `parent_mobile` varchar(20) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `marital_status` varchar(40) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `state` varchar(80) DEFAULT NULL,
  `pincode` varchar(20) DEFAULT NULL,
  `aadhaar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `is_fresher` tinyint(1) NOT NULL DEFAULT 0,
  `total_experience_months` int(11) NOT NULL DEFAULT 0,
  `current_ctc` decimal(12,2) DEFAULT NULL,
  `expected_ctc` decimal(12,2) DEFAULT NULL,
  `offered_salary` decimal(12,2) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `probation_months` tinyint(3) unsigned DEFAULT NULL,
  `offer_valid_till` date DEFAULT NULL,
  `selected_at` datetime DEFAULT NULL,
  `notice_period` varchar(40) DEFAULT NULL,
  `current_location` varchar(120) DEFAULT NULL,
  `ready_to_relocate` tinyint(1) DEFAULT 0,
  `primary_skills` text DEFAULT NULL,
  `secondary_skills` text DEFAULT NULL,
  `certifications` text DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `best_project` text DEFAULT NULL,
  `why_join` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `resume_path` varchar(255) DEFAULT NULL,
  `declaration` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('new','shortlisted','interview','technical_round','hr_round','selected','offered','joined','on_hold','rejected') NOT NULL DEFAULT 'new',
  `hr_remarks` text DEFAULT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_no` (`application_no`),
  UNIQUE KEY `public_token` (`public_token`),
  KEY `idx_cand_status` (`status`),
  KEY `idx_cand_mobile` (`mobile`),
  KEY `department_id` (`department_id`),
  KEY `designation_id` (`designation_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `candidates_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidates_ibfk_2` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `candidates_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `candidates`
--

LOCK TABLES `candidates` WRITE;
/*!40000 ALTER TABLE `candidates` DISABLE KEYS */;
/*!40000 ALTER TABLE `candidates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `company_policies`
--

DROP TABLE IF EXISTS `company_policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_policies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `category` varchar(80) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `company_policies`
--

LOCK TABLES `company_policies` WRITE;
/*!40000 ALTER TABLE `company_policies` DISABLE KEYS */;
/*!40000 ALTER TABLE `company_policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `departments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Software Development','DEV',0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(2,'Human Resources','HR',0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(3,'Quality Assurance','QA',0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(4,'Business Development','BD',0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(5,'UI / UX Design','UX',0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(6,'DevOps & Infrastructure','DEVOPS',0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(7,'Project Management','PM',0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(8,'Accounts & Finance','FIN',0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(9,'IT Support','IT',0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(10,'Internship','INTERN',1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(11,'Designing','DESIGN',1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(12,'Web Designing','WEBDES',1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(13,'Shopify Development','SHOPIFY',1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(14,'Wordpress Development','WP',1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(15,'Web Development','WEBDEV',1,'2026-10-16 18:37:00','2026-10-16 18:44:33');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `designations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `designations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
INSERT INTO `designations` VALUES (1,'Software Engineer',1,0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(2,'Team Lead',1,0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(3,'HR Executive',2,0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(4,'HR Head',2,0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(5,'QA Engineer',3,0,'2026-10-06 17:52:24','2026-10-16 18:37:00'),(6,'Senior Software Engineer',1,0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(7,'Project Manager',7,0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(8,'UI/UX Designer',5,0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(9,'DevOps Engineer',6,0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(10,'Business Analyst',4,0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(11,'IT Support Executive',9,0,'2026-10-06 18:00:47','2026-10-16 18:37:00'),(12,'Web Developer',1,0,'2026-10-06 23:36:22','2026-10-16 18:37:00'),(13,'Web Designer',5,0,'2026-10-06 23:36:22','2026-10-16 18:37:00'),(14,'Designing',5,0,'2026-10-06 23:36:22','2026-10-16 18:37:00'),(15,'Receptionist',2,0,'2026-10-06 23:36:22','2026-10-16 18:37:00'),(16,'Sales Executive',4,0,'2026-10-06 23:36:22','2026-10-16 18:37:00'),(17,'Fresher',1,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(18,'Junior Software Engineer',1,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(19,'Junior Web Developer',15,1,'2026-10-16 18:22:00','2026-10-16 18:44:33'),(20,'Senior Web Developer',15,1,'2026-10-16 18:22:00','2026-10-16 18:44:33'),(21,'Junior QA Engineer',3,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(22,'Senior QA Engineer',3,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(23,'Junior UI/UX Designer',5,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(24,'Senior UI/UX Designer',5,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(25,'Junior Web Designer',12,1,'2026-10-16 18:22:00','2026-10-16 18:44:33'),(26,'Senior Web Designer',12,1,'2026-10-16 18:22:00','2026-10-16 18:44:33'),(27,'Junior DevOps Engineer',6,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(28,'Senior DevOps Engineer',6,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(29,'Junior HR Executive',2,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(30,'Junior Sales Executive',4,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(31,'Senior Sales Executive',4,0,'2026-10-16 18:22:00','2026-10-16 18:37:00'),(32,'Intern Employee',10,1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(33,'Junior Designer',11,1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(34,'Senior Designer',11,1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(35,'Junior Shopify Developer',13,1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(36,'Senior Shopify Developer',13,1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(37,'Junior Wordpress Developer',14,1,'2026-10-16 18:37:00','2026-10-16 18:44:33'),(38,'Senior Wordpress Developer',14,1,'2026-10-16 18:37:00','2026-10-16 18:44:33');
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_bank_details`
--

DROP TABLE IF EXISTS `employee_bank_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_bank_details` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `bank_name` varchar(120) DEFAULT NULL,
  `account_holder_name` varchar(120) DEFAULT NULL,
  `account_number` varchar(60) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `branch` varchar(120) DEFAULT NULL,
  `payment_mode` varchar(40) DEFAULT 'bank_transfer',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_bank_details_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_bank_details`
--

LOCK TABLES `employee_bank_details` WRITE;
/*!40000 ALTER TABLE `employee_bank_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_bank_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_documents`
--

DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_documents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `document_name` varchar(150) NOT NULL,
  `document_type` varchar(80) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `uploaded_by` int(10) unsigned DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `uploaded_by` (`uploaded_by`),
  CONSTRAINT `employee_documents_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_documents`
--

LOCK TABLES `employee_documents` WRITE;
/*!40000 ALTER TABLE `employee_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_education`
--

DROP TABLE IF EXISTS `employee_education`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_education` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `degree` varchar(120) DEFAULT NULL,
  `institute` varchar(150) DEFAULT NULL,
  `university` varchar(150) DEFAULT NULL,
  `passing_year` year(4) DEFAULT NULL,
  `percentage_grade` varchar(40) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_education_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_education`
--

LOCK TABLES `employee_education` WRITE;
/*!40000 ALTER TABLE `employee_education` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_education` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_emergency_contacts`
--

DROP TABLE IF EXISTS `employee_emergency_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_emergency_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `contact_name` varchar(120) DEFAULT NULL,
  `relationship` varchar(80) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `alternate_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_emergency_contacts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_emergency_contacts`
--

LOCK TABLES `employee_emergency_contacts` WRITE;
/*!40000 ALTER TABLE `employee_emergency_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_emergency_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_experience`
--

DROP TABLE IF EXISTS `employee_experience`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_experience` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `designation` varchar(120) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `leaving_date` date DEFAULT NULL,
  `total_experience` varchar(40) DEFAULT NULL,
  `reason_for_leaving` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_experience_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_experience`
--

LOCK TABLES `employee_experience` WRITE;
/*!40000 ALTER TABLE `employee_experience` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_experience` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_queries`
--

DROP TABLE IF EXISTS `employee_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_queries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `category` enum('salary','attendance','leave','payroll','documents','hr','it_support','asset','other') NOT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `assigned_to` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `employee_queries_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_queries_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_queries`
--

LOCK TABLES `employee_queries` WRITE;
/*!40000 ALTER TABLE `employee_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_query_replies`
--

DROP TABLE IF EXISTS `employee_query_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_query_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `query_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `message` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `query_id` (`query_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `employee_query_replies_ibfk_1` FOREIGN KEY (`query_id`) REFERENCES `employee_queries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employee_query_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_query_replies`
--

LOCK TABLES `employee_query_replies` WRITE;
/*!40000 ALTER TABLE `employee_query_replies` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_query_replies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_salaries`
--

DROP TABLE IF EXISTS `employee_salaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_salaries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `monthly_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `effective_from` date DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_salaries_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_salaries`
--

LOCK TABLES `employee_salaries` WRITE;
/*!40000 ALTER TABLE `employee_salaries` DISABLE KEYS */;
INSERT INTO `employee_salaries` VALUES (1,1,25000.00,'2026-10-07','2026-10-07 11:22:31',NULL);
/*!40000 ALTER TABLE `employee_salaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_self_fill_invites`
--

DROP TABLE IF EXISTS `employee_self_fill_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_self_fill_invites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(64) NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT 'Employee details form',
  `expires_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_active_exp` (`is_active`,`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_self_fill_invites`
--

LOCK TABLES `employee_self_fill_invites` WRITE;
/*!40000 ALTER TABLE `employee_self_fill_invites` DISABLE KEYS */;
INSERT INTO `employee_self_fill_invites` VALUES (1,'b4259c1f769fbca5689ada22b6bcfebe','Employee details form','2026-10-23 18:35:08',1,1,'2026-10-16 18:35:08');
/*!40000 ALTER TABLE `employee_self_fill_invites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_self_fill_logs`
--

DROP TABLE IF EXISTS `employee_self_fill_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_self_fill_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invite_id` int(10) unsigned NOT NULL,
  `employee_id` int(10) unsigned NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invite_id` (`invite_id`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_self_fill_logs`
--

LOCK TABLES `employee_self_fill_logs` WRITE;
/*!40000 ALTER TABLE `employee_self_fill_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_self_fill_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_timeline`
--

DROP TABLE IF EXISTS `employee_timeline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_timeline` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_timeline_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_timeline`
--

LOCK TABLES `employee_timeline` WRITE;
/*!40000 ALTER TABLE `employee_timeline` DISABLE KEYS */;
INSERT INTO `employee_timeline` VALUES (1,1,'Employee joined','Amit Patel profile created','2026-10-07',NULL,'2026-10-07 11:22:31'),(2,1,'Salary updated','Joining salary · ₹25,000.00 / month','2026-10-07',NULL,'2026-10-07 11:22:31'),(3,1,'Employee login created','Portal username OI20260001','2026-10-07',NULL,'2026-10-07 11:22:31');
/*!40000 ALTER TABLE `employee_timeline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `employee_code` varchar(40) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `middle_name` varchar(80) DEFAULT NULL,
  `last_name` varchar(80) NOT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` varchar(10) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `personal_email` varchar(150) DEFAULT NULL,
  `official_email` varchar(150) DEFAULT NULL,
  `marital_status` varchar(40) DEFAULT NULL,
  `nationality` varchar(80) DEFAULT 'Indian',
  `current_address` text DEFAULT NULL,
  `current_city` varchar(80) DEFAULT NULL,
  `current_state` varchar(80) DEFAULT NULL,
  `current_country` varchar(80) DEFAULT 'India',
  `current_pincode` varchar(20) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `permanent_city` varchar(80) DEFAULT NULL,
  `permanent_state` varchar(80) DEFAULT NULL,
  `permanent_country` varchar(80) DEFAULT 'India',
  `permanent_pincode` varchar(20) DEFAULT NULL,
  `same_as_current` tinyint(1) DEFAULT 0,
  `joining_date` date DEFAULT NULL,
  `employment_type` enum('full_time','part_time','intern','freelancer','contract','consultant') DEFAULT 'full_time',
  `department_id` int(10) unsigned DEFAULT NULL,
  `designation_id` int(10) unsigned DEFAULT NULL,
  `reporting_manager_id` int(10) unsigned DEFAULT NULL,
  `team_id` int(10) unsigned DEFAULT NULL,
  `branch_id` int(10) unsigned DEFAULT NULL,
  `work_location` varchar(120) DEFAULT NULL,
  `shift_id` int(10) unsigned DEFAULT NULL,
  `probation_period_days` int(11) DEFAULT 90,
  `confirmation_date` date DEFAULT NULL,
  `exit_date` date DEFAULT NULL,
  `notice_period_days` int(11) DEFAULT 30,
  `status` enum('draft','onboarding','probation','active','inactive','resigned') DEFAULT 'onboarding',
  `pan_number` varchar(20) DEFAULT NULL,
  `aadhaar_number` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  KEY `designation_id` (`designation_id`),
  KEY `reporting_manager_id` (`reporting_manager_id`),
  KEY `team_id` (`team_id`),
  KEY `branch_id` (`branch_id`),
  KEY `shift_id` (`shift_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`reporting_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_5` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_6` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_7` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,3,'OI20260001','Amit',NULL,'Patel',NULL,'male','1996-10-14',NULL,'9876500001','amit.patel@example.com','amit.patel@oceaninfotech.co.in',NULL,'Indian',NULL,'Rajkot','Gujarat','India',NULL,NULL,NULL,NULL,'India',NULL,0,'2026-10-07','full_time',1,1,NULL,NULL,NULL,NULL,1,90,NULL,NULL,30,'active',NULL,NULL,'2026-10-07 11:22:31','2026-10-07 11:22:31');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `holiday_date` date NOT NULL,
  `holiday_type` varchar(60) DEFAULT 'public',
  `description` varchar(255) DEFAULT NULL,
  `location` varchar(120) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,'Republic Day','2026-01-26','public','National Holiday','All','2026-10-06 17:52:25'),(2,'Independence Day','2026-08-15','public','National Holiday','All','2026-10-06 17:52:25'),(3,'Diwali','2026-11-08','public','Festival Holiday','All','2026-10-06 17:52:25'),(4,'Ocean Infotech Annual Day','2026-12-25','company','Company celebration','Ahmedabad','2026-10-07 00:31:43');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hr_letters`
--

DROP TABLE IF EXISTS `hr_letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hr_letters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `letter_type` enum('offer','appointment','experience','relieving') NOT NULL,
  `ref_no` varchar(60) NOT NULL,
  `employee_id` int(10) unsigned DEFAULT NULL,
  `candidate_id` int(10) unsigned DEFAULT NULL,
  `letter_date` date NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref_no` (`ref_no`),
  KEY `idx_letter_emp` (`employee_id`,`letter_type`),
  KEY `idx_letter_cand` (`candidate_id`,`letter_type`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hr_letters`
--

LOCK TABLES `hr_letters` WRITE;
/*!40000 ALTER TABLE `hr_letters` DISABLE KEYS */;
INSERT INTO `hr_letters` VALUES (1,'offer','OI/HR/OFFER/26-27/0001',1,NULL,'2026-10-16',NULL,1,'2026-10-16 18:18:56');
/*!40000 ALTER TABLE `hr_letters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_balances`
--

DROP TABLE IF EXISTS `leave_balances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_balances` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `leave_type_id` int(10) unsigned NOT NULL,
  `year` year(4) NOT NULL,
  `allocated` decimal(5,1) DEFAULT 0.0,
  `used` decimal(5,1) DEFAULT 0.0,
  `pending` decimal(5,1) DEFAULT 0.0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_bal` (`employee_id`,`leave_type_id`,`year`),
  KEY `leave_type_id` (`leave_type_id`),
  CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_balances_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_balances`
--

LOCK TABLES `leave_balances` WRITE;
/*!40000 ALTER TABLE `leave_balances` DISABLE KEYS */;
INSERT INTO `leave_balances` VALUES (1,1,3,2026,1.0,0.0,0.0);
/*!40000 ALTER TABLE `leave_balances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `leave_type_id` int(10) unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `days` decimal(5,1) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `manager_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `hr_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `manager_remark` varchar(255) DEFAULT NULL,
  `hr_remark` varchar(255) DEFAULT NULL,
  `approved_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `code` varchar(20) NOT NULL,
  `days_per_year` decimal(5,1) DEFAULT 0.0,
  `monthly_accrual` decimal(4,1) NOT NULL DEFAULT 0.0,
  `is_paid` tinyint(1) DEFAULT 1,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES (1,'Casual Leave','CL',12.0,0.0,1,0,'2026-10-06 17:52:25'),(2,'Sick Leave','SL',12.0,0.0,1,0,'2026-10-06 17:52:25'),(3,'Paid Leave','PL',12.0,1.0,1,1,'2026-10-06 17:52:25'),(4,'Privilege Leave','PRL',10.0,0.0,1,0,'2026-10-06 17:52:25'),(5,'Comp-Off','CO',0.0,0.0,1,0,'2026-10-06 17:52:25'),(6,'Work From Home','WFH',0.0,0.0,1,0,'2026-10-06 17:52:25'),(7,'Unpaid Leave','UL',0.0,0.0,0,1,'2026-10-06 17:52:25');
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,3,'Welcome to Ocean HRMS','Your employee login is ready. Use the employee portal to punch attendance and apply for leave.','employee/dashboard',0,'2026-10-07 11:22:31'),(2,1,'Punch In · Amit Patel','Amit Patel punched in at 11:23 AM (late by 1h 53m)','attendance',0,'2026-10-07 11:23:26'),(3,2,'Punch In · Amit Patel','Amit Patel punched in at 11:23 AM (late by 1h 53m)','attendance',0,'2026-10-07 11:23:26'),(4,1,'Punch Out · Amit Patel','Amit Patel punched out at 04:24 PM · worked 5h 00m','attendance',0,'2026-10-07 16:24:06'),(5,2,'Punch Out · Amit Patel','Amit Patel punched out at 04:24 PM · worked 5h 00m','attendance',0,'2026-10-07 16:24:06'),(6,1,'Punch In · Amit Patel','Amit Patel punched in at 06:27 PM (late by 8h 57m)','attendance',0,'2026-10-08 18:27:03'),(7,2,'Punch In · Amit Patel','Amit Patel punched in at 06:27 PM (late by 8h 57m)','attendance',0,'2026-10-08 18:27:03'),(8,1,'Punch Out · Amit Patel','Amit Patel punched out at 06:35 PM · worked 0h 07m','attendance',0,'2026-10-08 18:35:01'),(9,2,'Punch Out · Amit Patel','Amit Patel punched out at 06:35 PM · worked 0h 07m','attendance',0,'2026-10-08 18:35:01');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `module` varchar(80) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'View Dashboard','dashboard.view','dashboard','2026-10-06 17:52:24'),(2,'View Employees','employee.view','employee','2026-10-06 17:52:24'),(3,'Create Employee','employee.create','employee','2026-10-06 17:52:24'),(4,'Edit Employee','employee.edit','employee','2026-10-06 17:52:24'),(5,'Delete Employee','employee.delete','employee','2026-10-06 17:52:24'),(6,'View Attendance','attendance.view','attendance','2026-10-06 17:52:24'),(7,'Edit Attendance','attendance.edit','attendance','2026-10-06 17:52:24'),(8,'View Leave','leave.view','leave','2026-10-06 17:52:24'),(9,'Create Leave','leave.create','leave','2026-10-06 17:52:24'),(10,'Approve Leave','leave.approve','leave','2026-10-06 17:52:24'),(11,'Reject Leave','leave.reject','leave','2026-10-06 17:52:24'),(12,'View Salary','salary.view','salary','2026-10-06 17:52:24'),(13,'Create Salary','salary.create','salary','2026-10-06 17:52:24'),(14,'Edit Salary','salary.edit','salary','2026-10-06 17:52:24'),(15,'View Queries','query.view','query','2026-10-06 17:52:24'),(16,'Create Query','query.create','query','2026-10-06 17:52:24'),(17,'Reply Query','query.reply','query','2026-10-06 17:52:24'),(18,'Close Query','query.close','query','2026-10-06 17:52:24'),(19,'Manage Settings','settings.manage','settings','2026-10-06 17:52:24'),(20,'View Reports','reports.view','reports','2026-10-06 17:52:24'),(21,'Manage Recruitment','recruitment.manage','recruitment','2026-10-06 21:52:40');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES (1,5),(2,1),(2,2),(2,3),(2,4),(2,5),(2,6),(2,7),(2,8),(2,9),(2,10),(2,11),(2,12),(2,13),(2,14),(2,15),(2,16),(2,17),(2,18),(2,19),(2,20),(2,21),(3,1),(3,2),(3,3),(3,4),(3,6),(3,8),(3,9),(3,15),(3,16),(3,17),(3,20),(3,21),(4,1),(4,2),(4,6),(4,8),(4,10),(4,11),(4,15),(4,20),(5,6),(5,8),(5,9),(5,12),(5,15),(5,16);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','super_admin','Full system access','2026-10-06 17:52:24'),(2,'HR Head','hr_head','Complete HR process','2026-10-06 17:52:24'),(3,'HR Executive','hr_executive','Limited HR permissions','2026-10-06 17:52:24'),(4,'Manager','manager','Team leader access','2026-10-06 17:52:24'),(5,'Employee','employee','Employee portal only','2026-10-06 17:52:24');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_components`
--

DROP TABLE IF EXISTS `salary_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_components` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `code` varchar(40) NOT NULL,
  `type` enum('earning','deduction') NOT NULL,
  `is_percentage` tinyint(1) DEFAULT 0,
  `status` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_components`
--

LOCK TABLES `salary_components` WRITE;
/*!40000 ALTER TABLE `salary_components` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_histories`
--

DROP TABLE IF EXISTS `salary_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_histories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `monthly_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `effective_from` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `snapshot_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`snapshot_json`)),
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `salary_histories_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_histories`
--

LOCK TABLES `salary_histories` WRITE;
/*!40000 ALTER TABLE `salary_histories` DISABLE KEYS */;
INSERT INTO `salary_histories` VALUES (1,1,25000.00,'2026-10-07','Joining salary','{\"monthly_salary\":25000}',NULL,'2026-10-07 11:22:31');
/*!40000 ALTER TABLE `salary_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'company_name','','2026-10-06 23:25:31'),(2,'company_tagline','','2026-10-06 23:25:31'),(3,'company_email','hr@oceaninfotech.com','2026-10-06 23:25:31'),(4,'company_phone','+91 00000 00000','2026-10-06 23:25:31'),(5,'company_address','Ahmedabad, Gujarat, India','2026-10-06 23:25:31'),(6,'brand_primary','#F5A01B','2026-10-06 17:52:25'),(7,'brand_ink','#211F23','2026-10-06 17:52:25'),(8,'company_type','IT / Software Development','2026-10-16 18:22:00'),(14,'letterhead_mode','full','2026-10-06 23:57:02'),(15,'letterhead_header','','2026-10-06 23:43:07'),(16,'letterhead_footer','','2026-10-06 23:43:07'),(17,'letterhead_full','assets/uploads/letterhead/lh_full_69e3cb481060902b.jpg','2026-10-06 23:44:39'),(18,'letterhead_top_mm','50','2026-10-06 23:57:02'),(19,'letterhead_bottom_mm','30','2026-10-06 23:57:02'),(20,'letterhead_side_mm','10','2026-10-06 23:57:02'),(21,'careers_intro','Ocean Infotech is a software development company building web, mobile and cloud solutions for clients across India and abroad. We are always looking for passionate developers, designers, testers and IT professionals to grow with us.','2026-10-06 23:25:31'),(28,'careers_positions','Intern Employee\nJunior Designer\nSenior Designer\nJunior Web Designer\nSenior Web Designer\nJunior Shopify Developer\nSenior Shopify Developer\nJunior Wordpress Developer\nSenior Wordpress Developer\nJunior Web Developer\nSenior Web Developer','2026-10-16 18:44:33'),(50,'company_website','www.oceaninfotech.co.in','2026-10-06 23:25:31'),(60,'letter_ref_prefix','OI/HR','2026-10-06 23:36:22'),(61,'letter_signatory_name','HR Manager','2026-10-06 23:36:22'),(62,'letter_signatory_designation','Human Resources','2026-10-06 23:36:22'),(63,'letter_week_off','Sunday','2026-10-06 23:36:22'),(64,'letter_jurisdiction','Ahmedabad, Gujarat','2026-10-06 23:36:22'),(65,'letter_notice_period','1 month','2026-10-06 23:36:22'),(93,'leave_policy_applied','1 paid leave per month','2026-10-07 00:54:04'),(95,'db_sync_last_run','2026-10-16 18:44:33','2026-10-16 18:44:33'),(98,'org_founder_name','Sandip Gajera','2026-10-16 18:44:33'),(99,'org_co_founder_name','Charmi Gajera','2026-10-16 18:44:33'),(100,'org_founder_id','','2026-10-16 18:41:54'),(101,'org_co_founder_id','','2026-10-16 18:41:54'),(105,'org_founder_photo','','2026-10-16 18:44:33'),(106,'org_co_founder_photo','','2026-10-16 18:44:33'),(107,'db_sync_version','2026-10-16-org-chart-self-fill-v2','2026-10-16 18:44:33');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shifts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_after_minutes` int(11) NOT NULL DEFAULT 15,
  `break_minutes` int(11) NOT NULL DEFAULT 60,
  `half_day_minutes` int(11) NOT NULL DEFAULT 240,
  `full_day_minutes` int(11) NOT NULL DEFAULT 480,
  `week_offs` varchar(20) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
INSERT INTO `shifts` VALUES (1,'General Shift','GEN','09:30:00','18:30:00',15,60,240,480,'0',1,'2026-10-06 17:52:25',NULL);
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `department_id` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (1,'Alpha Team',1,1,'2026-10-06 17:52:25'),(2,'HR Ops',2,1,'2026-10-06 17:52:25');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1),(2,2),(3,5);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `username` varchar(80) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `login_secret` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin','admin','admin@oceaninfotech.com','$2y$10$aOjM8FvnGISefWQiTHkIDOEiZBWrCUE5DTVCsE1HPGNwiomptQR56',NULL,1,0,'2026-10-16 19:17:09','::1','2026-10-06 17:52:24','2026-10-07 02:36:05'),(2,'HR Head','hrhead','hrhead@oceaninfotech.com','$2y$10$qU7fk/GGFPQJJNNz4Pan5e.ZOBn4ZegVb.A2S5LIR9OnT1T7e7vv2',NULL,1,1,NULL,NULL,'2026-10-06 17:52:24','2026-10-07 01:27:26'),(3,'Amit Patel','OI20260001','amit.patel@oceaninfotech.co.in','$2y$10$cWjOzVvl9br9sRJ0QMBU6.6AxgeFr4DD7ozC3nuZnCS.QpZ8LHOfq','2e54o8dcriuGTsF3QuZMCJ7/wrWAhe63Into3M2Y9CA=',1,0,'2026-10-16 17:32:16','::1','2026-10-07 11:22:31',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'ocean_hrms'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-17 12:48:32
