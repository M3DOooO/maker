<?php
/**
 * Run this file on the local computer from Task Scheduler / cron.
 * It sends a compressed MySQL dump to the online server when internet is back.
 */
require_once __DIR__ . '/../includes/sync_settings_computer.php';

function fail($message, $code = 1)
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function command_exists($command)
{
    $where = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where' : 'command -v';
    $result = shell_exec($where . ' ' . escapeshellarg($command) . ' 2>NUL 2>/dev/null');
    return !empty($result);
}

if (!function_exists('curl_init')) {
    fail('PHP cURL extension is required.');
}

if (!command_exists(SYNC_COMPUTER_DUMP_COMMAND)) {
    fail('mysqldump was not found. Add MySQL/MariaDB bin folder to PATH.');
}

$tmp = tempnam(sys_get_temp_dir(), 'ps_sync_');
if ($tmp === false) {
    fail('Could not create a temporary dump file.');
}

$dumpCommand = sprintf(
    '%s --single-transaction --quick --skip-lock-tables --default-character-set=utf8mb4 -h%s -u%s %s %s > %s',
    escapeshellcmd(SYNC_COMPUTER_DUMP_COMMAND),
    escapeshellarg(SYNC_COMPUTER_DB_HOST),
    escapeshellarg(SYNC_COMPUTER_DB_USER),
    SYNC_COMPUTER_DB_PASS === '' ? '' : '-p' . escapeshellarg(SYNC_COMPUTER_DB_PASS),
    escapeshellarg(SYNC_COMPUTER_DB_NAME),
    escapeshellarg($tmp)
);

exec($dumpCommand, $output, $exitCode);
if ($exitCode !== 0 || !is_file($tmp) || filesize($tmp) === 0) {
    @unlink($tmp);
    fail('Database dump failed.');
}

$payload = array(
    'api_key' => SYNC_COMPUTER_API_KEY,
    'branch_id' => SYNC_COMPUTER_BRANCH_ID,
    'device_id' => SYNC_COMPUTER_DEVICE_ID,
    'generated_at' => gmdate('c'),
    'database' => SYNC_COMPUTER_DB_NAME,
    'dump_gz_b64' => base64_encode(gzencode(file_get_contents($tmp), 6)),
);
@unlink($tmp);

$ch = curl_init(SYNC_COMPUTER_SERVER_URL);
curl_setopt_array($ch, array(
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => SYNC_COMPUTER_CONNECT_TIMEOUT,
    CURLOPT_TIMEOUT => SYNC_COMPUTER_CONNECT_TIMEOUT * 3,
));
$response = curl_exec($ch);
$error = curl_error($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $status < 200 || $status >= 300) {
    fail('Sync upload failed: HTTP ' . $status . ' ' . $error . ' ' . $response);
}

echo 'Sync uploaded successfully at ' . date('Y-m-d H:i:s') . PHP_EOL;
