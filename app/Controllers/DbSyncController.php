<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Services\DbSync;

class DbSyncController extends Controller
{
    public function run(): void
    {
        $token = (string) $this->input('token', '');
        $okToken = $token !== '' && hash_equals(DbSync::token(), $token);
        $okAdmin = Auth::check() && Auth::hasPermission('employee.delete');

        if (!$okToken && !$okAdmin) {
            http_response_code(403);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>DB Sync</title></head><body style="font-family:system-ui;padding:40px;max-width:640px;margin:auto">';
            echo '<h1>DB Sync · Access denied</h1>';
            echo '<p>Open this URL with the sync token, or login as Super Admin and revisit <code>/db-sync</code>.</p>';
            echo '<p><code>?token=YOUR_TOKEN</code></p>';
            echo '</body></html>';
            exit;
        }

        // #region agent log
        $dbgLine = json_encode([
            'sessionId' => '5cf050',
            'runId' => 'pre-fix',
            'hypothesisId' => 'D',
            'location' => 'DbSyncController.php:run',
            'message' => 'db_sync_start',
            'data' => ['okToken' => $okToken, 'okAdmin' => $okAdmin],
            'timestamp' => (int) round(microtime(true) * 1000),
        ], JSON_UNESCAPED_SLASHES);
        @file_put_contents(BASE_PATH . '/debug-5cf050.log', $dbgLine . "\n", FILE_APPEND);
        // #endregion

        try {
            $log = DbSync::run();
            $ok = true;
            $error = null;
            // #region agent log
            $dbgLine = json_encode([
                'sessionId' => '5cf050',
                'runId' => 'pre-fix',
                'hypothesisId' => 'D',
                'location' => 'DbSyncController.php:run',
                'message' => 'db_sync_done',
                'data' => ['lines' => count($log)],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES);
            @file_put_contents(BASE_PATH . '/debug-5cf050.log', $dbgLine . "\n", FILE_APPEND);
            // #endregion
        } catch (\Throwable $e) {
            $log = [];
            $ok = false;
            $error = $e->getMessage();
            // #region agent log
            $dbgLine = json_encode([
                'sessionId' => '5cf050',
                'runId' => 'pre-fix',
                'hypothesisId' => 'E',
                'location' => 'DbSyncController.php:run',
                'message' => 'db_sync_exception',
                'data' => ['error' => $error],
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES);
            @file_put_contents(BASE_PATH . '/debug-5cf050.log', $dbgLine . "\n", FILE_APPEND);
            // #endregion
        }

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Ocean HRMS · DB Sync</title>';
        echo '<style>
          body{font-family:Segoe UI,system-ui,sans-serif;background:#f6f4f8;color:#211F23;margin:0;padding:32px}
          .box{max-width:720px;margin:0 auto;background:#fff;border-radius:16px;padding:24px 28px;box-shadow:0 8px 24px rgba(0,0,0,.06);border:1px solid #efe8df}
          h1{margin:0 0 6px;font-size:22px} .sub{color:#777;margin:0 0 18px;font-size:14px}
          .ok{color:#15803d;font-weight:700} .err{color:#b91c1c;font-weight:700}
          ul{margin:0;padding:0;list-style:none} li{padding:8px 12px;border-bottom:1px solid #f0ebe3;font-size:13.5px;font-family:ui-monospace,Consolas,monospace}
          li:last-child{border:0} .badge{display:inline-block;background:#fff4e5;color:#c47800;font-weight:800;font-size:11px;padding:4px 10px;border-radius:999px;margin-bottom:12px}
        </style></head><body><div class="box">';
        echo '<div class="badge">Ocean HRMS · DB Sync</div>';
        echo '<h1>Database Sync</h1>';
        echo '<p class="sub">exit_date · designations (Flutter / BDM / BDE) · self-fill QR · org chart · letters · attendance (safe to re-run)</p>';

        if ($ok) {
            echo '<p class="ok">✓ Sync completed — live DB is up to date</p><ul>';
            foreach ($log as $line) {
                echo '<li>' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p class="err">✗ Sync failed</p>';
            echo '<p>' . htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') . '</p>';
        }

        echo '</div></body></html>';
        exit;
    }
}
