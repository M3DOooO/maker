<?php
// QR page bootstrap fixes:
if (!isset($mac)) {
    $mac = '';
}

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$device_id = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$pin = isset($_REQUEST['pin']) ? trim($_REQUEST['pin']) : '';
$done = isset($_GET['done']) ? (int)$_GET['done'] : 0;

if ($done === 1) {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>تم الإرسال</title>
</head>
<body style="background:#fff;font-family:tahoma;direction:rtl;padding:40px;text-align:center">
    <h2 style="color:#177d2f">✅ تم إرسال الطلب بنجاح</h2>
    <p>شكراً لك، تم استلام الطلب.</p>
</body>
</html>
<?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $pin === '') {
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>دخول الطلبات</title>
    <style>
        body{font-family:tahoma;direction:rtl;padding:20px;background:#f6f8fb}
        .box{max-width:360px;margin:40px auto;border:1px solid #ddd;background:#fff;padding:18px;border-radius:10px}
        input,button{width:100%;padding:11px;box-sizing:border-box}
        input{margin:10px 0;border:1px solid #ccc;border-radius:8px}
        button{border:0;border-radius:8px;background:#2d89ef;color:#fff}
    </style>
</head>
<body>
    <div class="box">
        <h3 style="margin-top:0">ادخل الرقم السري للطلبات</h3>
        <form method="get" action="device_order_qr.php">
            <input type="hidden" name="device_id" value="<?php echo (int)$device_id; ?>">
            <input type="text" name="pin" placeholder="اكتب الرقم السري" required autofocus>
            <button type="submit">دخول</button>
        </form>
    </div>
</body>
</html>
<?php
    exit;
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        http_response_code(500);
        echo '<h2>Fatal error in device_order_qr.php</h2>';
        echo '<pre>' . htmlspecialchars(print_r($error, true), ENT_QUOTES, 'UTF-8') . '</pre>';
    }
});

try {
    include 'device_order_form.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h2>Exception in device_order_qr.php</h2>';
    echo '<pre>' . htmlspecialchars($e->__toString(), ENT_QUOTES, 'UTF-8') . '</pre>';
}
