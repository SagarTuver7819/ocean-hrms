CREATE DATABASE IF NOT EXISTS ocean_hrms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ocean_hrms;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS candidate_interviews;
DROP TABLE IF EXISTS candidate_experience;
DROP TABLE IF EXISTS candidate_education;
DROP TABLE IF EXISTS candidates;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS holidays;
DROP TABLE IF EXISTS company_policies;
DROP TABLE IF EXISTS employee_query_replies;
DROP TABLE IF EXISTS employee_queries;
DROP TABLE IF EXISTS salary_histories;
DROP TABLE IF EXISTS employee_salaries;
DROP TABLE IF EXISTS salary_components;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS leave_balances;
DROP TABLE IF EXISTS leave_types;
DROP TABLE IF EXISTS attendance_logs;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS employee_timeline;
DROP TABLE IF EXISTS employee_documents;
DROP TABLE IF EXISTS employee_experience;
DROP TABLE IF EXISTS employee_education;
DROP TABLE IF EXISTS employee_emergency_contacts;
DROP TABLE IF EXISTS employee_bank_details;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS shifts;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS designations;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  username VARCHAR(80) NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  must_change_password TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at DATETIME NULL,
  last_login_ip VARCHAR(45) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE permissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  module VARCHAR(80) NOT NULL,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
  role_id INT UNSIGNED NOT NULL,
  permission_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_roles (
  user_id INT UNSIGNED NOT NULL,
  role_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  code VARCHAR(40) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE designations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  department_id INT UNSIGNED NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE branches (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  city VARCHAR(80) NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE teams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  department_id INT UNSIGNED NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE shifts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  late_after_minutes INT NOT NULL DEFAULT 15,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE employees (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL UNIQUE,
  employee_code VARCHAR(40) NOT NULL UNIQUE,
  first_name VARCHAR(80) NOT NULL,
  middle_name VARCHAR(80) NULL,
  last_name VARCHAR(80) NOT NULL,
  profile_photo VARCHAR(255) NULL,
  gender ENUM('male','female','other') NULL,
  date_of_birth DATE NULL,
  blood_group VARCHAR(10) NULL,
  mobile VARCHAR(20) NULL,
  personal_email VARCHAR(150) NULL,
  official_email VARCHAR(150) NULL,
  marital_status VARCHAR(40) NULL,
  nationality VARCHAR(80) DEFAULT 'Indian',
  current_address TEXT NULL,
  current_city VARCHAR(80) NULL,
  current_state VARCHAR(80) NULL,
  current_country VARCHAR(80) DEFAULT 'India',
  current_pincode VARCHAR(20) NULL,
  permanent_address TEXT NULL,
  permanent_city VARCHAR(80) NULL,
  permanent_state VARCHAR(80) NULL,
  permanent_country VARCHAR(80) DEFAULT 'India',
  permanent_pincode VARCHAR(20) NULL,
  same_as_current TINYINT(1) DEFAULT 0,
  joining_date DATE NULL,
  employment_type ENUM('full_time','part_time','intern','freelancer','contract','consultant') DEFAULT 'full_time',
  department_id INT UNSIGNED NULL,
  designation_id INT UNSIGNED NULL,
  reporting_manager_id INT UNSIGNED NULL,
  team_id INT UNSIGNED NULL,
  branch_id INT UNSIGNED NULL,
  work_location VARCHAR(120) NULL,
  shift_id INT UNSIGNED NULL,
  probation_period_days INT DEFAULT 90,
  confirmation_date DATE NULL,
  notice_period_days INT DEFAULT 30,
  status ENUM('draft','onboarding','probation','active','inactive','resigned') DEFAULT 'onboarding',
  pan_number VARCHAR(20) NULL,
  aadhaar_number VARCHAR(20) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  FOREIGN KEY (designation_id) REFERENCES designations(id) ON DELETE SET NULL,
  FOREIGN KEY (reporting_manager_id) REFERENCES employees(id) ON DELETE SET NULL,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  FOREIGN KEY (shift_id) REFERENCES shifts(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE employee_bank_details (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL UNIQUE,
  bank_name VARCHAR(120) NULL,
  account_holder_name VARCHAR(120) NULL,
  account_number VARCHAR(60) NULL,
  ifsc_code VARCHAR(20) NULL,
  branch VARCHAR(120) NULL,
  payment_mode VARCHAR(40) DEFAULT 'bank_transfer',
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_emergency_contacts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL UNIQUE,
  contact_name VARCHAR(120) NULL,
  relationship VARCHAR(80) NULL,
  mobile VARCHAR(20) NULL,
  alternate_number VARCHAR(20) NULL,
  address TEXT NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_education (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  degree VARCHAR(120) NULL,
  institute VARCHAR(150) NULL,
  university VARCHAR(150) NULL,
  passing_year YEAR NULL,
  percentage_grade VARCHAR(40) NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_experience (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  company VARCHAR(150) NULL,
  designation VARCHAR(120) NULL,
  joining_date DATE NULL,
  leaving_date DATE NULL,
  total_experience VARCHAR(40) NULL,
  reason_for_leaving VARCHAR(255) NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  document_name VARCHAR(150) NOT NULL,
  document_type VARCHAR(80) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  upload_date DATE NOT NULL,
  expiry_date DATE NULL,
  uploaded_by INT UNSIGNED NULL,
  status ENUM('pending','verified','rejected') DEFAULT 'pending',
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE employee_timeline (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NULL,
  event_date DATE NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attendance (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  attendance_date DATE NOT NULL,
  punch_in DATETIME NULL,
  punch_out DATETIME NULL,
  working_minutes INT DEFAULT 0,
  late_minutes INT DEFAULT 0,
  overtime_minutes INT DEFAULT 0,
  status ENUM('present','absent','half_day','late','wfh','leave','holiday','week_off') DEFAULT 'present',
  remarks VARCHAR(255) NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  UNIQUE KEY uniq_emp_date (employee_id, attendance_date),
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE attendance_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  punch_type ENUM('in','out') NOT NULL,
  punched_at DATETIME NOT NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE leave_types (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  days_per_year DECIMAL(5,1) DEFAULT 0,
  is_paid TINYINT(1) DEFAULT 1,
  status TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE leave_balances (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  leave_type_id INT UNSIGNED NOT NULL,
  year YEAR NOT NULL,
  allocated DECIMAL(5,1) DEFAULT 0,
  used DECIMAL(5,1) DEFAULT 0,
  pending DECIMAL(5,1) DEFAULT 0,
  UNIQUE KEY uniq_bal (employee_id, leave_type_id, year),
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE leave_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  leave_type_id INT UNSIGNED NOT NULL,
  from_date DATE NOT NULL,
  to_date DATE NOT NULL,
  days DECIMAL(5,1) NOT NULL,
  reason TEXT NULL,
  status ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
  manager_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  hr_status ENUM('pending','approved','rejected') DEFAULT 'pending',
  manager_remark VARCHAR(255) NULL,
  hr_remark VARCHAR(255) NULL,
  approved_by INT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (leave_type_id) REFERENCES leave_types(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE salary_components (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  code VARCHAR(40) NOT NULL UNIQUE,
  type ENUM('earning','deduction') NOT NULL,
  is_percentage TINYINT(1) DEFAULT 0,
  status TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE employee_salaries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL UNIQUE,
  monthly_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  effective_from DATE NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE salary_histories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  monthly_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  effective_from DATE NOT NULL,
  reason VARCHAR(255) NULL,
  snapshot_json JSON NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE employee_queries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id INT UNSIGNED NOT NULL,
  category ENUM('salary','attendance','leave','payroll','documents','hr','it_support','asset','other') NOT NULL,
  subject VARCHAR(200) NOT NULL,
  description TEXT NOT NULL,
  attachment VARCHAR(255) NULL,
  status ENUM('open','in_progress','resolved','closed') DEFAULT 'open',
  assigned_to INT UNSIGNED NULL,
  created_at DATETIME NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE employee_query_replies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  query_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  attachment VARCHAR(255) NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (query_id) REFERENCES employee_queries(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  image VARCHAR(255) NULL,
  publish_date DATE NULL,
  expiry_date DATE NULL,
  target_department_id INT UNSIGNED NULL,
  status TINYINT(1) DEFAULT 1,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (target_department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE holidays (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  holiday_date DATE NOT NULL,
  holiday_type VARCHAR(60) DEFAULT 'public',
  description VARCHAR(255) NULL,
  location VARCHAR(120) NULL,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE company_policies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  category VARCHAR(80) NULL,
  status TINYINT(1) DEFAULT 1,
  created_at DATETIME NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  message TEXT NULL,
  link VARCHAR(255) NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  module VARCHAR(80) NOT NULL,
  action VARCHAR(80) NOT NULL,
  record_id INT UNSIGNED NULL,
  old_value JSON NULL,
  new_value JSON NULL,
  ip_address VARCHAR(45) NULL,
  created_at DATETIME NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at DATETIME NULL
) ENGINE=InnoDB;
