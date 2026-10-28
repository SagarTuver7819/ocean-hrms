<?php
$page = $page ?? '';
$isEmp = !empty($isEmp);

function nav_active(string $page, array $keys): string {
    return in_array($page, $keys, true) ? 'active' : '';
}
function group_open(string $page, array $keys): string {
    return in_array($page, $keys, true) ? 'open' : '';
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <a href="<?= url($isEmp ? 'employee/dashboard' : 'dashboard') ?>" class="brand-logo" title="Ocean HRMS">
      <img src="<?= asset($app['brand']['logo']) ?>" alt="Ocean Infotech">
    </a>
    <button type="button" class="sidebar-collapse-btn" id="sidebarCollapse" title="Collapse to icons" aria-label="Toggle sidebar">
      <i class="fa-solid fa-angles-left"></i>
    </button>
  </div>

  <div class="sidebar-search" id="sidebarSearchWrap">
    <i class="fa-solid fa-magnifying-glass ico-search"></i>
    <input type="search" id="sidebarSearch" placeholder="Search menu…" autocomplete="off">
    <button type="button" class="ico-clear" id="sidebarSearchClear" title="Clear"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <nav class="sidebar-nav" id="sidebarNav">
    <?php if ($isEmp): ?>
      <div class="nav-section-label">Employee Portal</div>

      <a href="<?= url('employee/dashboard') ?>" class="sidebar-link <?= nav_active($page, ['emp-dashboard']) ?>" data-menu="home dashboard">
        <span class="ico t-orange"><i class="fa-solid fa-house"></i></span>
        <span class="txt"><b>Home</b><small>My dashboard</small></span>
      </a>
      <a href="<?= url('employee/profile') ?>" class="sidebar-link <?= nav_active($page, ['emp-profile']) ?>" data-menu="profile personal">
        <span class="ico t-blue"><i class="fa-solid fa-id-card"></i></span>
        <span class="txt"><b>My Profile</b><small>Personal details</small></span>
      </a>
      <a href="<?= url('org-chart') ?>" class="sidebar-link <?= nav_active($page, ['org-chart']) ?>" data-menu="organization chart team structure">
        <span class="ico t-violet"><i class="fa-solid fa-sitemap"></i></span>
        <span class="txt"><b>Org Chart</b><small>Team structure</small></span>
      </a>
      <a href="<?= url('employee/leave') ?>" class="sidebar-link <?= nav_active($page, ['emp-leave']) ?>" data-menu="leave apply">
        <span class="ico t-red"><i class="fa-solid fa-calendar-check"></i></span>
        <span class="txt"><b>My Leave</b><small>Apply &amp; track leave</small></span>
      </a>
      <a href="<?= url('employee/attendance') ?>" class="sidebar-link <?= nav_active($page, ['emp-attendance']) ?>" data-menu="attendance punch">
        <span class="ico t-sky"><i class="fa-solid fa-fingerprint"></i></span>
        <span class="txt"><b>My Attendance</b><small>Punch, breaks &amp; history</small></span>
      </a>
      <a href="<?= url('employee/salary') ?>" class="sidebar-link <?= nav_active($page, ['emp-salary']) ?>" data-menu="salary payslip">
        <span class="ico t-green"><i class="fa-solid fa-file-invoice-dollar"></i></span>
        <span class="txt"><b>Salary Slip</b><small>Download monthly slip</small></span>
      </a>
      <a href="<?= url('employee/documents') ?>" class="sidebar-link <?= nav_active($page, ['emp-documents']) ?>" data-menu="documents">
        <span class="ico t-amber"><i class="fa-solid fa-folder-open"></i></span>
        <span class="txt"><b>My Documents</b><small>Uploaded files</small></span>
      </a>
      <a href="<?= url('announcements') ?>" class="sidebar-link <?= nav_active($page, ['announcements']) ?>" data-menu="announcements circulars notices">
        <span class="ico t-pink"><i class="fa-solid fa-bullhorn"></i></span>
        <span class="txt"><b>Announcements</b><small>Company notices</small></span>
      </a>
      <a href="<?= url('employee/queries') ?>" class="sidebar-link <?= nav_active($page, ['emp-queries']) ?>" data-menu="query helpdesk voice">
        <span class="ico t-indigo"><i class="fa-solid fa-headset"></i></span>
        <span class="txt"><b>Employee Query</b><small>HR · IT · Payroll helpdesk</small></span>
      </a>
      <a href="<?= url('change-password') ?>" class="sidebar-link" data-menu="password security">
        <span class="ico t-slate"><i class="fa-solid fa-key"></i></span>
        <span class="txt"><b>Change Password</b><small>Secure your login</small></span>
      </a>

    <?php else: ?>
      <div class="nav-section-label">Main</div>

      <a href="<?= url('dashboard') ?>" class="sidebar-link <?= nav_active($page, ['dashboard']) ?>" data-menu="dashboard hr workspace overview">
        <span class="ico t-orange"><i class="fa-solid fa-gauge-high"></i></span>
        <span class="txt"><b>HR Dashboard</b><small>IT company workspace</small></span>
      </a>
      <a href="<?= url('announcements') ?>" class="sidebar-link <?= nav_active($page, ['announcements']) ?>" data-menu="announcements circulars notices">
        <span class="ico t-pink"><i class="fa-solid fa-bullhorn"></i></span>
        <span class="txt"><b>Announcements</b><small>Company notices</small></span>
      </a>
      <a href="<?= url('holidays') ?>" class="sidebar-link <?= nav_active($page, ['holidays']) ?>" data-menu="holidays calendar">
        <span class="ico t-teal"><i class="fa-solid fa-umbrella-beach"></i></span>
        <span class="txt"><b>Holidays</b><small>Holiday calendar</small></span>
      </a>

      <div class="nav-section-label">Modules</div>

      <?php if (can('recruitment.manage')): ?>
      <div class="nav-group <?= group_open($page, ['recruitment','recruitment-interviews','recruitment-qr']) ?>" data-menu-group="recruitment hiring applications interview candidates qr careers">
        <button type="button" class="nav-group-btn">
          <span class="ico t-blue"><i class="fa-solid fa-user-plus"></i></span>
          <span class="txt"><b>Recruitment</b><small>Hiring pipeline</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('recruitment') ?>" class="<?= nav_active($page, ['recruitment']) ?>">Applications</a>
          <a href="<?= url('recruitment/interviews') ?>" class="<?= nav_active($page, ['recruitment-interviews']) ?>">Interview Candidates</a>
          <a href="<?= url('recruitment/qr') ?>" class="<?= nav_active($page, ['recruitment-qr']) ?>">Apply QR Code</a>
        </div>
      </div>
      <?php endif; ?>

      <div class="nav-group <?= group_open($page, ['employees', 'employee-logins', 'employee-self-fill', 'org-chart']) ?>" data-menu-group="employees staff directory exit login password credentials self fill qr whatsapp organization chart founder">
        <button type="button" class="nav-group-btn">
          <span class="ico t-violet"><i class="fa-solid fa-users"></i></span>
          <span class="txt"><b>Employees</b><small>Staff directory</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('employees') ?>" class="<?= nav_active($page, ['employees']) ?>">All Employees</a>
          <a href="<?= url('employees/create') ?>">Add Employee</a>
          <a href="<?= url('org-chart') ?>" class="<?= nav_active($page, ['org-chart']) ?>">Organization Chart</a>
          <a href="<?= url('employees/self-fill') ?>" class="<?= nav_active($page, ['employee-self-fill']) ?>">Self-Fill QR</a>
          <a href="<?= url('employees/logins') ?>" class="<?= nav_active($page, ['employee-logins']) ?>">Employee Logins</a>
          <a href="<?= url('employees?status=inactive') ?>">Inactive / Exit</a>
        </div>
      </div>

      <div class="nav-group <?= group_open($page, ['leave', 'leave-types']) ?>" data-menu-group="leave requests reports types">
        <button type="button" class="nav-group-btn">
          <span class="ico t-red"><i class="fa-solid fa-calendar-check"></i></span>
          <span class="txt"><b>Leave</b><small>Requests &amp; approvals</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('leave?status=pending') ?>" class="<?= nav_active($page, ['leave']) ?>">Leave Requests</a>
          <a href="<?= url('leave?status=all') ?>">All Leave</a>
          <a href="<?= url('leave/types') ?>" class="<?= nav_active($page, ['leave-types']) ?>">Leave Types</a>
        </div>
      </div>

      <div class="nav-group <?= group_open($page, ['attendance', 'attendance-report', 'attendance-summary', 'shifts']) ?>" data-menu-group="attendance punch reports late daily monthly shift">
        <button type="button" class="nav-group-btn">
          <span class="ico t-sky"><i class="fa-solid fa-fingerprint"></i></span>
          <span class="txt"><b>Attendance</b><small>Punch &amp; reports</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('attendance') ?>" class="<?= nav_active($page, ['attendance']) ?>">Live Today</a>
          <a href="<?= url('attendance/report') ?>" class="<?= nav_active($page, ['attendance-report']) ?>">Daily Report</a>
          <a href="<?= url('attendance/summary') ?>" class="<?= nav_active($page, ['attendance-summary']) ?>">Monthly Summary</a>
          <a href="<?= url('shifts') ?>" class="<?= nav_active($page, ['shifts']) ?>">Shift Master</a>
        </div>
      </div>

      <div class="nav-group <?= group_open($page, ['salary', 'salary-register']) ?>" data-menu-group="salary payroll monthly register">
        <button type="button" class="nav-group-btn">
          <span class="ico t-green"><i class="fa-solid fa-indian-rupee-sign"></i></span>
          <span class="txt"><b>Salary</b><small>Decided · monthly register</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('salary/register') ?>" class="<?= nav_active($page, ['salary-register']) ?>">Salary Register</a>
          <a href="<?= url('salary') ?>" class="<?= nav_active($page, ['salary']) ?>">Employee Salary</a>
        </div>
      </div>

      <div class="nav-group <?= group_open($page, ['queries']) ?>" data-menu-group="query helpdesk voice grievance it support">
        <button type="button" class="nav-group-btn">
          <span class="ico t-indigo"><i class="fa-solid fa-headset"></i></span>
          <span class="txt"><b>Employee Query</b><small>HR · IT · Payroll desk</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('queries') ?>" class="<?= nav_active($page, ['queries']) ?>">All Tickets</a>
          <a href="<?= url('queries?status=open') ?>">Open Tickets</a>
        </div>
      </div>

      <div class="nav-group <?= group_open($page, ['departments','designations']) ?>" data-menu-group="masters department designation shift">
        <button type="button" class="nav-group-btn">
          <span class="ico t-slate"><i class="fa-solid fa-database"></i></span>
          <span class="txt"><b>Masters</b><small>Master data setup</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('departments') ?>" class="<?= nav_active($page, ['departments']) ?>">Departments (IT)</a>
          <a href="<?= url('designations') ?>" class="<?= nav_active($page, ['designations']) ?>">Designations</a>
          <a href="<?= url('shifts') ?>">Shift Master</a>
          <a href="<?= url('leave/types') ?>">Leave Master</a>
          <a href="<?= url('holidays') ?>">Holiday Master</a>
        </div>
      </div>

      <div class="nav-group <?= group_open($page, ['settings','letterhead']) ?>" data-menu-group="system settings roles company letterhead">
        <button type="button" class="nav-group-btn">
          <span class="ico t-amber"><i class="fa-solid fa-gears"></i></span>
          <span class="txt"><b>System Setup</b><small>Company &amp; access</small></span>
          <i class="fa-solid fa-chevron-down chev"></i>
        </button>
        <div class="nav-sub">
          <a href="<?= url('settings') ?>" class="<?= nav_active($page, ['settings']) ?>">Company Settings</a>
          <a href="<?= url('settings/letterhead') ?>" class="<?= nav_active($page, ['letterhead']) ?>">Letterhead Setup</a>
        </div>
      </div>
    <?php endif; ?>
  </nav>

  <div class="sidebar-foot">
    <a href="<?= url($isEmp ? 'employee/dashboard' : 'dashboard') ?>"><i class="fa-solid fa-house"></i> Home</a>
  </div>
</aside>
