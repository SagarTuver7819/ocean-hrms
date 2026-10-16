<?php

use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Database;
use App\Core\View;

function asset(string $path): string
{
    return View::asset($path);
}

function url(string $path = ''): string
{
    return View::url($path);
}

function e(?string $value): string
{
    return View::e($value);
}

function csrf_field(): string
{
    return CSRF::field();
}

function csrf_token(): string
{
    return CSRF::token();
}

function auth_user(): ?array
{
    return Auth::user();
}

function can(string $permission): bool
{
    return Auth::hasPermission($permission);
}

function old(string $key, mixed $default = ''): mixed
{
    $value = $_SESSION['_old'][$key] ?? $default;
    return is_string($value) ? e($value) : $value;
}

function set_old(array $data): void
{
    $_SESSION['_old'] = $data;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function activity_log(string $module, string $action, ?int $recordId = null, mixed $old = null, mixed $new = null): void
{
    try {
        $db = Database::connect();
        $stmt = $db->prepare(
            'INSERT INTO activity_logs (user_id, module, action, record_id, old_value, new_value, ip_address, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
        );
        $stmt->execute([
            Auth::id(),
            $module,
            $action,
            $recordId,
            $old !== null ? json_encode($old) : null,
            $new !== null ? json_encode($new) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        // silent fail for logging
    }
}

function notify(int $userId, string $title, string $message, ?string $link = null): void
{
    try {
        $db = Database::connect();
        $stmt = $db->prepare(
            'INSERT INTO notifications (user_id, title, message, link, is_read, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())'
        );
        $stmt->execute([$userId, $title, $message, $link]);
    } catch (Throwable $e) {
        // silent
    }
}

/** Notifies every active HR / admin user. */
function notify_hr(string $title, string $message, ?string $link = null): void
{
    try {
        $users = Database::all(
            "SELECT DISTINCT u.id FROM users u
             INNER JOIN user_roles ur ON ur.user_id = u.id
             INNER JOIN roles r ON r.id = ur.role_id
             WHERE u.status = 1 AND r.slug IN ('super_admin','hr_head','hr_executive')"
        );
        foreach ($users as $u) {
            notify((int) $u['id'], $title, $message, $link);
        }
    } catch (Throwable $e) {
        // silent
    }
}

/** Minutes as "8h 05m". */
function hm(int|float|null $minutes, string $empty = '—'): string
{
    $m = (int) round((float) $minutes);
    if ($m <= 0) return $empty;
    return intdiv($m, 60) . 'h ' . str_pad((string) ($m % 60), 2, '0', STR_PAD_LEFT) . 'm';
}

/** Renders a '|'-separated break list ("09:10 AM – 09:25 AM|…") as one chip per break. */
function break_chips(?string $list): string
{
    if (!$list) return '<span class="text-muted">—</span>';
    return implode('', array_map(
        fn($b) => '<span class="break-chip' . (str_contains($b, 'on break') ? ' live' : '') . '">' . e($b) . '</span>',
        explode('|', $list)
    ));
}

function time_ago(?string $datetime): string
{
    if (!$datetime) return '';
    $s = time() - strtotime($datetime);
    if ($s < 60) return 'just now';
    if ($s < 3600) return intdiv($s, 60) . ' min ago';
    if ($s < 86400) return intdiv($s, 3600) . ' hr ago';
    if ($s < 172800) return 'yesterday';
    return date('d M, h:i A', strtotime($datetime));
}

function format_time(?string $datetime, string $empty = '—'): string
{
    return $datetime ? date('h:i A', strtotime($datetime)) : $empty;
}

function format_date(?string $date, string $format = 'd M Y'): string
{
    if (!$date) {
        return '—';
    }
    return date($format, strtotime($date));
}

function money(float|int|string|null $amount): string
{
    return '₹' . number_format((float) $amount, 2);
}

/** Indian digit grouping without symbol, e.g. 420000 -> "4,20,000". */
function inr(float|int|string|null $amount): string
{
    $n = (string) (int) round(abs((float) $amount));
    $last3 = substr($n, -3);
    $rest = substr($n, 0, -3);
    $grouped = $rest !== '' ? preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last3 : $last3;
    return ((float) $amount < 0 ? '-' : '') . $grouped;
}

/** Rupee amount in words using the Indian system, e.g. "Forty Thousand Rupees Only". */
function amount_in_words(float|int|string $amount): string
{
    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
        'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    $words = function (int $n) use (&$words, $ones, $tens): string {
        if ($n < 20) return $ones[$n];
        if ($n < 100) return trim($tens[intdiv($n, 10)] . ' ' . $ones[$n % 10]);
        foreach ([[10000000, 'Crore'], [100000, 'Lakh'], [1000, 'Thousand'], [100, 'Hundred']] as [$unit, $label]) {
            if ($n >= $unit) return trim($words(intdiv($n, $unit)) . ' ' . $label . ' ' . $words($n % $unit));
        }
        return '';
    };
    $n = (int) round((float) $amount);
    return ($n === 0 ? 'Zero' : $words($n)) . ' Rupees Only';
}

/**
 * Validate and move an uploaded file. Returns the path relative to BASE_PATH,
 * null when no file was sent, or throws RuntimeException with a user-facing message.
 */
function store_upload(?array $file, string $relativeDir, array $allowedMimes, string $prefix = 'file_', ?int $maxBytes = null): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('File upload failed. Please try again.');
    }
    $app = require BASE_PATH . '/app/Config/app.php';
    $maxBytes ??= $app['upload_max'];
    if ($file['size'] > $maxBytes) {
        throw new RuntimeException('File exceeds ' . round($maxBytes / 1048576) . 'MB limit.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $allowedMimes, true)) {
        throw new RuntimeException('Invalid file type: ' . $file['name']);
    }
    $extMap = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif',
        'application/pdf' => 'pdf', 'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    $dir = BASE_PATH . '/' . trim($relativeDir, '/');
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = $prefix . bin2hex(random_bytes(8)) . '.' . ($extMap[$mime] ?? 'bin');
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('Could not save uploaded file.');
    }
    return trim($relativeDir, '/') . '/' . $name;
}

function settings_all(bool $refresh = false): array
{
    static $cache = null;
    if ($cache === null || $refresh) {
        $cache = [];
        try {
            foreach (Database::connect()->query('SELECT setting_key, setting_value FROM settings') as $row) {
                $cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $e) {
            // settings table unavailable
        }
    }
    return $cache;
}

function setting(string $key, mixed $default = null): mixed
{
    $value = settings_all()[$key] ?? null;
    return ($value === null || $value === '') ? $default : $value;
}

function greeting(): string
{
    $h = (int) date('G');
    if ($h < 12) return 'Good Morning';
    if ($h < 17) return 'Good Afternoon';
    return 'Good Evening';
}
