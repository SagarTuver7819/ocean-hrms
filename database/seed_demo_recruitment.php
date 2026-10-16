<?php

/**
 * Demo IT job applications. Selected candidates are converted into employees.
 * Safe to re-run: candidates are matched by email.
 * Run: php database/seed_demo_recruitment.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Models\Candidate;
use App\Models\Employee;

$db = Database::connect();

$designation = function (string $name) use ($db): array {
    $s = $db->prepare('SELECT id, department_id FROM designations WHERE name = ? LIMIT 1');
    $s->execute([$name]);
    return $s->fetch() ?: ['id' => null, 'department_id' => null];
};

$candidates = [
    [
        'first' => 'Harsh', 'last' => 'Patel', 'gender' => 'male', 'dob' => '1999-03-12', 'position' => 'Web Developer',
        'mobile' => '9825011201', 'parent' => '9825011291', 'email' => 'harsh.patel.dev@gmail.com',
        'address' => '12, Shivalik Residency, Satellite', 'city' => 'Ahmedabad', 'pin' => '380015',
        'skills' => 'PHP, Laravel, MySQL, JavaScript, jQuery, REST API', 'other' => 'Git, Bootstrap, AJAX, Postman',
        'exp' => 26, 'current' => 360000, 'expected' => 480000, 'notice' => '30 Days',
        'edu' => [['B.E.', 'L.D. College of Engineering', 'Computer Engineering', '2020', '7.8 CGPA']],
        'work' => [['WebSquare Technologies', 'PHP Developer', '2022-08', null, 1, 'PHP, Laravel, MySQL', 'Built CRM and billing modules, REST APIs for mobile app.']],
        'status' => 'selected', 'scores' => [5, 4, 4, 5], 'joining' => '2026-09-14',
        'remarks' => 'Strong Laravel fundamentals, cleared machine test.',
    ],
    [
        'first' => 'Krupa', 'last' => 'Shah', 'gender' => 'female', 'dob' => '2000-07-25', 'position' => 'Web Designer',
        'mobile' => '9898022302', 'parent' => '9898022392', 'email' => 'krupa.shah.design@gmail.com',
        'address' => 'B-204, Swagat Flora, Chandkheda', 'city' => 'Ahmedabad', 'pin' => '382424',
        'skills' => 'HTML5, CSS3, Bootstrap 5, Figma, Responsive Design', 'other' => 'Adobe XD, Photoshop, Tailwind CSS',
        'exp' => 18, 'current' => 264000, 'expected' => 360000, 'notice' => '15 Days',
        'edu' => [['B.Sc. IT', 'Gujarat University', 'Information Technology', '2021', '72%']],
        'work' => [['Pixel Crafters', 'Junior Web Designer', '2024-04', null, 1, 'Figma, HTML, CSS', 'Designed landing pages and converted Figma to responsive HTML.']],
        'status' => 'selected', 'scores' => [4, 5, 4, 5], 'joining' => '2026-09-21',
        'remarks' => 'Excellent UI sense and pixel-perfect HTML.',
    ],
    [
        'first' => 'Dhruv', 'last' => 'Mehta', 'gender' => 'male', 'dob' => '2003-01-09', 'position' => 'Web Developer',
        'mobile' => '9712033403', 'parent' => '9712033493', 'email' => 'dhruv.mehta.js@gmail.com',
        'address' => '7, Sundarvan Society, Vastrapur', 'city' => 'Ahmedabad', 'pin' => '380054',
        'skills' => 'React.js, Node.js, JavaScript, MongoDB', 'other' => 'Express, Git, HTML, CSS',
        'exp' => 0, 'current' => null, 'expected' => 240000, 'notice' => 'Immediate',
        'edu' => [['BCA', 'Silver Oak University', 'Computer Applications', '2025', '8.4 CGPA']],
        'work' => [],
        'status' => 'selected', 'scores' => [4, 4, 5, 5], 'joining' => '2026-10-01',
        'remarks' => 'Fresher with good MERN projects. Hired as trainee developer.',
    ],
    [
        'first' => 'Riya', 'last' => 'Desai', 'gender' => 'female', 'dob' => '1998-11-30', 'position' => 'Designing',
        'mobile' => '9426044504', 'parent' => '9426044594', 'email' => 'riya.desai.creative@gmail.com',
        'address' => '31, Parshwanath Nagar, Maninagar', 'city' => 'Ahmedabad', 'pin' => '380008',
        'skills' => 'Photoshop, Illustrator, Figma, Branding, Social Media Creatives', 'other' => 'CorelDRAW, Canva, After Effects',
        'exp' => 38, 'current' => 300000, 'expected' => 420000, 'notice' => '30 Days',
        'edu' => [['B.Des.', 'Anant National University', 'Communication Design', '2020', '7.2 CGPA']],
        'work' => [['Brandverse Studio', 'Graphic Designer', '2023-08', null, 1, 'Photoshop, Illustrator', 'Brand identity, social media creatives and print collaterals.']],
        'status' => 'selected', 'scores' => [5, 4, 4, 4], 'joining' => '2026-09-28',
        'remarks' => 'Strong portfolio, good communication.',
    ],
    [
        'first' => 'Parth', 'last' => 'Joshi', 'gender' => 'male', 'dob' => '1997-05-18', 'position' => 'Web Developer',
        'mobile' => '9979055605', 'parent' => '9979055695', 'email' => 'parth.joshi.py@gmail.com',
        'address' => '18, Raghuvir Park, Naranpura', 'city' => 'Ahmedabad', 'pin' => '380013',
        'skills' => 'Python, Django, PostgreSQL', 'other' => 'Docker, Linux',
        'exp' => 30, 'current' => 420000, 'expected' => 720000, 'notice' => '60 Days',
        'edu' => [['MCA', 'Nirma University', 'Computer Applications', '2021', '7.0 CGPA']],
        'work' => [['DataNest Solutions', 'Python Developer', '2023-03', null, 1, 'Python, Django', 'Backend APIs for analytics dashboard.']],
        'status' => 'rejected', 'scores' => [3, 3, 2, 3], 'remarks' => 'Expected CTC too high and long notice period.',
    ],
    [
        'first' => 'Nidhi', 'last' => 'Trivedi', 'gender' => 'female', 'dob' => '2001-02-14', 'position' => 'Web Designer',
        'mobile' => '9737066706', 'parent' => '9737066796', 'email' => 'nidhi.trivedi.ui@gmail.com',
        'address' => '5, Krishna Kunj, Bopal', 'city' => 'Ahmedabad', 'pin' => '380058',
        'skills' => 'HTML, CSS, WordPress', 'other' => 'Elementor',
        'exp' => 8, 'current' => 180000, 'expected' => 300000, 'notice' => '15 Days',
        'edu' => [['BCA', 'Gujarat University', 'Computer Applications', '2022', '65%']],
        'work' => [['Smart Web Hub', 'WordPress Designer', '2026-02', null, 1, 'WordPress', 'Theme customisation for small business sites.']],
        'status' => 'rejected', 'scores' => [2, 4, 2, 4], 'remarks' => 'Limited custom design skills.',
    ],
    [
        'first' => 'Yash', 'last' => 'Chauhan', 'gender' => 'male', 'dob' => '2002-09-03', 'position' => 'Web Developer',
        'mobile' => '9601077807', 'parent' => '9601077897', 'email' => 'yash.chauhan.flutter@gmail.com',
        'address' => '22, Shanti Nagar, Gota', 'city' => 'Ahmedabad', 'pin' => '382481',
        'skills' => 'Flutter, Dart, Firebase', 'other' => 'Android Studio',
        'exp' => 0, 'current' => null, 'expected' => 300000, 'notice' => 'Immediate',
        'edu' => [['B.Tech', 'GTU', 'Information Technology', '2025', '6.9 CGPA']],
        'work' => [],
        'status' => 'rejected', 'scores' => [2, 3, 3, 3], 'remarks' => 'Web fundamentals not clear.',
    ],
    [
        'first' => 'Aakash', 'last' => 'Rana', 'gender' => 'male', 'dob' => '1998-12-21', 'position' => 'Web Developer',
        'mobile' => '9824088908', 'parent' => '9824088998', 'email' => 'aakash.rana.php@gmail.com',
        'address' => '9, Sahjanand Society, Nikol', 'city' => 'Ahmedabad', 'pin' => '382350',
        'skills' => 'PHP, CodeIgniter, MySQL, jQuery', 'other' => 'Bootstrap, Git',
        'exp' => 42, 'current' => 390000, 'expected' => 500000, 'notice' => '30 Days',
        'edu' => [['B.E.', 'VGEC Chandkheda', 'Information Technology', '2020', '7.4 CGPA']],
        'work' => [['CodeBucket Infotech', 'Software Developer', '2023-01', null, 1, 'PHP, CodeIgniter', 'ERP modules and payment gateway integration.']],
        'status' => 'new',
    ],
    [
        'first' => 'Megha', 'last' => 'Vyas', 'gender' => 'female', 'dob' => '2001-06-06', 'position' => 'Designing',
        'mobile' => '9638099009', 'parent' => '9638099099', 'email' => 'megha.vyas.art@gmail.com',
        'address' => '44, Sahyog Flats, Paldi', 'city' => 'Ahmedabad', 'pin' => '380007',
        'skills' => 'Illustrator, Photoshop, Logo Design', 'other' => 'Canva, Premiere Pro',
        'exp' => 14, 'current' => 216000, 'expected' => 300000, 'notice' => '15 Days',
        'edu' => [['BFA', 'CEPT University', 'Applied Arts', '2023', '70%']],
        'work' => [['Inkpot Media', 'Graphic Designer', '2025-08', null, 1, 'Illustrator', 'Logos, brochures and ad creatives.']],
        'status' => 'new',
    ],
    [
        'first' => 'Kunal', 'last' => 'Solanki', 'gender' => 'male', 'dob' => '2000-04-27', 'position' => 'Web Developer',
        'mobile' => '9510010110', 'parent' => '9510010190', 'email' => 'kunal.solanki.mern@gmail.com',
        'address' => '3, Om Residency, Thaltej', 'city' => 'Ahmedabad', 'pin' => '380059',
        'skills' => 'MongoDB, Express, React, Node.js, TypeScript', 'other' => 'Next.js, Redux, AWS',
        'exp' => 22, 'current' => 340000, 'expected' => 460000, 'notice' => '30 Days',
        'edu' => [['B.Tech', 'Nirma University', 'Computer Science', '2022', '8.1 CGPA']],
        'work' => [['Stackwave Labs', 'MERN Developer', '2024-12', null, 1, 'React, Node.js', 'SaaS dashboard and admin panel.']],
        'status' => 'new',
    ],
];

$candidateModel = new Candidate();
$employees = new Employee();
$adminId = (int) ($db->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1")->fetchColumn() ?: 0) ?: null;

$exists = $db->prepare('SELECT id FROM candidates WHERE email = ? LIMIT 1');
$insCand = $db->prepare(
    "INSERT INTO candidates
     (application_no, public_token, department_id, designation_id, position_other, employment_type, work_mode, source,
      first_name, last_name, mobile, parent_mobile, email, date_of_birth, gender, marital_status, address, city, state, pincode,
      is_fresher, total_experience_months, current_ctc, expected_ctc, notice_period, current_location, ready_to_relocate,
      primary_skills, secondary_skills, why_join, declaration, status, hr_remarks, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, 'full_time', 'office', ?, ?, ?, ?, ?, ?, ?, ?, 'Single', ?, ?, 'Gujarat', ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, 1, ?, ?, ?, ?)"
);
$insEdu = $db->prepare('INSERT INTO candidate_education (candidate_id, degree, institute, specialization, passing_year, percentage_grade) VALUES (?, ?, ?, ?, ?, ?)');
$insExp = $db->prepare('INSERT INTO candidate_experience (candidate_id, company, designation, from_month, to_month, is_current, tech_stack, responsibilities) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$insRound = $db->prepare(
    'INSERT INTO candidate_interviews (candidate_id, round, interview_at, mode, interviewer, technical_score, communication_score, problem_solving_score, attitude_score, result, feedback, created_by, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
);
$sources = ['LinkedIn', 'Naukri', 'Walk-in', 'Employee Referral', 'Company Website'];
$created = 0;
$converted = 0;

foreach ($candidates as $i => $p) {
    $exists->execute([$p['email']]);
    if ($exists->fetchColumn()) {
        continue;
    }
    $des = $designation($p['position']);
    $appliedAt = date('Y-m-d H:i:s', strtotime('2026-09-01 10:30 +' . ($i * 2) . ' days'));
    $prefix = 'OIR-' . date('Ymd', strtotime($appliedAt)) . '-';
    $n = $db->prepare('SELECT COUNT(*) FROM candidates WHERE application_no LIKE ?');
    $n->execute([$prefix . '%']);
    $appNo = $prefix . str_pad((string) ((int) $n->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);

    $insCand->execute([
        $appNo, bin2hex(random_bytes(16)), $des['department_id'], $des['id'], $p['position'], $sources[$i % count($sources)],
        $p['first'], $p['last'], $p['mobile'], $p['parent'], $p['email'], $p['dob'], $p['gender'],
        $p['address'], $p['city'], $p['pin'],
        $p['exp'] === 0 ? 1 : 0, $p['exp'], $p['current'], $p['expected'], $p['notice'], $p['city'],
        $p['skills'], $p['other'], 'I want to grow my career with a product-focused IT company like Ocean Infotech.',
        $p['status'], $p['remarks'] ?? null, $appliedAt, $appliedAt,
    ]);
    $candId = (int) $db->lastInsertId();
    $created++;

    foreach ($p['edu'] as $ed) {
        $insEdu->execute([$candId, ...$ed]);
    }
    foreach ($p['work'] as $w) {
        $insExp->execute([$candId, ...$w]);
    }

    if (!empty($p['scores'])) {
        [$t, $c, $l, $a] = $p['scores'];
        $pass = $p['status'] === 'selected';
        $insRound->execute([$candId, 'technical', date('Y-m-d 11:00:00', strtotime($appliedAt . ' +2 days')), 'in_person', 'Tech Lead',
            $t, $c, $l, $a, $pass || $t >= 3 ? 'pass' : 'fail', $pass ? 'Good technical depth.' : 'Below expectation in practical task.', $adminId]);
        $insRound->execute([$candId, 'hr', date('Y-m-d 16:00:00', strtotime($appliedAt . ' +3 days')), 'in_person', 'HR Manager',
            null, $c, null, $a, $pass ? 'pass' : 'fail', $pass ? 'Culture fit, ready to join.' : ($p['remarks'] ?? ''), $adminId]);
    }

    if ($p['status'] !== 'selected') {
        continue;
    }

    $c = $candidateModel->fullProfile($candId);
    $empId = $employees->create([
        'employee_code'        => $employees->nextCode(),
        'first_name'           => $c['first_name'],
        'last_name'            => $c['last_name'],
        'gender'               => $c['gender'],
        'date_of_birth'        => $c['date_of_birth'],
        'mobile'               => $c['mobile'],
        'personal_email'       => $c['email'],
        'official_email'       => strtolower($c['first_name'] . '.' . $c['last_name']) . '@oceaninfotech.co.in',
        'marital_status'       => 'Single',
        'nationality'          => 'Indian',
        'current_address'      => $c['address'],
        'current_city'         => $c['city'],
        'current_state'        => 'Gujarat',
        'current_country'      => 'India',
        'current_pincode'      => $c['pincode'],
        'same_as_current'      => 1,
        'joining_date'         => $p['joining'],
        'employment_type'      => 'full_time',
        'department_id'        => $c['department_id'],
        'designation_id'       => $c['designation_id'],
        'team_id'              => 1,
        'branch_id'            => 1,
        'work_location'        => 'Ahmedabad Office',
        'shift_id'             => 1,
        'probation_period_days'=> 90,
        'notice_period_days'   => 30,
        'status'               => 'probation',
        'created_at'           => date('Y-m-d H:i:s'),
    ]);

    foreach ($c['education'] as $ed) {
        $db->prepare('INSERT INTO employee_education (employee_id, degree, institute, passing_year, percentage_grade, created_at) VALUES (?, ?, ?, ?, ?, NOW())')
            ->execute([$empId, $ed['degree'] . ' (' . $ed['specialization'] . ')', $ed['institute'], $ed['passing_year'], $ed['percentage_grade']]);
    }

    $monthly = round((float) $p['expected'] / 12);
    \App\Services\Salary::set($empId, $monthly, $p['joining'], 'Final salary at selection');
    $candidateModel->update($candId, [
        'offered_salary' => $monthly, 'joining_date' => $p['joining'], 'probation_months' => 3, 'selected_at' => date('Y-m-d H:i:s'),
    ]);

    $db->prepare('INSERT INTO employee_bank_details (employee_id, bank_name, account_holder_name, account_number, ifsc_code, branch, payment_mode, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())')
        ->execute([$empId, 'HDFC Bank', $c['full_name'], '50100' . substr($c['mobile'], -7) . '21', 'HDFC0001234', 'Satellite, Ahmedabad', 'bank_transfer']);
    $db->prepare('INSERT INTO employee_emergency_contacts (employee_id, contact_name, relationship, mobile, created_at) VALUES (?, ?, ?, ?, NOW())')
        ->execute([$empId, 'Mr. ' . $c['last_name'], 'Father', $c['parent_mobile']]);

    $candidateModel->update($candId, ['employee_id' => $empId, 'updated_at' => date('Y-m-d H:i:s')]);
    $employees->addTimeline($empId, 'Hired via recruitment', 'Application ' . $c['application_no'] . ' for ' . $c['position'], $p['joining']);
    $converted++;
}

echo "Demo applications created: {$created}, converted to employees: {$converted}.\n";
