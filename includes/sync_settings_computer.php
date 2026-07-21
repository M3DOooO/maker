<?php
/**
 * Computer-side sync settings.
 *
 * Copy/edit this file only on the local shop computer. Do not upload it as the
 * active server settings file.
 */
define('SYNC_COMPUTER_DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('SYNC_COMPUTER_DB_USER', getenv('DB_USER') ?: 'root');
define('SYNC_COMPUTER_DB_PASS', getenv('DB_PASS') ?: '');
define('SYNC_COMPUTER_DB_NAME', getenv('DB_NAME') ?: 'ps_local');

define('SYNC_COMPUTER_BRANCH_ID', getenv('BRANCH_ID') ?: 'main');
define('SYNC_COMPUTER_DEVICE_ID', getenv('DEVICE_ID') ?: 'cashier-1');
define('SYNC_COMPUTER_API_KEY', getenv('SYNC_API_KEY') ?: 'change-this-secret-key');
define('SYNC_COMPUTER_SERVER_URL', getenv('SYNC_SERVER_URL') ?: 'https://example.com/ps/api/sync_receive_server.php');

define('SYNC_COMPUTER_DUMP_COMMAND', getenv('MYSQLDUMP_COMMAND') ?: 'mysqldump');
define('SYNC_COMPUTER_CONNECT_TIMEOUT', (int) (getenv('SYNC_TIMEOUT_SECONDS') ?: 20));
