<?php

use App\Core\Router;

/** @var Router $router */

// Auth
$router->get('/login', ['AuthController', 'showLogin'], ['GuestMiddleware']);
$router->post('/login', ['AuthController', 'login'], ['GuestMiddleware']);
$router->get('/logout', ['AuthController', 'logout'], ['AuthMiddleware']);
$router->get('/change-password', ['AuthController', 'showChangePassword'], ['AuthMiddleware']);
$router->post('/change-password', ['AuthController', 'changePassword'], ['AuthMiddleware']);

// DB sync (token OR logged-in admin with employee.delete) — applies pending schema patches
$router->get('/db-sync', ['DbSyncController', 'run']);

// Organization chart
$router->get('/org-chart', ['OrgChartController', 'index'], ['AuthMiddleware']);
$router->get('/org-chart/print', ['OrgChartController', 'print'], ['AuthMiddleware']);
$router->post('/org-chart/leaders', ['OrgChartController', 'saveLeaders'], ['AuthMiddleware']);

// HR / Admin
$router->get('/', ['DashboardController', 'index'], ['AuthMiddleware']);
$router->get('/dashboard', ['DashboardController', 'index'], ['AuthMiddleware']);

// Masters
$router->get('/departments', ['DepartmentController', 'index'], ['AuthMiddleware']);
$router->post('/departments', ['DepartmentController', 'store'], ['AuthMiddleware']);
$router->post('/departments/{id}/update', ['DepartmentController', 'update'], ['AuthMiddleware']);
$router->post('/departments/{id}/delete', ['DepartmentController', 'destroy'], ['AuthMiddleware']);

$router->get('/designations', ['DesignationController', 'index'], ['AuthMiddleware']);
$router->post('/designations', ['DesignationController', 'store'], ['AuthMiddleware']);
$router->post('/designations/{id}/update', ['DesignationController', 'update'], ['AuthMiddleware']);
$router->post('/designations/{id}/delete', ['DesignationController', 'destroy'], ['AuthMiddleware']);

// Employees
$router->get('/employees', ['EmployeeController', 'index'], ['AuthMiddleware']);
$router->get('/employees/create', ['EmployeeController', 'create'], ['AuthMiddleware']);
$router->post('/employees', ['EmployeeController', 'store'], ['AuthMiddleware']);
$router->get('/employees/self-fill', ['EmployeeSelfFillController', 'admin'], ['AuthMiddleware']);
$router->post('/employees/self-fill', ['EmployeeSelfFillController', 'createInvite'], ['AuthMiddleware']);
$router->post('/employees/self-fill/{id}/revoke', ['EmployeeSelfFillController', 'revoke'], ['AuthMiddleware']);
$router->get('/employees/logins', ['EmployeeController', 'logins'], ['AuthMiddleware']);
$router->get('/employees/logins/export', ['EmployeeController', 'exportLogins'], ['AuthMiddleware']);
$router->post('/employees/logins/generate', ['EmployeeController', 'issueAllLogins'], ['AuthMiddleware']);
$router->post('/employees/{id}/login/toggle', ['EmployeeController', 'toggleLogin'], ['AuthMiddleware']);
$router->get('/employees/{id}', ['EmployeeController', 'show'], ['AuthMiddleware']);
$router->get('/employees/{id}/edit', ['EmployeeController', 'edit'], ['AuthMiddleware']);
$router->post('/employees/{id}', ['EmployeeController', 'update'], ['AuthMiddleware']);
$router->post('/employees/{id}/delete', ['EmployeeController', 'destroy'], ['AuthMiddleware']);
$router->post('/employees/{id}/login', ['EmployeeController', 'createLogin'], ['AuthMiddleware']);
$router->post('/employees/{id}/documents', ['EmployeeController', 'uploadDocument'], ['AuthMiddleware']);
$router->post('/employees/{id}/photo', ['EmployeeController', 'photo'], ['AuthMiddleware']);
$router->post('/employees/{id}/photo/remove', ['EmployeeController', 'removePhoto'], ['AuthMiddleware']);
$router->get('/employees/{id}/letters/{type}', ['LetterController', 'employee'], ['AuthMiddleware']);

