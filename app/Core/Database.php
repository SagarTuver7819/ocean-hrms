<?php

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance instanceof PDO) {
            return self::$instance;
        }

        $cfg = require BASE_PATH . '/app/Config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['dbname'],
            $cfg['charset']
        );

        // #region agent log
        $dbg = static function (string $hypothesisId, string $message, array $data = []): void {
            $line = json_encode([
                'sessionId' => '5cf050',
                'runId' => 'pre-fix',
                'hypothesisId' => $hypothesisId,
                'location' => 'Database.php:connect',
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES);
            @file_put_contents(BASE_PATH . '/debug-5cf050.log', $line . "\n", FILE_APPEND);
        };
        $t0 = microtime(true);
        $dbg('B', 'pdo_connect_start', ['host' => $cfg['host'], 'port' => $cfg['port'], 'dbname' => $cfg['dbname']]);
        // #endregion

        try {
            self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            // #region agent log
            $dbg('B', 'pdo_connect_ok', ['ms' => (int) round((microtime(true) - $t0) * 1000)]);
            $t1 = microtime(true);
            // #endregion
            self::$instance->exec("SET time_zone = '" . date('P') . "'");
            // #region agent log
            $dbg('A', 'timezone_set_ok', ['ms' => (int) round((microtime(true) - $t1) * 1000)]);
            $t2 = microtime(true);
            self::$instance->query('SELECT 1')->fetchColumn();
            $dbg('A', 'select1_ok', ['ms' => (int) round((microtime(true) - $t2) * 1000)]);
            // #endregion
        } catch (PDOException $e) {
            // #region agent log
            $dbg('E', 'pdo_connect_fail', [
                'ms' => (int) round((microtime(true) - $t0) * 1000),
                'code' => $e->getCode(),
                'error' => $e->getMessage(),
            ]);
            // #endregion
            if (APP_DEBUG) {
                throw $e;
            }
            http_response_code(500);
            exit('Database connection failed.');
        }

        return self::$instance;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function first(string $sql, array $params = []): ?array
    {
        return self::run($sql, $params)->fetch() ?: null;
    }

    public static function value(string $sql, array $params = []): mixed
    {
        $value = self::run($sql, $params)->fetchColumn();
        return $value === false ? null : $value;
    }
}
