<?php

/**
 * Sync Ocean Infotech departments + designations (11 roles).
 * Run: php database/update_it_masters.php
 * Or open: /db-sync?token=ocean-hrms-db-sync
 */

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Services\OceanOrgMasters;

foreach (OceanOrgMasters::sync() as $line) {
    echo $line . PHP_EOL;
}
echo "Ocean org masters synced.\n";
