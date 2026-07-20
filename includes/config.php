<?php
require_once __DIR__ . '/mysql_compat.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($mac)) {
    $mac = '';
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'psxeqwgl_tooth';
$pass = getenv('DB_PASS') ?: 'Midos2010@';
$db = getenv('DB_NAME') ?: 'psxeqwgl_teeth';

date_default_timezone_set('Africa/Cairo');
$script_tz = date_default_timezone_get();
if (strcmp($script_tz, ini_get('date.timezone'))){} else {}

$Month = idate('m');
$Day = idate('d');

	mysql_connect("$host", "$user", "$pass") or die(mysql_error()); 
	mysql_select_db("$db") or die(mysql_error()); 
	$mysql_tz_offset = date('P');
	mysql_query("SET time_zone = '" . $mysql_tz_offset . "'") or die(mysql_error());
	$sql="SELECT * FROM config";
	$result=mysql_query($sql);
	while($row = mysql_fetch_array($result))
		{
	     $current_shift = $row['current_shift'];
	     $last_shift = $row['last_shift'];
	     $shift_day = $row['shift_day'];
	     $shift_month = $row['shift_month'];
         $labx = $row['lic'];
         $lang = $row['lang'];
         $printornot = $row['print'];
         $service_ch = $row['service_ch'];
         $tax_ch = $row['tax_ch'];
         $service = $row['service'];
         $tax = $row['tax'];
		 $min_time = $row['min_time'];
		 $ta7akom = $row['control'];
	     $mx = md5($mac);
		}
if($current_shift == 'No')
{
	mysql_query("UPDATE `config` set `shift_day` = '$Day';");
	mysql_query("UPDATE `config` set `shift_month` = '$Month';");
}
$Month = $shift_month;
$Day = $shift_day;
$casheer = isset($_SESSION['ps_user']) ? $_SESSION['ps_user'] : '';
?>