// Public employee self-fill (QR / WhatsApp, time-limited)
$router->get('/fill/{token}', ['EmployeeSelfFillController', 'form']);
$router->post('/fill/{token}/verify', ['EmployeeSelfFillController', 'verify']);
$router->post('/fill/{token}', ['EmployeeSelfFillController', 'submit']);

// Attendance
$router->get('/attendance', ['AttendanceController', 'index'], ['AuthMiddleware']);
$router->post('/attendance/punch', ['AttendanceController', 'punch'], ['AuthMiddleware']);
$router->get('/attendance/report', ['AttendanceController', 'report'], ['AuthMiddleware']);
$router->get('/attendance/summary', ['AttendanceController', 'summary'], ['AuthMiddleware']);

// Shift master
$router->get('/shifts', ['ShiftController', 'index'], ['AuthMiddleware']);
$router->post('/shifts', ['ShiftController', 'store'], ['AuthMiddleware']);
$router->post('/shifts/assign', ['ShiftController', 'assign'], ['AuthMiddleware']);
$router->post('/shifts/{id}/update', ['ShiftController', 'update'], ['AuthMiddleware']);
$router->post('/shifts/{id}/delete', ['ShiftController', 'destroy'], ['AuthMiddleware']);

// Leave
$router->get('/leave', ['LeaveController', 'index'], ['AuthMiddleware']);
$router->post('/leave', ['LeaveController', 'store'], ['AuthMiddleware']);
$router->get('/leave/days', ['LeaveController', 'days'], ['AuthMiddleware']);
$router->get('/leave/types', ['LeaveController', 'types'], ['AuthMiddleware']);
$router->post('/leave/types', ['LeaveController', 'storeType'], ['AuthMiddleware']);
$router->post('/leave/types/{id}/update', ['LeaveController', 'updateType'], ['AuthMiddleware']);
$router->post('/leave/{id}/approve', ['LeaveController', 'approve'], ['AuthMiddleware']);
$router->post('/leave/{id}/reject', ['LeaveController', 'reject'], ['AuthMiddleware']);
$router->post('/leave/{id}/cancel', ['LeaveController', 'cancel'], ['AuthMiddleware']);

// Notifications
$router->get('/notifications', ['NotificationController', 'index'], ['AuthMiddleware']);
$router->get('/notifications/feed', ['NotificationController', 'feed'], ['AuthMiddleware']);
$router->post('/notifications/read-all', ['NotificationController', 'readAll'], ['AuthMiddleware']);
$router->get('/notifications/{id}/open', ['NotificationController', 'open'], ['AuthMiddleware']);

// Salary
$router->get('/salary', ['SalaryController', 'index'], ['AuthMiddleware']);
$router->get('/salary/register', ['SalaryController', 'register'], ['AuthMiddleware']);
$router->get('/salary/register/pdf', ['SalaryController', 'registerPdf'], ['AuthMiddleware']);
$router->get('/salary/employee/{id}', ['SalaryController', 'employee'], ['AuthMiddleware']);
$router->post('/salary/employee/{id}', ['SalaryController', 'save'], ['AuthMiddleware']);
$router->post('/salary/employee/{id}/pt', ['SalaryController', 'savePt'], ['AuthMiddleware']);

// Queries
$router->get('/queries', ['QueryController', 'index'], ['AuthMiddleware']);
$router->get('/queries/create', ['QueryController', 'create'], ['AuthMiddleware']);
$router->post('/queries', ['QueryController', 'store'], ['AuthMiddleware']);
$router->get('/queries/{id}', ['QueryController', 'show'], ['AuthMiddleware']);
$router->post('/queries/{id}/reply', ['QueryController', 'reply'], ['AuthMiddleware']);

// Settings
$router->get('/settings', ['SettingController', 'index'], ['AuthMiddleware']);
$router->post('/settings', ['SettingController', 'save'], ['AuthMiddleware']);
$router->get('/settings/letterhead', ['SettingController', 'letterhead'], ['AuthMiddleware']);
$router->post('/settings/letterhead', ['SettingController', 'saveLetterhead'], ['AuthMiddleware']);

