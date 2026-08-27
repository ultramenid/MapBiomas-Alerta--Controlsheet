<?php

// Alert table names. Defaults match the local schema; set DB_TABLE_ALERTS_TEST
// in the production .env if the alerts-test table has a different name there.
return [

    'test_table' => env('DB_TABLE_ALERTS_TEST', 'alerts_backup_terbaru'),

];
