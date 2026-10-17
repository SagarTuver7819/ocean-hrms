<?php
/**
 * Temporary local DB probe for debug session 5cf050.
 * Open: /Ocean_HRMS/public/debug-db-ping.php
 */
define('BASE_PATH', dirname(__DIR__));
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(12);

$log = BASE_PATH . '/debug-5cf050.log';
$write = static function (string $hid, string $msg, array $data = []) use ($log): void {
    $line = json_encode([
        'sessionId' => '5cf050',
        'runId' => 'pre-fix',
        'hypothesisId' => $hid,
        'location' => 'debug-db-ping.php',
        'message' => $msg,
        'data' => $data,
        'timestamp' => (int) round(microtime(true) * 1000),
    ], JSON_UNESCAPED_SLASHES);
    @file_put_contents($log, $line . "\n", FILE_APPEND);
};

$write('C', 'ping_start', ['php' => PHP_VERSION]);
echo "ping_start\n";

$cfg = require BASE_PATH . '/app/Config/database.php';
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $cfg['host'],
    $cfg['port'],
    $cfg['dbname'],
    $cfg['charset']
);

$t0 = microtime(true);
try {
    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $ms = (int) round((microtime(true) - $t0) * 1000);
    $write('B', 'connect_ok', ['ms' => $ms]);
    echo "connect_ok {$ms}ms\n";

    $t1 = microtime(true);
    $n = (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
    $qms = (int) round((microtime(true) - $t1) * 1000);
    $write('A', 'employees_count_ok', ['count' => $n, 'ms' => $qms]);
    echo "employees_count={$n} {$qms}ms\n";
} catch (Throwable $e) {
    $ms = (int) round((microtime(true) - $t0) * 1000);
    $write('E', 'ping_fail', ['ms' => $ms, 'error' => $e->getMessage()]);
    echo "FAIL {$ms}ms :: " . $e->getMessage() . "\n";
    exit(1);
}

echo "done\n";
