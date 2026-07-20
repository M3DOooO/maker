<?php
session_start();
if (!isset($_SESSION['ps_user'])) { include('login.php'); die(); }
include('includes/config.php');

mysql_connect("$host", "$user", "$pass") or die('DB Connection Error');
mysql_select_db("$db") or die('DB Select Error');

$selected_device_id = isset($_GET['device_id']) ? (int)$_GET['device_id'] : 0;
$devices = array();
@mysql_query("ALTER TABLE devices ADD COLUMN qr_access_code VARCHAR(20) NOT NULL DEFAULT ''");
$res = mysql_query("SELECT ID, `Device Name`, qr_access_code FROM devices ORDER BY orderby");
while ($row = mysql_fetch_assoc($res)) { $devices[] = $row; }

if ($selected_device_id <= 0 && count($devices) > 0) {
    $selected_device_id = (int)$devices[0]['ID'];
}

$selected_device_name = '';
$selected_qr_code = ''; 
foreach ($devices as $d) {
    if ((int)$d['ID'] === $selected_device_id) {
        $selected_device_name = $d['Device Name'];
        $selected_qr_code = isset($d['qr_access_code']) ? $d['qr_access_code'] : '';
        break;
    }
}
?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>QR الأجهزة</title>
<style>
body{font-family:tahoma;direction:rtl;padding:15px}
.wrap{max-width:700px;margin:0 auto}
.sel{margin:15px 0;padding:10px;border:1px solid #ddd;border-radius:8px;background:#fafafa}
.card{width:420px;border:1px solid #ddd;border-radius:8px;padding:16px;margin:10px auto;text-align:center;background:#fff}
select,button{padding:8px;font-size:14px}
.print-btn{display:inline-block;margin-top:12px;background:#2d89ef;color:#fff;padding:8px 14px;border-radius:6px;text-decoration:none}
@media print{
  .sel,h2,.print-btn{display:none}
  body{padding:0}
  .card{border:0;box-shadow:none;margin:0 auto}
}
</style>
</head>
<body>
<div class="wrap">
<h2>QR للأجهزة</h2>
<form method="get" class="sel">
<label>اختار الروم / الجهاز:</label><br><br>
<select name="device_id" onchange="this.form.submit()">
<?php foreach($devices as $d){ ?>
<option value="<?php echo (int)$d['ID'];?>" <?php if((int)$d['ID']===$selected_device_id){echo 'selected';}?>><?php echo htmlspecialchars($d['Device Name']);?></option>
<?php } ?>
</select>
<noscript><button type="submit">عرض</button></noscript>
</form>

<?php if($selected_device_id > 0 && $selected_device_name != ''){
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$order_url = $scheme.'://'.$host.$base_path.'/device_order_qr.php?device_id='.(int)$selected_device_id.'&pin='.urlencode($selected_qr_code);
$qr='https://quickchart.io/qr?size=260&text='.urlencode($order_url);
?>
<div class="card">
    <b><?php echo htmlspecialchars($selected_device_name);?></b><br><br>
    <img src="<?php echo $qr;?>" width="360" height="360" alt="QR"><br>
    <a href="#" class="print-btn" onclick="window.print();return false;">طباعة الصورة</a>
</div>
<?php } else { ?>
<p>لا يوجد أجهزة متاحة.</p>
<?php } ?>
</div>
</body>
</html>
