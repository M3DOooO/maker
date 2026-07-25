<?php session_start();
if( !isset($_SESSION['ps_user']) )
{
	include('login.php');
	die();
}
include('includes/config.php');
if($lang == 'en'){include('languages/en.php');}else if($lang == 'ar'){include('languages/ar.php');}
mysql_connect("$host", "$user", "$pass")or die("cannot connect");
mysql_select_db("$db")or die("cannot select DB");

$cashier_name = $_SESSION['ps_user'];
$cashier_sql = mysql_real_escape_string($cashier_name);
$today = $shift_day;
$this_month = $shift_month;
$Year = idate('Y');

function cashier_sum_value($query)
{
	$result = mysql_query($query) or die(mysql_error());
	$row = mysql_fetch_array($result);
	return isset($row[0]) ? (float)$row[0] : 0;
}

$devices_total = cashier_sum_value("SELECT SUM(money) FROM reports WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND status = 'done' AND casheer = '$cashier_sql'");
$device_discount_percent = cashier_sum_value("SELECT SUM(discount2) FROM reports WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND status = 'done' AND casheer = '$cashier_sql'");
$device_discount_amount = cashier_sum_value("SELECT SUM(discount_amount) FROM reports WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND status = 'done' AND casheer = '$cashier_sql'");
$orders_discount = cashier_sum_value("SELECT SUM(discount2) FROM reports2 WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND status = 'done' AND casheer = '$cashier_sql'");
$ps_orders_total = cashier_sum_value("SELECT SUM(price) FROM ps_orders INNER JOIN (SELECT DISTINCT session_id FROM reports WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND status = 'done' AND casheer = '$cashier_sql') AS shift_sessions ON ps_orders.session_id = shift_sessions.session_id WHERE ps_orders.status = 'yes' AND ps_orders.casheer = '$cashier_sql'");
$orders_total = cashier_sum_value("SELECT SUM(price) FROM reports2 WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND notes = 'order' AND status = 'done' AND casheer = '$cashier_sql'");
$expenses_total = cashier_sum_value("SELECT SUM(price) FROM reports2 WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND catagory = 'exp' AND status = 'done' AND casheer = '$cashier_sql'");
$income_total = cashier_sum_value("SELECT SUM(price) FROM reports2 WHERE day = '$today' AND month = '$this_month' AND year = '$Year' AND catagory = 'in' AND status = 'done' AND casheer = '$cashier_sql'");
$total_discounts = $device_discount_percent + $device_discount_amount + $orders_discount;
$daily_total = $devices_total + $ps_orders_total + $orders_total + $income_total - $expenses_total - $total_discounts;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>تقرير الكاشير</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo $lang_1;?>">
	<meta name="author" content="Mohamed Gad">
	<link id="bs-css" href="css/bootstrap-cerulean.css" rel="stylesheet">
	<style type="text/css">
	  body { padding-bottom: 40px; }
	  .sidebar-nav { padding: 9px 0; }
	  .cashier-report-card { max-width: 520px; margin: 40px auto; text-align: center; }
	  .cashier-report-card h1 { margin: 20px 0; }
	  .cashier-report-total { font-size: 34px; line-height: 46px; color: #468847; font-weight: bold; }
	</style>
	<link href="css/bootstrap-responsive.css" rel="stylesheet">
	<link href="css/charisma-app.css" rel="stylesheet">
	<link rel="shortcut icon" href="img/favicon.ico">
</head>
<body>
<?php include('includes/navbar.php');?>
<div class="container-fluid">
	<div class="row-fluid">
		<?php include('includes/menu.php');?>
		<noscript>
			<div class="alert alert-block span10">
				<h4 class="alert-heading">Warning!</h4>
				<p>You need JavaScript enabled to use this site.</p>
			</div>
		</noscript>
		<div id="content" class="span10">
			<div style="border-style:solid;border-width:1px;padding:15px;margin-right: 50px;">
				<a href="devices.php"><span><?php echo $lang_120;?></span></a> / <span>تقرير الكاشير</span>
			</div>
			<div class="well cashier-report-card">
				<h2>اسم الكاشير</h2>
				<h1><?php echo htmlspecialchars($cashier_name, ENT_QUOTES, 'UTF-8'); ?></h1>
				<p>إجمالي الحساب طول اليوم للكاشير الحالي فقط</p>
				<div class="cashier-report-total"><?php echo $daily_total; ?> <?php echo $lang_100;?></div>
				<p><?php echo $Year;?>/<?php echo $this_month;?>/<?php echo $today;?></p>
			</div>
		</div>
	</div>
	<hr>
	<footer>
		<p class="pull-left">&copy; <a href="http://www.psxegy.com" target="_blank">Gesture For Playstation</a> <?php echo idate('Y');?></p>
	</footer>
</div>
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/bootstrap-transition.js"></script>
<script src="js/bootstrap-alert.js"></script>
<script src="js/bootstrap-modal.js"></script>
<script src="js/bootstrap-dropdown.js"></script>
</body>
</html>
