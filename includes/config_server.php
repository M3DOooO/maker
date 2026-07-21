<?php
/**
 * Online server configuration.
 *
 * Use this file on the hosted/online server. The online server receives synced
 * actions from the local computer and stores them in the online MySQL database.
 *
 * To activate it, copy this file over includes/config.php on the online server
 * after taking a backup of the current config.php.
 */
require_once __DIR__ . '/mysql_compat.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($mac)) {
    $mac = '';
}

/*
 * Server identity used by the future sync receive API.
 */
define('APP_RUN_MODE', 'server');
define('SERVER_ID', getenv('SERVER_ID') ?: 'online-main');

/*
 * Online database settings.
 * Fill these values with the hosting database details, or set them as
 * environment variables on the server.
 */
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'online_db_user';
$pass = getenv('DB_PASS') ?: 'online_db_password';
$db = getenv('DB_NAME') ?: 'ps_online';

/*
 * Sync receiver protection.
 * The local computer must use the same SYNC_API_KEY when pushing data.
 */
define('SYNC_RECEIVE_ENABLED', (getenv('SYNC_RECEIVE_ENABLED') ?: '1') === '1');
define('SYNC_API_KEY', getenv('SYNC_API_KEY') ?: 'change-this-secret-key');
define('SYNC_ALLOWED_BRANCHES', getenv('SYNC_ALLOWED_BRANCHES') ?: 'main');
define('SYNC_MAX_BATCH_SIZE', (int) (getenv('SYNC_MAX_BATCH_SIZE') ?: 100));

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
