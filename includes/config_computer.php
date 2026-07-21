<?php
/**
 * Local computer configuration.
 *
 * Use this file on the cashier / shop computer when the site runs locally from
 * http://localhost/ps. The local MySQL database is the source of truth while the
 * internet is down. A sync job can later push new rows/actions to the online
 * server defined in SYNC_SERVER_URL.
 *
 * To activate it, copy this file over includes/config.php on the local computer
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
 * Device identity used by the future sync layer.
 * Keep BRANCH_ID the same for one branch and make DEVICE_ID unique per cashier PC.
 */
if (!defined('APP_RUN_MODE')) { define('APP_RUN_MODE', 'computer'); }
if (!defined('BRANCH_ID')) { define('BRANCH_ID', getenv('BRANCH_ID') ?: 'main'); }
if (!defined('DEVICE_ID')) { define('DEVICE_ID', getenv('DEVICE_ID') ?: 'cashier-1'); }

/*
 * Local database settings.
 * The local computer should point to localhost so the site keeps working without
 * internet. Override these values with environment variables if needed.
 */
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db = getenv('DB_NAME') ?: 'ps_local';

/*
 * Online sync endpoint.
 * This is not used by the current old pages directly; it is prepared for the
 * upcoming sync worker/script that will push local changes when internet returns.
 */
if (!defined('SYNC_ENABLED')) { define('SYNC_ENABLED', (getenv('SYNC_ENABLED') ?: '1') === '1'); }
if (!defined('SYNC_SERVER_URL')) { define('SYNC_SERVER_URL', getenv('SYNC_SERVER_URL') ?: 'https://example.com/ps/api/sync_receive_server.php'); }
if (!defined('SYNC_API_KEY')) { define('SYNC_API_KEY', getenv('SYNC_API_KEY') ?: 'change-this-secret-key'); }
if (!defined('SYNC_BATCH_SIZE')) { define('SYNC_BATCH_SIZE', (int) (getenv('SYNC_BATCH_SIZE') ?: 50)); }
if (!defined('SYNC_TIMEOUT_SECONDS')) { define('SYNC_TIMEOUT_SECONDS', (int) (getenv('SYNC_TIMEOUT_SECONDS') ?: 15)); }

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
