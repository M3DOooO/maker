<?php
include('includes/config.php');

$db_ok = true;

function normalize_int_digits($value)
{
    $value = trim((string)$value);
    $value = strtr($value, array('٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9'));
    return (int)$value;
}

$conn = mysql_connect("$host", "$user", "$pass");
if (!$conn) {
    $db_ok = false;
} elseif (!mysql_select_db("$db")) {
    $db_ok = false;
} else {
    mysql_set_charset('utf8mb4');
    mysql_query("SET NAMES utf8mb4");
}


if ($db_ok) {
    $create_sql = "CREATE TABLE IF NOT EXISTS qr_device_requests (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        device_id INT NOT NULL,
        device_name VARCHAR(255) NOT NULL,
        request_type VARCHAR(255) NOT NULL,
        qty INT NOT NULL DEFAULT 1,
        status VARCHAR(20) NOT NULL DEFAULT 'new',
        created_at DATETIME NOT NULL
    )";
    if (!mysql_query($create_sql)) {
        $db_ok = false;
    }
    if ($db_ok) {
        mysql_query("ALTER TABLE qr_device_requests CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    // مهم: CREATE TABLE IF NOT EXISTS لا يضيف الأعمدة في الجداول القديمة.
    // لذلك نتأكد أن عمود qty موجود حتى لا يفشل INSERT في البيئات القديمة.
    if ($db_ok) {
        $qty_col = mysql_query("SHOW COLUMNS FROM qr_device_requests LIKE 'qty'");
        if ($qty_col && mysql_num_rows($qty_col) == 0) {
            if (!mysql_query("ALTER TABLE qr_device_requests ADD COLUMN qty INT NOT NULL DEFAULT 1 AFTER request_type")) {
                $db_ok = false;
            }
        }
    }
}

$device_id = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$stock_items = array();
if ($db_ok) {
    $sres = mysql_query("SELECT name, (SUM(stock)-SUM(sold)) AS qty_left FROM stock WHERE name != '' GROUP BY name HAVING qty_left > 0 ORDER BY name ASC");
    if ($sres) {
        while ($sr = mysql_fetch_assoc($sres)) {
            $stock_items[] = $sr;
        }
    }
}


$device_name = 'Unknown Device';
$device_qr_pin = '';
$device_qr_mode = 'single';
$device_time_mode = 'time';
$error_message = '';
$success_message = '';
$provided_pin = isset($_REQUEST['pin']) ? trim($_REQUEST['pin']) : '';

if ($db_ok) {
    @mysql_query("ALTER TABLE devices ADD COLUMN qr_access_code VARCHAR(20) NOT NULL DEFAULT ''");
    @mysql_query("ALTER TABLE devices ADD COLUMN qr_order_mode VARCHAR(10) NOT NULL DEFAULT 'single'");
}

if ($db_ok && $device_id > 0) {
    $r = mysql_query("SELECT `Device Name`,`qr_access_code`,`qr_order_mode`,`timetype` FROM devices WHERE ID='".$device_id."' LIMIT 1");
    if ($r && ($row = mysql_fetch_assoc($r))) {
        $device_name = $row['Device Name'];
        $device_qr_pin = isset($row['qr_access_code']) ? trim($row['qr_access_code']) : '';
        $device_qr_mode = isset($row['qr_order_mode']) ? trim($row['qr_order_mode']) : 'single';
        $device_time_mode = isset($row['timetype']) ? trim($row['timetype']) : 'time';
    } else {
        $error_message = 'الجهاز غير موجود.';
    }
} elseif ($device_id <= 0) {
    $error_message = 'رابط غير صحيح، من فضلك اعمل Scan من QR الجهاز.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_type = isset($_POST['request_type']) ? trim($_POST['request_type']) : '';
    $posted_qty = isset($_POST['qty']) ? normalize_int_digits($_POST['qty']) : 1;

    if (!$db_ok) {
        $error_message = 'حصلت مشكلة في قاعدة البيانات. برجاء المحاولة مرة أخرى.';
    } elseif ($device_id <= 0 || $device_name == 'Unknown Device') {
        $error_message = 'لا يمكن إرسال الطلب بدون جهاز صحيح.';
    } elseif ($device_qr_pin === '' || $provided_pin !== $device_qr_pin) {
        $error_message = 'الكود السري غير صحيح. اطلب QR محدث من الكاشير.';
    } elseif ($posted_type == '') {
        $error_message = 'من فضلك اختار نوع الطلب.';
    } else {
        if ($posted_qty < 1) { $posted_qty = 1; }
        if ($posted_qty > 50) { $posted_qty = 50; }

        $device_state_res = mysql_query("SELECT `session_id`, `Device Status` FROM devices WHERE ID='".$device_id."' LIMIT 1");
        $device_state = $device_state_res ? mysql_fetch_assoc($device_state_res) : false;
        if (!$device_state || $device_state['Device Status'] != 'On' || (int)$device_state['session_id'] <= 0) {
            $error_message = 'الجهاز غير متاح حاليا. برجاء فتح وقت من الكاشير قبل بدء الطلب.';
        } else {
            $request_type = mysql_real_escape_string($posted_type);
            $safe_name = mysql_real_escape_string($device_name);
            if ($device_qr_mode === 'single') {
                $single_check = mysql_query("SELECT id FROM qr_device_requests WHERE device_id='".$device_id."' AND status='new' LIMIT 1");
                if ($single_check && mysql_num_rows($single_check) > 0) {
                    $error_message = 'الجهاز مضبوط على Single ويوجد طلب جديد بالفعل.';
                }
            }

            $insert = false;
            if ($error_message == '') {
                $insert = mysql_query("INSERT INTO qr_device_requests (device_id,device_name,request_type,qty,status,created_at) VALUES ('".$device_id."','$safe_name','$request_type','".$posted_qty."','new',NOW())");
            }
            if ($insert) {
                $stock_res = mysql_query("SELECT `catagory`,`sub_cat`,`price` FROM stock WHERE name='".$request_type."' LIMIT 1");
                $srow = $stock_res ? mysql_fetch_assoc($stock_res) : false;
                if ($srow) {
                    $cfg = mysql_query("SELECT `current_shift`,`shift_day`,`shift_month` FROM config LIMIT 1");
                    $c = $cfg ? mysql_fetch_assoc($cfg) : false;
                    $current_shift = $c ? $c['current_shift'] : '';
                    $shift_day = $c ? (int)$c['shift_day'] : (int)date('d');
                    $shift_month = $c ? (int)$c['shift_month'] : (int)date('m');
                    $year = (int)date('Y');
                    $hour = (int)date('H');
                    $unit_price = (float)$srow['price'];
                    $total = $unit_price * $posted_qty;
                    $cat = mysql_real_escape_string($srow['catagory']);
                    $sub_cat = mysql_real_escape_string($srow['sub_cat']);
                    $name = mysql_real_escape_string($posted_type);
                    $session_id = (int)$device_state['session_id'];
                    mysql_query("INSERT INTO `ps_orders` (`catagory`, `sub_cat`,`name`, `price`, `num` , `ps_id` ,`session_id`,`day`,`month`,`year`,`shift`,`hour` ) VALUES ('$cat', '$sub_cat', '$name','$total','$posted_qty','$device_id','$session_id','$shift_day','$shift_month','$year','$current_shift','$hour')");
                }
                header("Location: device_order_qr.php?device_id=".$device_id."&pin=".urlencode($provided_pin)."&done=1");
                exit;
            } else {
                $error_message = 'تعذر إرسال الطلب حاليا. حاول مرة أخرى. ('.mysql_error().')';
            }
        }
    }
}

if ($db_ok && $device_id > 0 && $device_qr_pin !== '' && $provided_pin === $device_qr_pin) {
    if (isset($_GET['switch_mode'])) {
        $next_mode = ($_GET['switch_mode'] === 'multi') ? 'multi' : 'single';
        $safe_name = mysql_real_escape_string($device_name);
        $mode_text = ($next_mode === 'multi') ? 'طلب تحويل من Single إلى Multi' : 'طلب تحويل من Multi إلى Single';
        $safe_mode_text = mysql_real_escape_string($mode_text);
        mysql_query("INSERT INTO qr_device_requests (device_id,device_name,request_type,qty,status,created_at) VALUES ('".$device_id."','$safe_name','$safe_mode_text','1','new',NOW())");
        header("Location: device_order_qr.php?device_id=".$device_id."&pin=".urlencode($provided_pin)."&done=1");
        exit;
    }
    if (isset($_GET['switch_time'])) {
        $next_time = ($_GET['switch_time'] === 'unlimited') ? 'unlimited' : 'time';
        $safe_name = mysql_real_escape_string($device_name);
        $time_text = ($next_time === 'unlimited') ? 'طلب تحويل الوقت إلى غير محدود' : 'طلب تحويل الوقت إلى محدود';
        $safe_time_text = mysql_real_escape_string($time_text);
        mysql_query("INSERT INTO qr_device_requests (device_id,device_name,request_type,qty,status,created_at) VALUES ('".$device_id."','$safe_name','$safe_time_text','1','new',NOW())");
        header("Location: device_order_qr.php?device_id=".$device_id."&pin=".urlencode($provided_pin)."&done=1");
        exit;
    }
}

?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>طلب خارجي</title>
<style>body{font-family:tahoma;padding:20px;direction:rtl}.box{max-width:380px;margin:auto;border:1px solid #ddd;padding:18px;border-radius:10px}button{width:100%;padding:10px;background:#2d89ef;color:#fff;border:0;border-radius:8px}.err{color:#b30000}.ok{color:green}</style>
</head><body><div class="box"><h3>جهاز: <?php echo htmlspecialchars($device_name);?></h3>
<p>اختار المنتج من المخزن:</p>
<form method="post" action="device_order_qr.php?device_id=<?php echo (int)$device_id; ?>&pin=<?php echo urlencode($provided_pin); ?>">
<select name="request_type" style="width:100%;padding:10px;margin-bottom:12px" required>
<option value="">-- اختار المنتج --</option>
<?php foreach($stock_items as $it){ ?>
<option value="<?php echo htmlspecialchars($it['name']);?>"><?php echo htmlspecialchars($it['name']);?></option>
<?php } ?>
</select>
<label style="display:block;margin-bottom:6px">الكمية المطلوبة:</label>
<input type="number" name="qty" min="1" max="50" value="1" style="width:120px;max-width:100%;padding:8px;margin-bottom:12px;box-sizing:border-box" placeholder="الكمية" required>
<button type="submit">إرسال الطلب</button>
</form>
<div style="margin-top:12px;border-top:1px solid #ddd;padding-top:10px">
<?php if ($device_qr_mode === 'single') { ?>
<a href="device_order_qr.php?device_id=<?php echo (int)$device_id; ?>&pin=<?php echo urlencode($provided_pin); ?>&switch_mode=multi" style="display:block;background:#444;color:#fff;text-align:center;padding:8px;border-radius:6px;text-decoration:none;margin-bottom:8px">تحويل إلى Multi</a>
<?php } else { ?>
<a href="device_order_qr.php?device_id=<?php echo (int)$device_id; ?>&pin=<?php echo urlencode($provided_pin); ?>&switch_mode=single" style="display:block;background:#444;color:#fff;text-align:center;padding:8px;border-radius:6px;text-decoration:none;margin-bottom:8px">تحويل إلى Single</a>
<?php } ?>

<?php if ($device_time_mode === 'time') { ?>
<a href="device_order_qr.php?device_id=<?php echo (int)$device_id; ?>&pin=<?php echo urlencode($provided_pin); ?>&switch_time=unlimited" style="display:block;background:#1d7f3f;color:#fff;text-align:center;padding:8px;border-radius:6px;text-decoration:none">تحويل الوقت إلى غير محدود</a>
<?php } else { ?>
<a href="device_order_qr.php?device_id=<?php echo (int)$device_id; ?>&pin=<?php echo urlencode($provided_pin); ?>&switch_time=time" style="display:block;background:#1d7f3f;color:#fff;text-align:center;padding:8px;border-radius:6px;text-decoration:none">تحويل الوقت إلى محدود</a>
<?php } ?>
</div>
<?php if($success_message!=''){ ?><p class="ok"><?php echo $success_message;?></p><?php } ?>
<?php if($error_message!=''){ ?><p class="err"><?php echo htmlspecialchars($error_message);?></p><?php } ?>
</div></body></html>
