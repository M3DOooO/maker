<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['ps_user'])) {
    include('login.php');
    die();
}

include('includes/config.php');
if($lang == 'en'){
    include('languages/en.php');
} else if($lang == 'ar'){
    include('languages/ar.php');
}

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$now = $_SESSION['ps_user'];
$stmt = $conn->prepare("SELECT type FROM users WHERE Username = ?");
$stmt->bind_param("s", $now);
$stmt->execute();
$result = $stmt->get_result();
$usern = null;

while($row = $result->fetch_assoc()) {
    $usern = $row['type'];
}

if($usern != 1) {
    echo "<script>location='devices.php'</script>";
    die();
}

$session_id = isset($_GET['s']) ? trim($_GET['s']) : (isset($_GET['session']) ? trim($_GET['session']) : '');

$check_orders = 0;
$Items = 0;
$timing = 0;
$discount = 0;
$service = 0;
$tax = 0;
$discount_reason = '';
$cash_u = '';
$shift_check2 = '';
$y = '';
$m = '';
$d = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $lang_302;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $lang_1;?>">
    <meta name="author" content="Mohamed Gad">
    <?php include 'includes/css.php';?>
    <script type="text/javascript">
    function newPopup(url) {
        window.open(url, 'popUpWindow', 'height=300,width=300,left=10,top=10,resizable=no,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes');
    }
    </script>
