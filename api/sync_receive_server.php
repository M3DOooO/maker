<?php
/**
 * Upload this file to the online server as /api/sync_receive_server.php.
 * It replaces the online database with the latest local-computer dump.
 */
require_once __DIR__ . '/../includes/sync_settings_server.php';

header('Content-Type: application/json; charset=utf-8');

function respond($status, $data)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, array('ok' => false, 'error' => 'POST only'));
}

$raw = file_get_contents('php://input');
if ($raw === false || strlen($raw) > SYNC_SERVER_MAX_UPLOAD_BYTES) {
    respond(413, array('ok' => false, 'error' => 'Upload is too large'));
}

$data = json_decode($raw, true);
if (!is_array($data)) {
    respond(400, array('ok' => false, 'error' => 'Invalid JSON'));
}

if (!hash_equals(SYNC_SERVER_API_KEY, isset($data['api_key']) ? $data['api_key'] : '')) {
    respond(403, array('ok' => false, 'error' => 'Invalid API key'));
}

$branchId = isset($data['branch_id']) ? $data['branch_id'] : '';
if (!in_array($branchId, $SYNC_SERVER_ALLOWED_BRANCHES, true)) {
    respond(403, array('ok' => false, 'error' => 'Branch is not allowed'));
}

$dump = isset($data['dump_gz_b64']) ? base64_decode($data['dump_gz_b64'], true) : false;
if ($dump === false) {
    respond(400, array('ok' => false, 'error' => 'Invalid dump encoding'));
}

$sql = gzdecode($dump);
if ($sql === false || trim($sql) === '') {
    respond(400, array('ok' => false, 'error' => 'Invalid compressed dump'));
}

$tmp = tempnam(sys_get_temp_dir(), 'ps_import_');
if ($tmp === false || file_put_contents($tmp, $sql) === false) {
    respond(500, array('ok' => false, 'error' => 'Could not write temporary import file'));
}

$command = sprintf(
    '%s -h%s -u%s %s %s < %s',
    escapeshellcmd(SYNC_SERVER_IMPORT_COMMAND),
    escapeshellarg(SYNC_SERVER_DB_HOST),
    escapeshellarg(SYNC_SERVER_DB_USER),
    SYNC_SERVER_DB_PASS === '' ? '' : '-p' . escapeshellarg(SYNC_SERVER_DB_PASS),
    escapeshellarg(SYNC_SERVER_DB_NAME),
    escapeshellarg($tmp)
);

exec($command, $output, $exitCode);
@unlink($tmp);

if ($exitCode !== 0) {
    respond(500, array('ok' => false, 'error' => 'Database import failed'));
}

respond(200, array(
    'ok' => true,
    'branch_id' => $branchId,
    'device_id' => isset($data['device_id']) ? $data['device_id'] : '',
    'synced_at' => gmdate('c'),
));