// Public career form (QR) — no login
$router->get('/apply', ['CareerController', 'apply']);
$router->post('/apply', ['CareerController', 'submit']);
$router->get('/apply/thank-you/{token}', ['CareerController', 'thanks']);
$router->get('/apply/print/{token}', ['CareerController', 'printApplication']);

// Recruitment
$router->get('/recruitment', ['RecruitmentController', 'index'], ['AuthMiddleware']);
$router->get('/recruitment/interviews', ['RecruitmentController', 'interviews'], ['AuthMiddleware']);
$router->get('/recruitment/qr', ['RecruitmentController', 'qr'], ['AuthMiddleware']);
$router->get('/recruitment/{id}', ['RecruitmentController', 'show'], ['AuthMiddleware']);
$router->get('/recruitment/{id}/print', ['RecruitmentController', 'printForm'], ['AuthMiddleware']);
$router->get('/recruitment/{id}/offer-letter', ['LetterController', 'candidateOffer'], ['AuthMiddleware']);
$router->get('/recruitment/{id}/file/{type}', ['RecruitmentController', 'file'], ['AuthMiddleware']);
$router->post('/recruitment/{id}/status', ['RecruitmentController', 'updateStatus'], ['AuthMiddleware']);
$router->post('/recruitment/{id}/select', ['RecruitmentController', 'select'], ['AuthMiddleware']);
$router->post('/recruitment/{id}/interviews', ['RecruitmentController', 'storeInterview'], ['AuthMiddleware']);
$router->post('/recruitment/{id}/interviews/{roundId}/delete', ['RecruitmentController', 'deleteInterview'], ['AuthMiddleware']);
$router->post('/recruitment/{id}/convert', ['RecruitmentController', 'convert'], ['AuthMiddleware']);
$router->post('/recruitment/{id}/delete', ['RecruitmentController', 'destroy'], ['AuthMiddleware']);

// Holidays & Announcements
$router->get('/holidays', ['HolidayController', 'index'], ['AuthMiddleware']);
$router->post('/holidays', ['HolidayController', 'store'], ['AuthMiddleware']);
$router->post('/holidays/{id}/update', ['HolidayController', 'update'], ['AuthMiddleware']);
$router->post('/holidays/{id}/delete', ['HolidayController', 'destroy'], ['AuthMiddleware']);
$router->get('/announcements', ['AnnouncementController', 'index'], ['AuthMiddleware']);
$router->post('/announcements', ['AnnouncementController', 'store'], ['AuthMiddleware']);
$router->get('/announcements/{id}', ['AnnouncementController', 'show'], ['AuthMiddleware']);
$router->post('/announcements/{id}/update', ['AnnouncementController', 'update'], ['AuthMiddleware']);
$router->post('/announcements/{id}/delete', ['AnnouncementController', 'destroy'], ['AuthMiddleware']);

// Employee portal
$router->get('/employee/dashboard', ['EmployeePortalController', 'dashboard'], ['AuthMiddleware']);
$router->post('/employee/wish', ['EmployeePortalController', 'wish'], ['AuthMiddleware']);
$router->get('/employee/profile', ['EmployeePortalController', 'profile'], ['AuthMiddleware']);
$router->post('/employee/photo', ['EmployeePortalController', 'photo'], ['AuthMiddleware']);
$router->post('/employee/photo/remove', ['EmployeePortalController', 'removePhoto'], ['AuthMiddleware']);
$router->get('/employee/attendance', ['EmployeePortalController', 'attendance'], ['AuthMiddleware']);
$router->get('/employee/leave', ['EmployeePortalController', 'leave'], ['AuthMiddleware']);
$router->get('/employee/salary', ['EmployeePortalController', 'salary'], ['AuthMiddleware']);
$router->get('/employee/salary/{month}', ['EmployeePortalController', 'salarySlip'], ['AuthMiddleware']);
$router->get('/employee/documents', ['EmployeePortalController', 'documents'], ['AuthMiddleware']);
$router->get('/employee/queries', ['EmployeePortalController', 'queries'], ['AuthMiddleware']);