</head>
<body>
    <?php include('includes/navbar.php');?>
    
    <div class="container-fluid">
        <div class="row-fluid">
            <?php include('includes/menu.php');?>
            
            <noscript>
                <div class="alert alert-block span10">
                    <h4 class="alert-heading">Warning!</h4>
                    <p>You need to have <a href="http://en.wikipedia.org/wiki/JavaScript" target="_blank">JavaScript</a> enabled to use this site.</p>
                </div>
            </noscript>
            
            <div id="content" class="span10">
                
                <h2><span class="btn-primary">&nbsp;&nbsp;<?php echo $lang_303;?>: <?php echo htmlspecialchars($session_id);?>&nbsp;&nbsp;</span></h2><br/>
                
                <div class="row-fluid sortable">
                    <div class="box span10">
                        <div class="box-content">
                            <table class="table table-striped table-bordered span6">
                                <thead>
                                    <tr><td colspan="6" align="center"><center><b><font color="blue"><?php echo $lang_78;?></font></b></center></td></tr>
                                    <tr>
                                        <th><?php echo $lang_304;?></th>
                                        <th><?php echo $lang_159;?></th>
                                        <th><?php echo $lang_160;?></th>
                                        <th><?php echo $lang_161;?></th>
                                        <th><?php echo $lang_162;?></th>
                                        <th><?php echo $lang_305;?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM `reports` WHERE session_id = ?");
                                    $stmt->bind_param("s", $session_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    if($result->num_rows == 0) {
                                        echo "<tr><td colspan='6' align='center'><font color='red'><b>لا توجد بيانات للعرض</b></font></td></tr>";
                                    }
                                    
                                    while($row = $result->fetch_assoc()) {
                                        $ps_id = $row['pc_id'];
                                        $service = !empty($row['service']) ? (float)$row['service'] : 0;
                                        $tax = !empty($row['tax']) ? (float)$row['tax'] : 0;
                                        $tom = $row['total'];
                                        $hr = floor($tom / 3600) % 24;
                                        $mr = floor($tom / 60) % 60;
                                        $sr = ($tom % 60);
                                        $shift_check = $row['shift'];
                                        
                                        $discount_val = 0;
                                        if(!empty($row['discount'])) $discount_val += (float)$row['discount'];
                                        if(!empty($row['discount2'])) $discount_val += (float)$row['discount2'];
                                        if(!empty($row['discount_amount'])) $discount_val += (float)$row['discount_amount'];
                                        $discount += $discount_val;
                                        
                                        $discount_reason = !empty($row['dis_reason']) ? $row['dis_reason'] : '-';
                                        $cash_u = $row['casheer'];
                                        $d = $row['day'];
                                        $m = $row['month'];
                                        $y = $row['year'];
                                        
                                        if($shift_check == 'One'){
                                            $shift_check2 = isset($lang_155) ? $lang_155 : 'الشيفت الأول';
                                        } else {
                                            $shift_check2 = isset($lang_156) ? $lang_156 : 'الشيفت الثاني';
                                        }
                                        
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td>";
                                        $thetype = $row['type'];
                                        switch($thetype) {
                                            case 'single': echo isset($lang_3) ? $lang_3 : 'أحادي'; break;
                                            case 'multi': echo isset($lang_4) ? $lang_4 : 'متعدد'; break;
                                            case 'multi5': echo isset($lang_5) ? $lang_5 : 'متعدد 5'; break;
                                            case 'multi6': echo isset($lang_6) ? $lang_6 : 'متعدد 6'; break;
                                            case 'multi7': echo isset($lang_7) ? $lang_7 : 'متعدد 7'; break;
                                        }
                                        
                                        echo "</td>";
                                        echo "<td>" . htmlspecialchars($row['Start_hour'] . ":" . $row['Start_minute']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['End_hour'] . ":" . $row['End_minute']) . "</td>";
                                        echo "<td>" . $hr . ":" . $mr . ":" . $sr . "</td>";
                                        echo "<td><font color='green'>" . htmlspecialchars($row['money']) . "</font> " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</td>";
                                        echo "</tr>";
                                    }
                                    $stmt->close();
                                    
                                    $stmt = $conn->prepare("SELECT SUM(money) as total_money FROM `reports` WHERE session_id = ?");
                                    $stmt->bind_param("s", $session_id);
                                    $stmt->execute();
                                    $resultb = $stmt->get_result();
                                    
                                    while($rowb = $resultb->fetch_assoc()) {
                                        $timing = $rowb['total_money'];
                                        $total = $timing - $discount;
                                    }
                                    $stmt->close();
                                    ?>
                                </tbody>
                            </table>
                            
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM `ps_orders` WHERE session_id = ?");
                            $stmt->bind_param("s", $session_id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $check_orders = $result->num_rows;
                            
                            if($check_orders > 0) {
                            ?>
                            <table class="table table-striped table-bordered span6">
                                <thead>
                                    <tr><td colspan="6" align="center"><center><b><font color="blue"><?php echo isset($lang_154) ? $lang_154 : 'الطلبات'; ?></font></b></center></td></tr>
                                    <tr>
                                        <th colspan="2"><?php echo isset($lang_49) ? $lang_49 : 'اسم الطلب'; ?></th>
                                        <th><?php echo isset($lang_306) ? $lang_306 : 'الفئة'; ?></th>
                                        <th><?php echo isset($lang_307) ? $lang_307 : 'العدد'; ?></th>
                                        <th colspan="2"><?php echo isset($lang_23) ? $lang_23 : 'السعر'; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td align='center' colspan='2'>" . htmlspecialchars($row['name']) . "</td>";
                                        echo "<td align='center'>" . htmlspecialchars($row['sub_cat']) . "</td>";
                                        echo "<td align='center'>" . htmlspecialchars($row['num']) . " " . (isset($lang_308) ? $lang_308 : 'عدد') . "</td>";
                                        echo "<td align='center' colspan='2'><font color='green'>" . htmlspecialchars($row['price']) . "</font> " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php } $stmt->close(); ?>
                            
                            <?php
                            $stmt = $conn->prepare("SELECT SUM(price) as total_price FROM ps_orders WHERE session_id = ?");
                            $stmt->bind_param("s", $session_id);
                            $stmt->execute();
                            $resulty = $stmt->get_result();
                            
                            while($row = $resulty->fetch_assoc()) {
                                $Items = (float)$row['total_price'];
                            }
                            $stmt->close();
                            ?>
                            
                            <table border="1" span="6" width="40%" style="margin-right:10px">
                                <tr><th align='center'><h3><?php echo isset($lang_77) ? $lang_77 : 'التاريخ'; ?></h3></th> <th colspan="2" align='center'><h3><font color="#008080"><?php echo htmlspecialchars($y . "/" . $m . "/" . $d);?></font></h3></th></tr>
                                <tr><th align='center'><h3><?php echo isset($lang_166) ? $lang_166 : 'الكاشير'; ?></h3></th> <th colspan="2" align='center'><h3><font color="#008080"><?php echo htmlspecialchars($cash_u);?></font></h3></th></tr>
                                <tr><th align='center'><h3><?php echo isset($lang_150) ? $lang_150 : 'الشيفت'; ?></h3></th> <th colspan="2" align='center'><h3><font color="#008080"><?php echo $shift_check2;?></font></h3></th></tr>
                                
                                <?php if($discount > 0){?>
                                <tr><td align='center'><h2><?php echo isset($lang_106) ? $lang_106 : 'الإجمالي'; ?></h2></td> <td align='center'><h2><font color="green"><?php echo $Items + $timing;?></font></h2></td><td align='center'><h2> <?php echo isset($lang_100) ? $lang_100 : 'ج.م'; ?></h2></td></tr>
                                <tr><td align='center'><h2><?php echo isset($lang_105) ? $lang_105 : 'الخصم'; ?></h2></td> <td align='center'><h2><font color="red"><?php echo $discount;?></font></h2></td><td align='center'><h2> <?php echo isset($lang_100) ? $lang_100 : 'ج.م'; ?></h2></td></tr>
                                <tr><td align='center'><h2><?php echo isset($lang_153) ? $lang_153 : 'سبب الخصم'; ?></h2></td> <td colspan="2" align='center'><h2><font color="orange"><?php echo htmlspecialchars($discount_reason);?></font></h2></td> </tr>
                                <?php }?>
                                
                                <?php if($tax > 0){?>
                                <tr><td align='center'><h3><font color="orange">الضريبة</font></h3></td> <td align='center'><h3><font color="green"><?php echo $tax;?></font></h3></td><td align='center'><h3> <?php echo isset($lang_100) ? $lang_100 : 'ج.م'; ?></h3></td></tr>
                                <?php }?>
                                
                                <?php if($service > 0){?>
                                <tr><td align='center'><h3><font color="orange">الخدمة</font></h3></td> <td align='center'><h3><font color="green"><?php echo $service;?></font></h3></td><td align='center'><h3> <?php echo isset($lang_100) ? $lang_100 : 'ج.م'; ?></h3></td></tr>
                                <?php }?>
                                
                                <tr><td align='center'><h2><?php echo isset($lang_309) ? $lang_309 : 'الإجمالي النهائي'; ?></h2></td> <td align='center'><h1><font color="green"><?php echo $Items + $timing - $discount + $service + $tax;?></font></h1></td><td align='center'><h2> <?php echo isset($lang_100) ? $lang_100 : 'ج.م'; ?></h2></td></tr>
                            </table>
                        </div>
                        
                        <br/><br/>
                        <?php if(!empty($ps_id)) { ?>
                        <a class="btn btn-primary pull-right" href="JavaScript:newPopup('actions/print/ps.php?Session=<?php echo urlencode($session_id); ?>&id=<?php echo urlencode($ps_id); ?>')"><span class="icon32 icon-print"></span><?php echo isset($lang_310) ? $lang_310 : 'طباعة'; ?></a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <footer>
            <p class="pull-left">&copy; <a href="http://www.psxegy.com" target="_blank">Gesture For Playstation</a> <?php echo idate('Y');?></p>
        </footer>
    </div>
    
    <?php include 'includes/js.php';?>
</body>
</html>