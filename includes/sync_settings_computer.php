<?php
/**
 * Computer-side sync settings.
 *
 * Copy/edit this file only on the local shop computer. Do not upload it as the
 * active server settings file.
 */
if (!defined('SYNC_COMPUTER_DB_HOST')) { define('SYNC_COMPUTER_DB_HOST', getenv('DB_HOST') ?: 'localhost'); }
if (!defined('SYNC_COMPUTER_DB_USER')) { define('SYNC_COMPUTER_DB_USER', getenv('DB_USER') ?: 'root'); }
if (!defined('SYNC_COMPUTER_DB_PASS')) { define('SYNC_COMPUTER_DB_PASS', getenv('DB_PASS') ?: ''); }
if (!defined('SYNC_COMPUTER_DB_NAME')) { define('SYNC_COMPUTER_DB_NAME', getenv('DB_NAME') ?: 'ps_local'); }

if (!defined('SYNC_COMPUTER_BRANCH_ID')) { define('SYNC_COMPUTER_BRANCH_ID', getenv('BRANCH_ID') ?: 'main'); }
if (!defined('SYNC_COMPUTER_DEVICE_ID')) { define('SYNC_COMPUTER_DEVICE_ID', getenv('DEVICE_ID') ?: 'cashier-1'); }
if (!defined('SYNC_COMPUTER_API_KEY')) { define('SYNC_COMPUTER_API_KEY', getenv('SYNC_API_KEY') ?: 'change-this-secret-key'); }
if (!defined('SYNC_COMPUTER_SERVER_URL')) { define('SYNC_COMPUTER_SERVER_URL', getenv('SYNC_SERVER_URL') ?: 'https://example.com/ps/api/sync_receive_server.php'); }

if (!defined('SYNC_COMPUTER_DUMP_COMMAND')) { define('SYNC_COMPUTER_DUMP_COMMAND', getenv('MYSQLDUMP_COMMAND') ?: 'mysqldump'); }
if (!defined('SYNC_COMPUTER_CONNECT_TIMEOUT')) { define('SYNC_COMPUTER_CONNECT_TIMEOUT', (int) (getenv('SYNC_TIMEOUT_SECONDS') ?: 20)); }
