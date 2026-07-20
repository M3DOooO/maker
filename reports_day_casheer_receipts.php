<?php 
session_start();
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
$sql = "SELECT * FROM users WHERE Username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $now);
$stmt->execute();
$result = $stmt->get_result();

$usern = null;
while($row = $result->fetch_assoc()) {
    $usern = $row['type'];
}
$stmt->close();

if($usern != 1) {
    echo "<script>location='devices.php'</script>";
    die();
}

$rday = isset($_GET['day']) ? $_GET['day'] : '';
$rmonth = isset($_GET['month']) ? $_GET['month'] : '';
$ryear = isset($_GET['year']) ? $_GET['year'] : '';
$Rcash = $_GET['casheer'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $lang_157;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo $lang_1;?>">
    <meta name="author" content="Mohamed Gad">
    <?php include 'includes/css.php';?>
    <script type="text/javascript">
    function newPopup(url) {
        popupWindow = window.open(url,'popUpWindow','height=300,width=300,left=10,top=10,resizable=no,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes');
        popupWindow.focus();
    }
    function newPopup2(url) {
        popupWindow = window.open(url,'popUpWindow','height=700,width=300,left=10,top=10,resizable=no,scrollbars=no,toolbar=no,menubar=no,location=no,directories=no,status=yes');
        popupWindow.focus();
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
                <div style="border-style:solid;border-width:1px;padding:15px;margin-right: 50px;">
                    <a href="reports.php"><span>التقارير</span></a> / 
                    <a href="reports_day.php?day=<?php echo $rday;?>&month=<?php echo $rmonth;?>&year=<?php echo $ryear;?>"><span>تقارير يوم <?php echo $ryear?>-<?php echo $rmonth?>-<?php echo $rday?></span></a> / 
                    <a href="reports_day_casheer.php?day=<?php echo $rday;?>&month=<?php echo $rmonth;?>&year=<?php echo $ryear;?>"><span>تقارير الكاشير</span></a> / 
                    <a href="reports_day_casheer_all.php?day=<?php echo $rday;?>&month=<?php echo $rmonth;?>&year=<?php echo $ryear;?>&se_cash=<?php echo urlencode($Rcash);?>"><span><?php echo !empty($Rcash) ? htmlspecialchars($Rcash) : 'كل الكاشيرين'; ?></span></a> / 
                    <span>فواتير الأجهزة</span>
                </div>

                <div class="row-fluid">        
                    <div class="box span11">
                        <div class="box-header well" data-original-title>
                            <h2><i class="icon-user"></i> <?php echo $lang_158;?> 
                            <?php if(!empty($Rcash)) { echo " - الكاشير: <strong>" . htmlspecialchars($Rcash) . "</strong>"; } ?>
                            </h2>
                        </div>
                        
                        <div class="box-content">
                            <table class="table table-striped table-bordered bootstrap-datatable datatable">
                                <thead>
                                    <tr>
                                        <th><?php echo $lang_149;?></th>
                                        <th><?php echo $lang_62;?></th>
                                        <th><?php echo $lang_159;?></th>
                                        <th><?php echo $lang_150;?></th>
                                        <th><?php echo $lang_77;?></th>
                                        <th><?php echo $lang_160;?></th>
                                        <th><?php echo $lang_161;?></th>
                                        <th><?php echo $lang_162;?></th>
                                        <th><?php echo $lang_151;?></th>
                                        <th><?php echo $lang_152;?></th>
                                        <th><?php echo $lang_105;?></th>
                                        <th>الخدمة<hr/>الضريبة</th>
                                        <th><?php echo $lang_106;?></th>
                                        <th><?php echo $lang_154;?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // بناء الـ Query
                                    $query = "SELECT * FROM reports WHERE 1=1";
                                    $types = "";
                                    $params = array();
                                    
                                    if(!empty($rday)) {
                                        $query .= " AND day = ?";
                                        $params[] = $rday;
                                        $types .= "i";
                                    }
                                    if(!empty($rmonth)) {
                                        $query .= " AND month = ?";
                                        $params[] = $rmonth;
                                        $types .= "i";
                                    }
                                    if(!empty($ryear)) {
                                        $query .= " AND year = ?";
                                        $params[] = $ryear;
                                        $types .= "i";
                                    }
                                    if(!empty($Rcash)) {
                                        $query .= " AND casheer = ?";
                                        $params[] = $Rcash;
                                        $types .= "s";
                                    }
                                    
                                    $query .= " GROUP BY session_id ORDER BY session_id DESC";
                                    
                                    $stmt = $conn->prepare($query);
                                    
                                    if($stmt === false) {
                                        echo "<tr><td colspan='14' align='center'><font color='red'><b>❌ خطأ في الـ Query: " . htmlspecialchars($conn->error) . "</b></font></td></tr>";
                                    } else {
                                        if(count($params) > 0) {
                                            call_user_func_array(array($stmt, 'bind_param'), array_merge(array($types), $params));
                                        }
                                        
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        
                                        if($result->num_rows == 0) {
                                            echo "<tr><td colspan='14' align='center'><font color='red'><b>❌ لا توجد نتائج</b></font></td></tr>";
                                        } else {
                                            while($row = $result->fetch_assoc()) {
                                                $se_se = $row['session_id'] ?? '';
                                                
                                                $stmt2 = $conn->prepare("SELECT COALESCE(SUM(price), 0) as sum_price FROM ps_orders WHERE session_id = ?");
                                                $stmt2->bind_param("s", $se_se);
                                                $stmt2->execute();
                                                $result2 = $stmt2->get_result();
                                                $row2 = $result2->fetch_assoc();
                                                $sum_items = isset($row2['sum_price']) ? floatval($row2['sum_price']) : 0;
                                                $stmt2->close();
                                                
                                                $stmt3 = $conn->prepare("SELECT COALESCE(SUM(money), 0) as sum_money FROM reports WHERE session_id = ?");
                                                $stmt3->bind_param("s", $se_se);
                                                $stmt3->execute();
                                                $result3 = $stmt3->get_result();
                                                $row3 = $result3->fetch_assoc();
                                                $sum_money = isset($row3['sum_money']) ? floatval($row3['sum_money']) : 0;
                                                $stmt3->close();
                                                
                                                $tom = isset($row['total']) ? floatval($row['total']) : 0;
                                                $hr = floor($tom / 3600) % 24;
                                                $mr = floor($tom / 60) % 60;
                                                $sr = ($tom % 60);
                                                
                                                $shift_check = isset($row['shift']) ? $row['shift'] : '';
                                                $discount = (isset($row['discount2']) ? floatval($row['discount2']) : 0) + (isset($row['discount_amount']) ? floatval($row['discount_amount']) : 0);
                                                $tax = isset($row['tax']) ? floatval($row['tax']) : 0;
                                                $service = isset($row['service']) ? floatval($row['service']) : 0;
                                                
                                                $total = $sum_money + $sum_items - $discount + $tax + $service;
                                                
                                                if($shift_check == 'One') {
                                                    $shift_check2 = isset($lang_155) ? $lang_155 : 'الشيفت الأول';
                                                } else {
                                                    $shift_check2 = isset($lang_156) ? $lang_156 : 'الشيفت الثاني';
                                                }
                                                
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['session_id'] ?? '') . "</td>";
                                                echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
                                                
                                                $thetype = isset($row['type']) ? $row['type'] : '';
                                                echo "<td>";
                                                switch($thetype) {
                                                    case 'single': echo isset($lang_3) ? $lang_3 : 'أحادي'; break;
                                                    case 'multi': echo isset($lang_4) ? $lang_4 : 'متعدد'; break;
                                                    case 'multi6': echo isset($lang_6) ? $lang_6 : 'متعدد 6'; break;
                                                    case 'multi7': echo isset($lang_7) ? $lang_7 : 'متعدد 7'; break;
                                                    default: echo htmlspecialchars($thetype);
                                                }
                                                echo "</td>";
                                                
                                                echo "<td>" . $shift_check2 . "</td>";
                                         echo "<td>" . htmlspecialchars(($row['year'] ?? '') . "/" . ($row['month'] ?? '') . "/" . ($row['day'] ?? '')) . "</td>";
echo "<td>" . htmlspecialchars(($row['Start_hour'] ?? '') . ":" . ($row['Start_minute'] ?? '')) . "</td>";
echo "<td>" . htmlspecialchars(($row['End_hour'] ?? '') . ":" . ($row['End_minute'] ?? '')) . "</td>";
echo "<td>" . $hr . ":" . $mr . ":" . $sr . "</td>";
echo "<td>" . intval($sum_items) . " " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</td>";
echo "<td>" . intval($sum_money) . " " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</td>";
echo "<td><font color='red'>" . intval($discount) . " " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</font></td>";
echo "<td>" . intval($service) . " " . (isset($lang_100) ? $lang_100 : 'ج.م') . "<hr/>" . intval($tax) . " " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</td>";
echo "<td><b><font color='green'>" . intval($total) . " " . (isset($lang_100) ? $lang_100 : 'ج.م') . "</font></b></td>";
                                                echo '<td><a class="btn btn-success" target="_blank" href="reports_ps_summary.php?s=' . urlencode($row['session_id'] ?? '') . '"><i class="icon-zoom-in icon-white"></i>' . (isset($lang_107) ? $lang_107 : 'عرض') . '</a></td>';
                                                echo "</tr>";
                                            }
                                        }
                                        
                                        $stmt->close();
                                    }
                                    ?>
                                </tbody>
                            </table>            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <footer>
            <p class="pull-left">&copy; <a href="http://www.psxegy.com" target="_blank">Gesture For Playstation</a> <?php echo date('Y');?></p>
        </footer>
    </div>

    <?php include 'includes/js.php';?>
</body>
</html>