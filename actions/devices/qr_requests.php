<?php
session_start();
if (!isset($_SESSION['ps_user'])) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => false, 'error' => 'unauthorized'), JSON_UNESCAPED_UNICODE);
    exit;
}
include('../../includes/config.php');
$conn = mysql_connect("$host", "$user", "$pass");
if (!$conn || !mysql_select_db("$db")) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => false, 'error' => 'db_connection'), JSON_UNESCAPED_UNICODE);
    exit;
}
mysql_set_charset('utf8mb4');
mysql_query("SET NAMES utf8mb4");
$create_ok = mysql_query("CREATE TABLE IF NOT EXISTS qr_device_requests (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    device_id INT NOT NULL,
    device_name VARCHAR(255) NOT NULL,
    request_type VARCHAR(255) NOT NULL,
    qty INT NOT NULL DEFAULT 1,
    status VARCHAR(20) NOT NULL DEFAULT 'new',
    created_at DATETIME NOT NULL
)");
mysql_query("ALTER TABLE qr_device_requests CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
if (!$create_ok) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => false, 'error' => 'db_table'), JSON_UNESCAPED_UNICODE);
    exit;
}
// توافق مع قواعد بيانات قديمة: أضف qty إذا كان الجدول موجود بدون هذا العمود.
$qty_col = mysql_query("SHOW COLUMNS FROM qr_device_requests LIKE 'qty'");
if ($qty_col && mysql_num_rows($qty_col) == 0) {
    mysql_query("ALTER TABLE qr_device_requests ADD COLUMN qty INT NOT NULL DEFAULT 1 AFTER request_type");
}
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'count') {
    $c = mysql_query("SELECT COUNT(*) AS c FROM qr_device_requests WHERE status='new'");
    $row = $c ? mysql_fetch_assoc($c) : array('c'=>0);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => true, 'count' => (int)$row['c']), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($action == 'close') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    mysql_query("UPDATE qr_device_requests SET status='closed' WHERE id='".$id."'");
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => true), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action == 'set_mode') {
    $device_id = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
    $mode = isset($_GET['mode']) && $_GET['mode'] === 'multi' ? 'multi' : 'single';
    @mysql_query("ALTER TABLE devices ADD COLUMN qr_order_mode VARCHAR(10) NOT NULL DEFAULT 'single'");
    mysql_query("UPDATE devices SET qr_order_mode='".$mode."' WHERE ID='".$device_id."'");
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => true), JSON_UNESCAPED_UNICODE);
    exit;
}
if ($action == 'set_status') {
    $device_id = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
    $status = (isset($_GET['status']) && $_GET['status'] === 'On') ? 'On' : 'finished';
    mysql_query("UPDATE devices SET `Device Status`='".$status."' WHERE ID='".$device_id."'");
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('ok' => true), JSON_UNESCAPED_UNICODE);
    exit;
}

$res = mysql_query("SELECT * FROM qr_device_requests WHERE status='new' ORDER BY id DESC LIMIT 20");
$out = array();
while($row = mysql_fetch_assoc($res)) { $out[] = $row; }
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(array('ok' => true, 'items' => $out), JSON_UNESCAPED_UNICODE);
