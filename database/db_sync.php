<?php

/**
 * CLI: php database/db_sync.php
 * Web: /db-sync?token=ocean-hrms-db-sync
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\DbSync;

foreach (DbSync::run() as $line) {
    echo $line . PHP_EOL;
}
