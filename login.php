<?php
if (function_exists('session_status')) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
} elseif (!isset($_SESSION)) {
    session_start();
}

include('includes/config.php');
if($lang == 'en'){include('languages/en.php');}else if($lang == 'ar'){include('languages/ar.php');}$id = $_GET['id'];     ob_start();  
		system('ipconfig /all');  
		$mycom=ob_get_contents(); 
		ob_clean();  
		$findme = "Physical";
		$pmac = strpos($mycom, $findme); 
		$mac=substr($mycom,($pmac+36),17);  
		mysql_connect("$host", "$user", "$pass") or die(mysql_error()); 
		mysql_select_db("$db") or die(mysql_error()); 
		$sql="SELECT * FROM config";
		$result=mysql_query($sql);
 		while($row = mysql_fetch_array($result))
		{
		$logo = $row['logo'];
		$store = $row['store'];
		$phone = $row['phone'];
		$facebook = $row['facebook'];
		$lab = $row['lic'];
		$m = md5($mac);
 		}
	$lang_lo = $_GET['lo'];
if(isset($lang_lo))
{
	if($lang_lo == 'ar')
	{
	mysql_connect("$host", "$user", "$pass") or die(mysql_error()); 
	mysql_select_db("$db") or die(mysql_error()); 
	mysql_query("UPDATE `config` set `lang` = 'ar';"); 
	echo "<script>location='login.php'</script>";
	}
	else if($lang_lo == 'en')
	{
	mysql_connect("$host", "$user", "$pass") or die(mysql_error()); 
	mysql_select_db("$db") or die(mysql_error()); 
	mysql_query("UPDATE `config` set `lang` = 'en';"); 		
	echo "<script>location='login.php'</script>";
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Gesture for Playstation</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php echo $lang_1;?>">
	<meta name="author" content="Mohamed Gad">
	<?php  include 'includes/css.php';?>
	<style>
	input.simple-input {
    width: 86%;
    height: 30px; /* تقدر تغير الرقم ده براحتك دلوقتي هيستجيب فوراً */
    border: 1px solid #5f79b8;
    border-radius: 10px;
    padding: 0 14px;
    box-sizing: border-box;
    background: #0f1e45;
    color: #fff;
    font-size: 16px;
}
.simple-btn {
    min-width: 150px; /* ده اللي هيوحد حجم الزرار في اللغتين */
    height: 44px;
    padding: 0 18px;
    border: 0;
    border-radius: 10px;
    background: linear-gradient(135deg, #2d89ef, #56b0ff);
    color: #fff;
    font-size: 16px;
    font-weight: bold;
}
		body{background:linear-gradient(140deg,#04070f 0%,#0a1d49 45%,#1668d2 100%);}
		.simple-login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
		.simple-login-card{width:100%;max-width:390px;background:rgba(9,15,34,.90);border:1px solid rgba(117,165,255,.35);border-radius:12px;padding:34px 20px;box-shadow:0 16px 34px rgba(0,0,0,.45)}
		.simple-title{text-align:center;margin:0 0 8px;font-size:28px;line-height:1.5;color:#eaf2ff}
		.simple-sub{text-align:center;color:#b8c7ea;margin-bottom:16px}
		.simple-input{width:86%;height:56px;border:1px solid #5f79b8;border-radius:10px;padding:0 14px;box-sizing:border-box;background:#0f1e45;color:#fff}
		.simple-row{margin-bottom:14px;display:flex;justify-content:center}
		.simple-actions{display:flex;align-items:center;justify-content:space-between;margin-top:12px;color:#d5e1ff}
		.simple-btn{height:44px;padding:0 18px;border:0;border-radius:10px;background:linear-gradient(135deg,#2d89ef,#56b0ff);color:#fff}
		.simple-input::placeholder{color:#c7d6ff}
		.simple-lang a img{margin-left:6px}
	</style>
</head>
<body>
<div class="simple-login-wrap">
	<div class="simple-login-card">
		<h2 class="simple-title"><?php echo $lang_169;?> <font size="6" face="Arial Unicode MS" color="#b7b7b7">Ges</font><font  face="Arial Unicode MS" size="6" color="#33b5e5">ture</font></h2>
		<div class="simple-sub"><?php echo $lang_168;?></div>
		<?php $error = $_GET['error']; if(isset($error)) { ?>
			<div class="alert alert-error"><?php echo $lang_185;?></div>
		<?php } else { ?>
			<div class="alert alert-info"><?php echo $lang_186;?></div>
		<?php } ?>
		<form action="actions/login/login_process.php" method="post">
			<div class="simple-row"><input autofocus class="simple-input" name="ps_user" id="username" type="text" placeholder="<?php echo $lang_88;?>" /></div>
			<div class="simple-row"><input class="simple-input" name="ps_pass" id="password" type="password" placeholder="<?php echo $lang_89;?>" /></div>
			<div class="simple-actions">
				<label><input type="checkbox" id="remember" /> <?php echo $lang_188;?></label>
				<button type="submit" class="simple-btn"><?php echo $lang_187;?></button>
			</div>
		</form>
		<div class="simple-actions" style="margin-top:14px">
			<div class="simple-lang">
				<a href="login.php?lo=ar" data-rel="tooltip" title="<?php echo $lang_358;?>"><img src="img/app/login/eg.ico" width="25" height="25" /></a>
				<a href="login.php?lo=en" data-rel="tooltip" title="<?php echo $lang_359;?>"><img src="img/app/login/us.ico" width="25" height="25" /></a>
			</div>
			<img src="img/app/defaults/logo20.png" style="height:44px" />
		</div>
	</div>
</div>
<?php  include 'includes/js.php';?>
</body>
</html>
