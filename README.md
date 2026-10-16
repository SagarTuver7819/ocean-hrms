# Ocean HRMS

Web-based Human Resource Management System for **Ocean Infotech**.

**Stack:** Core PHP 8.2+ · MySQL · Bootstrap 5 · jQuery/AJAX · DataTables · Select2  

**UI:** Armor HRMS concept · Ocean Infotech branding (Orange `#F5A01B` + Dark Charcoal `#211F23`)

## Setup (XAMPP)

1. Ensure Apache + MySQL are running.
2. Project path: `C:\xampp\htdocs\Ocean_HRMS`
3. Install database:

```
http://localhost/Ocean_HRMS/database/install.php
```

4. Open app:

```
http://localhost/Ocean_HRMS/public/login
```

5. Delete `database/install.php` after setup.

## Demo logins

| Portal   | Credential ID              | Password   |
|----------|----------------------------|------------|
| Admin    | `admin`                    | `admin`     |
| HR Head  | `hrhead@oceaninfotech.com` | `Admin@123` |
| Employee | `OI20260001`               | `Admin@123` |

## MVP modules included

- Login (Admin / Employee) with Ocean logo
- Roles & permissions
- HR Dashboard
- Departments / Designations
- Employee Master (multi-tab) + 360 profile + timeline + documents
- Create Employee Login
- Attendance (punch in/out + HR dashboard + report)
- Leave (apply / approve / balances / types)
- Salary structure + history
- Employee Query / Helpdesk
- Employee Portal dashboard
- Announcements, Holidays, Company Settings
- Activity logs + notifications stub

## Logo

Located at: `public/assets/uploads/logo/ocean-infotech-logo.png`
