USE ocean_hrms;

-- Password for all demo users: Admin@123
SET @pwd = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
-- Above is Laravel's "password" hash. We'll use a proper one below via PHP install.
-- Placeholder replaced by install.php

INSERT INTO roles (id, name, slug, description, created_at) VALUES
(1, 'Super Admin', 'super_admin', 'Full system access', NOW()),
(2, 'HR Head', 'hr_head', 'Complete HR process', NOW()),
(3, 'HR Executive', 'hr_executive', 'Limited HR permissions', NOW()),
(4, 'Manager', 'manager', 'Team leader access', NOW()),
(5, 'Employee', 'employee', 'Employee portal only', NOW());

INSERT INTO permissions (name, slug, module, created_at) VALUES
('View Dashboard', 'dashboard.view', 'dashboard', NOW()),
('View Employees', 'employee.view', 'employee', NOW()),
('Create Employee', 'employee.create', 'employee', NOW()),
('Edit Employee', 'employee.edit', 'employee', NOW()),
('Delete Employee', 'employee.delete', 'employee', NOW()),
('View Attendance', 'attendance.view', 'attendance', NOW()),
('Edit Attendance', 'attendance.edit', 'attendance', NOW()),
('View Leave', 'leave.view', 'leave', NOW()),
('Create Leave', 'leave.create', 'leave', NOW()),
('Approve Leave', 'leave.approve', 'leave', NOW()),
('Reject Leave', 'leave.reject', 'leave', NOW()),
('View Salary', 'salary.view', 'salary', NOW()),
('Create Salary', 'salary.create', 'salary', NOW()),
('Edit Salary', 'salary.edit', 'salary', NOW()),
('View Queries', 'query.view', 'query', NOW()),
('Create Query', 'query.create', 'query', NOW()),
('Reply Query', 'query.reply', 'query', NOW()),
('Close Query', 'query.close', 'query', NOW()),
('Manage Settings', 'settings.manage', 'settings', NOW()),
('View Reports', 'reports.view', 'reports', NOW());

INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions;

INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE slug IN (
  'dashboard.view','employee.view','employee.create','employee.edit',
  'attendance.view','leave.view','leave.create','query.view','query.create','query.reply','reports.view'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions WHERE slug IN (
  'dashboard.view','employee.view','attendance.view','leave.view','leave.approve','leave.reject','query.view','reports.view'
);

INSERT INTO role_permissions (role_id, permission_id)
SELECT 5, id FROM permissions WHERE slug IN (
  'leave.create','leave.view','query.create','query.view','attendance.view','salary.view'
);

INSERT INTO users (id, name, username, email, password, status, must_change_password, created_at) VALUES
(1, 'Super Admin', 'admin', 'admin@oceaninfotech.com', @pwd, 1, 0, NOW()),
(2, 'HR Head', 'hrhead', 'hrhead@oceaninfotech.com', @pwd, 1, 0, NOW()),
(3, 'Rahul Sharma', 'OI20260001', 'rahul@oceaninfotech.com', @pwd, 1, 0, NOW());

INSERT INTO user_roles (user_id, role_id) VALUES (1, 1), (2, 2), (3, 5);

INSERT INTO departments (id, name, code, status, created_at) VALUES
(1, 'Software Development', 'DEV', 1, NOW()),
(2, 'Human Resources', 'HR', 1, NOW()),
(3, 'Quality Assurance', 'QA', 1, NOW()),
(4, 'Business Development', 'BD', 1, NOW()),
(5, 'UI / UX Design', 'UX', 1, NOW()),
(6, 'DevOps & Infrastructure', 'DEVOPS', 1, NOW()),
(7, 'Project Management', 'PM', 1, NOW()),
(8, 'Accounts & Finance', 'FIN', 1, NOW()),
(9, 'IT Support', 'IT', 1, NOW());

INSERT INTO designations (id, name, department_id, status, created_at) VALUES
(1, 'Software Engineer', 1, 1, NOW()),
(2, 'Senior Software Engineer', 1, 1, NOW()),
(3, 'Team Lead', 1, 1, NOW()),
(4, 'HR Executive', 2, 1, NOW()),
(5, 'HR Head', 2, 1, NOW()),
(6, 'QA Engineer', 3, 1, NOW()),
(7, 'UI/UX Designer', 5, 1, NOW()),
(8, 'DevOps Engineer', 6, 1, NOW()),
(9, 'Project Manager', 7, 1, NOW()),
(10, 'Business Analyst', 4, 1, NOW()),
(11, 'IT Support Executive', 9, 1, NOW());

INSERT INTO branches (id, name, city, status, created_at) VALUES
(1, 'Head Office', 'Ahmedabad', 1, NOW());

INSERT INTO teams (id, name, department_id, status, created_at) VALUES
(1, 'Alpha Team', 1, 1, NOW()),
(2, 'HR Ops', 2, 1, NOW());

INSERT INTO shifts (id, name, start_time, end_time, late_after_minutes, status, created_at) VALUES
(1, 'General Shift', '09:30:00', '18:30:00', 15, 1, NOW());

INSERT INTO employees (
  id, user_id, employee_code, first_name, last_name, gender, mobile, personal_email, official_email,
  joining_date, employment_type, department_id, designation_id, team_id, branch_id, shift_id,
  status, created_at
) VALUES (
  1, 3, 'OI20260001', 'Rahul', 'Sharma', 'male', '9876543210', 'rahul.p@gmail.com', 'rahul@oceaninfotech.com',
  '2026-01-15', 'full_time', 1, 1, 1, 1, 1, 'active', NOW()
);

INSERT INTO employee_timeline (employee_id, title, description, event_date, created_by, created_at) VALUES
(1, 'Employee joined', 'Rahul Sharma joined Ocean Infotech', '2026-01-15', 1, NOW());

INSERT INTO leave_types (name, code, days_per_year, is_paid, status, created_at) VALUES
('Casual Leave', 'CL', 12, 1, 1, NOW()),
('Sick Leave', 'SL', 12, 1, 1, NOW()),
('Paid Leave', 'PL', 15, 1, 1, NOW()),
('Privilege Leave', 'PRL', 10, 1, 1, NOW()),
('Comp-Off', 'CO', 0, 1, 1, NOW()),
('Work From Home', 'WFH', 0, 1, 1, NOW()),
('Unpaid Leave', 'UL', 0, 0, 1, NOW());

INSERT INTO leave_balances (employee_id, leave_type_id, year, allocated, used, pending)
SELECT 1, id, 2026, days_per_year, 0, 0 FROM leave_types WHERE code IN ('CL','SL','PL');

INSERT INTO employee_salaries (employee_id, monthly_salary, effective_from, created_at)
VALUES (1, 42500, '2026-01-15', NOW());

INSERT INTO salary_histories (employee_id, monthly_salary, effective_from, reason, created_by, created_at)
VALUES (1, 42500, '2026-01-15', 'Initial salary', 1, NOW());

INSERT INTO holidays (name, holiday_date, holiday_type, description, location, created_at) VALUES
('Republic Day', '2026-01-26', 'public', 'National Holiday', 'All', NOW()),
('Independence Day', '2026-08-15', 'public', 'National Holiday', 'All', NOW()),
('Diwali', '2026-11-08', 'public', 'Festival Holiday', 'All', NOW()),
('Ocean Infotech Annual Day', '2026-12-25', 'company', 'Company celebration', 'Ahmedabad', NOW());

INSERT INTO announcements (title, description, publish_date, expiry_date, status, created_by, created_at) VALUES
('Welcome to Ocean HRMS', 'Ocean Infotech HRMS is now live. Update your profile and explore the employee portal.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 1, 1, NOW());

INSERT INTO settings (setting_key, setting_value, updated_at) VALUES
('company_name', 'Ocean Infotech', NOW()),
('company_tagline', 'Be seen, get connected', NOW()),
('company_email', 'hr@oceaninfotech.com', NOW()),
('company_phone', '+91 00000 00000', NOW()),
('company_address', 'Ahmedabad, Gujarat, India', NOW()),
('brand_primary', '#F5A01B', NOW()),
('brand_ink', '#211F23', NOW());
