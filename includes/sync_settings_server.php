<?php
/**
 * Server-side sync settings.
 *
 * Copy/edit this file only on the hosted online server. It receives the local
 * computer dump and imports it into the online database.
 */
if (!defined('SYNC_SERVER_DB_HOST')) { define('SYNC_SERVER_DB_HOST', getenv('DB_HOST') ?: 'localhost'); }
if (!defined('SYNC_SERVER_DB_USER')) { define('SYNC_SERVER_DB_USER', getenv('DB_USER') ?: 'online_db_user'); }
if (!defined('SYNC_SERVER_DB_PASS')) { define('SYNC_SERVER_DB_PASS', getenv('DB_PASS') ?: 'online_db_password'); }
if (!defined('SYNC_SERVER_DB_NAME')) { define('SYNC_SERVER_DB_NAME', getenv('DB_NAME') ?: 'ps_online'); }

if (!defined('SYNC_SERVER_API_KEY')) { define('SYNC_SERVER_API_KEY', getenv('SYNC_API_KEY') ?: 'change-this-secret-key'); }
$SYNC_SERVER_ALLOWED_BRANCHES = array_filter(array_map('trim', explode(',', getenv('SYNC_ALLOWED_BRANCHES') ?: 'main')));
if (!defined('SYNC_SERVER_IMPORT_COMMAND')) { define('SYNC_SERVER_IMPORT_COMMAND', getenv('MYSQL_COMMAND') ?: 'mysql'); }
if (!defined('SYNC_SERVER_MAX_UPLOAD_BYTES')) { define('SYNC_SERVER_MAX_UPLOAD_BYTES', (int) (getenv('SYNC_MAX_UPLOAD_BYTES') ?: 52428800)); }
