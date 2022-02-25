<?php
include_once("Common.php");
include("CheckAdminLogin.php");

$Anual=0;
$Casual=0;
$Sick=0;
$Probation=0;
$CAnual=0;
$CCasual=0;
$CSick=0;
$CProbation=0;
$TAnual=0;
$TCasual=0;
$TSick=0;
$TProbation=0;

if(isset($_SESSION["FromDateLedger"]) && isset($_SESSION["TillDateLedger"]) && isset($_SESSION["EmployeeLedger"]))
	{
	    $query1="SELECT COUNT(ID) AS RosterDays,SUM(if(Status = 'Absent', 1, 0)) AS Absent,SUM(HalfDay) AS HalfDay,SUM(Late) AS Late,SUM(EarlyDep) AS Early FROM roster_login_history WHERE UserID = '".$_SESSION['UserID']."' AND (ActualDate BETWEEN '".date_format_Ymd($_SESSION['FromDateLedger'])."' AND '".date_format_Ymd($_SESSION['TillDateLedger'])."') ORDER BY ActualDate ASC";
	   // echo $query1; exit();
	    $result1 = mysql_query ($query1) or die(mysql_error()); 
		$row5 = mysql_fetch_array($result1);
		$rosterdays = $row5['RosterDays'];
		$rosterlate = $row5['Late'];
		$rosterearly = $row5['Early'];
		$rosterhalfdays = $row5['HalfDay'];
		$rosterabsent = $row5['Absent'];
	}

$TotalLateEarliesDedDays = 0;
$rosterhalfdays = ($rosterhalfdays * 0.5);

$query7 = "SELECT * FROM companies where ID <> 0 AND Status = 1";
	
	$res = mysql_query($query7) or die(mysql_error());
	$num_companydetails = mysql_num_rows($res);
	if($num_companydetails == 1)
	{
		$row7 = mysql_fetch_array($res);
		
		if($row7['DeductionOnLates'] == "Yes")
		{
			if($row7['DeductionOnLatesTypes'] == "Included")
			{
				$TotalLateEarlies = ($rosterlate + $rosterearly);
				
				if($TotalLateEarlies > 0)
				{
				
				if($TotalLateEarlies > 31)
				{
					$TotalLateEarliesDedDays = $row7['Days31'];
					if($row7['HalfDays31'] == 1)
					{
						$TotalLateEarliesDedDays = $TotalLateEarliesDedDays + 0.5;
					}
					
				}
				else
				{
					$TotalLateEarliesDedDays = $row7['Days'.$TotalLateEarlies];
					if($row7['HalfDays'.$TotalLateEarlies] == 1)
					{
						$TotalLateEarliesDedDays = $TotalLateEarliesDedDays + 0.5;
					}
				}
				
				}
				
			}
			if($row7['DeductionOnLatesTypes'] == "Individual")
			{
				if($rosterlate > 0)
				{
				
				if($rosterlate > 31)
				{
					$TotalLateDedDays = $row7['Days31'];
					if($row7['HalfDays31'] == 1)
					{
						$TotalLateDedDays = $TotalLateDedDays + 0.5;
					}
					
				}
				else
				{
					$TotalLateDedDays = $row7['Days'.$rosterlate];
					if($row7['HalfDays'.$rosterlate] == 1)
					{
						$TotalLateDedDays = $TotalLateDedDays + 0.5;
					}
				}
				
				}
				
				if($rosterearly > 0)
				{
				
				if($rosterearly > 31)
				{
					$TotalEarliesDedDays = $row7['EDays31'];
					if($row7['EHalfDays31'] == 1)
					{
						$TotalEarliesDedDays = $TotalEarliesDedDays + 0.5;
					}
					
				}
				else
				{
					$TotalEarliesDedDays = $row7['EDays'.$rosterearly];
					if($row7['EHalfDays'.$rosterearly] == 1)
					{
						$TotalEarliesDedDays = $TotalEarliesDedDays + 0.5;
					}
				}
				
				}
				
				$TotalLateEarliesDedDays = ($TotalLateDedDays + $TotalEarliesDedDays);
			}
		}
	}

$query4="SELECT Salary,ResignationDate FROM employees  WHERE ID=".(int)$_SESSION['UserID']." ";
$res4 = mysql_query($query4) or die(mysql_error());
$row4 = mysql_fetch_array($res4);
$CurrentSalary = ($row4['Salary']);
$ResignationDate = ($row4['ResignationDate']);

$deductionSalary = (($row4['Salary'] / 30) * ($TotalLateEarliesDedDays + $rosterhalfdays + $rosterabsent));

$query1="SELECT AnualLeaves,CasualLeaves,SickLeaves,ProbationaryLeaves FROM leaves_quota  WHERE ID <>0 AND EmpID=".(int)$_SESSION['UserID']." AND Approved = 1";
				$res1 = mysql_query($query1) or die(mysql_error());
				$num1 = mysql_num_rows($res1);
				if($num1 == 1)
				{
					$row1 = mysql_fetch_array($res1);
					$Anual=$row1['AnualLeaves'];
					$Casual=$row1['CasualLeaves'];
					$Sick=$row1['SickLeaves'];
					$Probation=$row1['ProbationaryLeaves'];
					
					$query2="SELECT AnualLeaves,CasualLeaves,SickLeaves,ProbationaryLeaves FROM current_leaves_quota  WHERE ID <>0 AND EmpID=".(int)$_SESSION['UserID']."";
					$res2 = mysql_query($query2) or die(mysql_error());
					$num2 = mysql_num_rows($res2);
					
					if($num2 == 1)
					{
						$row2 = mysql_fetch_array($res2);
						$CAnual=$row2['AnualLeaves'];
						$CCasual=$row2['CasualLeaves'];
						$CSick=$row2['SickLeaves'];
					    $CProbation=$row2['ProbationaryLeaves'];
						
						$TAnual = $Anual - $CAnual;
						$TCasual = $Casual - $CCasual;
						$TSick = $Sick - $CSick;
						$TProbation = $Probation - $CProbation;
					}
				}

	$msg="";
	
	$ArrivalTime=[];
	$DepartTime=[];
	$Reason=[];
	$Adjust=[];
	$RosterID=[];
	$RosterDate=[];
	$Late=[];
	$EarlyDep=[];
	$HalfDay=[];
	$Absent=[];
	$AdjustmentType=[];
	$LeaveType=[];
	$ErrorKeysLeave=[];
	$ErrorKeysReason=[];
	
	if(isset($_SESSION["FromDateLedger"]) && isset($_SESSION["TillDateLedger"]) && isset($_SESSION["EmployeeLedger"]))
	{
	    $query1="SELECT li.ActualDate,li.ID,li.Status,li.MStatus,li.HalfDay,li.Late,li.EarlyDep,li.ActualDate AS CheckDate ,li.LoginDate,DATE_FORMAT(li.ActualDate, '%D %b %Y') AS Date , DATE_FORMAT(li.ActualDate, '%W') AS Day , DATE_FORMAT(li.LoginTime, '%h:%i %p') AS ArrivalTime ,DATE_FORMAT(li.LoginTime, '%T') AS TArrivalTime ,li.LoginTime AS LoginAdjust,DATE_FORMAT(li.MLoginTime, '%h:%i %p') AS MArrivalTime , DATE_FORMAT(lo.LogoutTime, '%h:%i %p') AS DepartTime,DATE_FORMAT(lo.LogoutTime, '%T') AS TDepartTime,lo.LogoutTime AS LogoutAdjust,DATE_FORMAT(lo.MLogoutTime, '%h:%i %p') AS MDepartTime,e.EmpID,e.FirstName,e.LastName,e.Department,e.Designation,e.LeavesDays,e.OvertimePolicy,li.LateArrival,li.EarlyDepart,li.ScheduleDepart AS Depart,sh.Name AS ScheduleName,li.ScheduleArrival AS ScheduleArrivalTime,li.ScheduleDepart AS ScheduleDepartTime FROM employees e LEFT JOIN schedules sh ON e.ScheduleID = sh.ID LEFT JOIN roster_login_history li ON li.UserID = e.ID  LEFT JOIN roster_logout_history lo ON li.UserID = lo.UserID AND li.ActualDate = lo.ActualDate  WHERE li.Adjusted = 0 AND e.ID = '".$_SESSION['UserID']."' AND (li.ActualDate BETWEEN '".date_format_Ymd($_SESSION['FromDateLedger'])."' AND '".date_format_Ymd($_SESSION['TillDateLedger'])."') AND (li.Late = 1 OR li.EarlyDep = 1 OR li.HalfDay = 1 OR li.Status = 'Absent') ".($ResignationDate <> '0000-00-00' ? 'AND li.ActualDate <= e.ResignationDate' : '')." AND li.Requested = 0 ORDER BY li.ActualDate,e.EmpID ASC";
	   // echo $query; exit();
	    $result1 = mysql_query ($query1) or die(mysql_error()); 
		$maxRow = mysql_num_rows($result1);
				
	}
	else
	{
	    redirect("MyAttendanceLedger.php");
	}
		
if(isset($_POST["action"]) && $_POST["action"] == "submit_form")
{			

// 	echo '<pre>';
// 	print_r($_POST);
// 	print_r($_FILES);
// 	echo '</pre>';
// 	die();
	
// 	die('Working on it');

    $AnualLeaves=0;
	$CasualLeaves=0;
	$SickLeaves=0;
	$ProbationaryLeaves=0;
	
	if(isset($_POST["ArrivalTime"]))
		$ArrivalTime=$_POST["ArrivalTime"];
	if(isset($_POST["DepartTime"]))
		$DepartTime=$_POST["DepartTime"];
	if(isset($_POST["Reason"]))
		$Reason=$_POST["Reason"];
	if(isset($_POST["Adjust"]))
		$Adjust=$_POST["Adjust"];
	if(isset($_POST["RosterID"]))
		$RosterID=$_POST["RosterID"];
	if(isset($_POST["RosterDate"]))
		$RosterDate=$_POST["RosterDate"];
	if(isset($_POST["WaveOff"]))
		$WaveOff=$_POST["WaveOff"];
	if(isset($_POST["LeaveAdjust"]))
		$LeaveAdjust=$_POST["LeaveAdjust"];
	if(isset($_POST["Correction"]))
		$Correction=$_POST["Correction"];
	if(isset($_POST["AdjustmentType"]))
		$AdjustmentType=$_POST["AdjustmentType"];
	if(isset($_POST["LeaveType"]))
		$LeaveType=$_POST["LeaveType"];
	if(isset($_POST["Late"]))
		$Late=$_POST["Late"];
	if(isset($_POST["EarlyDep"]))
		$EarlyDep=$_POST["EarlyDep"];
	if(isset($_POST["HalfDay"]))
		$HalfDay=$_POST["HalfDay"];
	if(isset($_POST["Absent"]))
		$Absent=$_POST["Absent"];

	if (empty($_POST["Adjust"]))
	{
		$msg='<div class="alert alert-danger alert-dismissable">
		<i class="fa fa-ban"></i>
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
		<b>You have not requested for any adjustment. Please select the adjustment checkbox to forward your requests.</b>
		</div>';
	}
	else
	{
	    foreach($_POST["Adjust"] as $k => $TA)
		{
            if($AdjustmentType[$k] == 'LeaveAdjust')
			{
			    if($Late[$k] == 1)
    			{
    			    $$LeaveType[$k] += 0.25;
    			}
    			if($EarlyDep[$k] == 1)
    			{
    			    $$LeaveType[$k] += 0.25;
    			}
    			if($HalfDay[$k] == 1)
    			{
    			    $$LeaveType[$k] += 0.5;
    			}
    			if($Absent[$k] == 1)
    			{
    			    $$LeaveType[$k] += 1;
    			}
			}
		}
		
		foreach($_POST["Adjust"] as $k => $TA)
		{
            if($AdjustmentType[$k] == 'LeaveAdjust')
			{
			    if($LeaveType[$k] == 'AnualLeaves' && $AnualLeaves > $CAnual)
    			{
    			    array_push($ErrorKeysLeave,$k);
    			}
    			if($LeaveType[$k] == 'CasualLeaves' && $CasualLeaves > $CCasual)
    			{
    			    array_push($ErrorKeysLeave,$k);
    			}
    			if($LeaveType[$k] == 'SickLeaves' && $SickLeaves > $CSick)
    			{
    			    array_push($ErrorKeysLeave,$k);
    			}
    			if($LeaveType[$k] == 'ProbationaryLeaves' && $ProbationaryLeaves > $CProbation)
    			{
    			    array_push($ErrorKeysLeave,$k);
    			}
			}
			
			if($Reason[$k] == '')
			{
			    array_push($ErrorKeysReason,$k);
			}
			
		}
		
		if(!empty($ErrorKeysReason))
		{
		    $msg='<div class="alert alert-danger alert-dismissable">
        		<i class="fa fa-ban"></i>
        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
        		<b>Reason is required.</b>
        		</div>';
		}
		else if(!empty($ErrorKeysLeave))
		{
		    $msg='<div class="alert alert-danger alert-dismissable">
        		<i class="fa fa-ban"></i>
        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
        		<b>You don\'t have enough leaves in your quota.</b>
        		</div>';
		}
	}
		

	if($msg=="")
	{
	   // print_r($_POST["Adjust"]);
	   // echo '<br>';
	    echo '<pre>';
	    print_r($_POST); exit();
	   // echo '<br>';
		foreach($_POST["Adjust"] as $k => $TA)
		{
			if($AdjustmentType[$k] == 'WaveOff' || $AdjustmentType[$k] == 'Correction' || $AdjustmentType[$k] == 'LeaveAdjust')
			{
			
			$LeaveDaysCount = 0;
			    
			if($AdjustmentType[$k] == 'LeaveAdjust')
			{
			    if($Late[$k] == 1)
    			{
    			    $LeaveDaysCount += 0.25;
    			}
    			if($EarlyDep[$k] == 1)
    			{
    			    $LeaveDaysCount += 0.25;
    			}
    			if($HalfDay[$k] == 1)
    			{
    			    $LeaveDaysCount += 0.5;
    			}
    			if($Absent[$k] == 1)
    			{
    			    $LeaveDaysCount += 1;
    			}
    			
    			if($LeaveDaysCount > 0)
    			{
    			    $query2="UPDATE current_leaves_quota SET ".$LeaveType[$k]." = (".$LeaveType[$k]." - ".$LeaveDaysCount.") WHERE EmpID = " . $_SESSION['UserID'] . "";
    				mysql_query($query2) or die (mysql_error());
    				
    				$query2="INSERT INTO minus_leaves_quota SET EmpID = " . $_SESSION['UserID'] . ", LeaveDate='".$RosterDate[$k]."',LeaveType = '".$LeaveType[$k]."',FromRoster = '".(int)$RosterID[$k]."'";
    				mysql_query($query2) or die (mysql_error());
    			}
			}
			
			
			 
			$query="UPDATE roster_login_history SET Requested=1 WHERE  ID=" . (int)$RosterID[$k];
        	mysql_query($query) or die(mysql_error());
			
			$query="INSERT INTO timeadjust_requests SET 
					EmpID = '" . dbinput($_SESSION['UserID']) . "',
					RosterID = '" . (int)$RosterID[$k] . "',
					RosterDate = '" . $RosterDate[$k] . "',
					NotificationTo = '" . dbinput($_SESSION['MySupervisor']) . "',
					LoginTime = '" . time_format_gracetime($ArrivalTime[$k]) . "',
					LogoutTime = '" . time_format_gracetime($DepartTime[$k]) . "',
					Reason = '" . dbinput($Reason[$k]) . "',
					WaveOff = '" . ($AdjustmentType[$k] == 'WaveOff' ? 1 : 0) . "',
					Correction = '" . ($AdjustmentType[$k] == 'Correction' ? 1 : 0) . "',
					LeaveAdjust = '" . ($AdjustmentType[$k] == 'LeaveAdjust' ? 1 : 0) . "',
					LeaveType = '" . dbinput($LeaveType[$k]) . "',
					LeaveDays = '" . $LeaveDaysCount . "',
					DateAdded = NOW()";
				// 	echo $WaveOff[$k].' - '.$query.'<br>';
					
			mysql_query($query) or die (mysql_error());
			
			$ID = mysql_insert_id();
			
			if($Correction[$k] == 'Yes')
			{
			    if(isset($_FILES["Evidence"]) && $_FILES["Evidence"]['name'][$k] != "")
        		{
        		    $filenamearray=explode(".", $_FILES["Evidence"]['name'][$k]);
		            $ext=strtolower($filenamearray[sizeof($filenamearray)-1]);
        		    
        			ini_set('memory_limit', '-1');
        			
        			$tempName = $_FILES["Evidence"]['tmp_name'][$k];
        			$realName = "evidence".$ID . "." . $ext;
        			$StoreImage = $realName; 
        			$target = DIR_EVIDENCE . $realName;
        
        			$moved=move_uploaded_file($tempName, $target);
        		
        			if($moved)
        			{			
        			
        				$query="UPDATE timeadjust_requests SET FileName='" . dbinput($realName) . "' WHERE  ID=" . (int)$ID;
        				mysql_query($query) or die(mysql_error());
        			}
        			
        		}
			}
			
			
			}
		}
// 		exit();
		$_SESSION["msg"]='<div class="alert alert-success alert-dismissable">
		<i class="fa fa-ban"></i>
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
		<b>Time Adjust Request sent of all selected Attendance.</b>
		</div>';
		redirect("MyAttendanceLedger.php");	
// die;
	}
		

}
?>
<!--start from here-->
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Time Adjust Request</title>
<meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
<!-- bootstrap 3.0.2 -->
<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<!-- Ionicons -->
<link href="//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css" rel="stylesheet" type="text/css" />
<!-- daterange picker -->
<link href="css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<!-- iCheck for checkboxes and radio inputs -->
<link href="css/iCheck/all.css" rel="stylesheet" type="text/css" />
<!-- Bootstrap Color Picker -->
<link href="css/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet"/>
<!-- Bootstrap time Picker -->
<link href="css/timepicker/bootstrap-timepicker.min.css" rel="stylesheet"/>
<!-- Theme style -->
<link href="css/AdminLTE.css" rel="stylesheet" type="text/css" />

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
<style>
#footer {
	width:100%;
	height:50px;
	background-color:#3c8dbc;
	text-align:center;
	vertical-align:center;
	padding-top:15px;
}
#labelimp {
	background-color: rgba(60, 141, 188, 0.19);
	padding: 4px;
	font-size: 20px;
}
#labelimp {
	background-color: rgba(60, 141, 188, 0.19);
	padding: 4px;
	font-size: 20px;
	width: 100%;
	-webkit-border-radius: 5px;
	-moz-border-radius: 5px;
	border-radius: 5px;
	padding-left: 5px;
}
/*table-css-start*/
.table-content-1{
    color:#000033;
    font-size:20px;
    font-weight:500;
}
.table-content {
    padding:0px 0px 30px 20px;
}
.table-header {
    background-color: #ffffff;
    color: #0066cc;
    font-size: 14px;
    font-weight: 700;
    text-align: center;
    border-top: 1px solid #0066cc;
    border-top-left-radius:15px;
}
.table-dash tr .head-2 {
    width: 12% !important;
    min-height: 40px;
    padding: 15px 0 !important;
}
.table-dash tr .head-1 {
    width: 14% !important;
    padding: 15px 0 !important;
}
.table-dash tr .head-left {
    width: 16% !important;
    padding: 15px 0 !important;
}
.text-1{
    color:#000033;
    font-size:12px;
    font-weight:500;
}
.pt-15{
     padding: 15px 16px !important;
}
.text-right{
    color:#000033;
    font-size:13px;
    font-weight:500;
}
.col-redd {
    border: 2px solid #ff0000;
    border-right: none;
}
.table-header tr th{
    text-align:center;
}
.col-green{
    color:#339900;
    font-weight:600;
}
.col-red{
    color:#990000;
    font-weight:600;
}
.table-header tr th:first-child{
    border-top:none;
    border-top-left-radius:5px;
}
.table-rad {
    border-radius: 1.2em;
}
.table-rad .table-bordered>tbody>tr>th {
    border-bottom: none !important;
    border: 1px solid #ddd;
}
.table-rad {
    background: #ecf3fd;
    border-top: 1px solid #0066cc;
    padding: 0px 0px 0px 0px;
    box-shadow: 0 0 5px 0 #ccc;
}
.table.table-dash {
    margin-bottom: 0px;
}
.head-3{
    width:8%;
}
.table.table-dash tr td {
    border: 1px solid #ccc;
    border-bottom: none;
}
.table.table-dash tr th {
    border: 1px solid #ccc;
}
.table.table-dash {
    margin-bottom: 0px;
    border: 1px solid #ccc;
    border-bottom: none;
}
.day-timing {
    color: #9999ff;
    font-weight: 500;
    font-size: 13px;
}
.table-dash tr .head-1 {
    width: 14% !important;
    padding: 15px 0 !important;
    text-align:center;
}
.text-2{
 font-size:12px;
 font-weight:600;
}
.table-wrap-1{
    max-width: 100%;
    width: 100%;
}
.head-tables{
    display:flex;
}
.annual-content{
    color:#003333;
    font-size:14px;
    font-weight:600;
    text-align:start !important;
    padding: 10px 15px 10px 15px !important;

}
.annual-days{
    float:right;
    color:#000033;
}
.total-color{
    color:#0066cc;
    background: #d0e1fb;
}
.text-total{
    color:#000033;
    font-weight:500;
    font-size:13px;
}
.taken-text{
    color:#666666;
    font-size:13px;
    font-weight:500;
}
.adjust-text{
    font-size: 16px !important;
    font-weight: 500 !important;
    color: #fff !important;
    padding-left: 10px;
    margin: 0 !important;
}
.adjustment-wrap-main {
    padding: 0px 0px 30px 30px;
}
.adjustment-wrap {
    background: #000033;
    padding: 20px 0px 0 25px;
    border-radius: 12px 0px 0px 12px;
    min-height:325px;
}
.adjust-text1{
    color:#ffffff;
    font-weight:600;
    font-size:14px;
}
.adjust-text2{
    color: #999999;
    font-weight: 600;
    font-size: 15px;
}
.adjustment-content {
    margin-top: 25px;
}
.form-check-input.radio-input{
  accent-color: #000;
  transform: scale(1.3);
}
.form-check-input.radio-input:checked {
  background-color: #000;
  border-color: #000;
  box-shadow:none !important;
  outline:none !important;
}
.radio-wrap {
    display: flex;
}
.radio-text-1 {
    margin-left: 15px;
}
.leave-wrap {
    padding: 20px 0px 0 25px;
    background: #dfebfb;
    min-height:325px;
    border-top: 2px solid #0066cc;
}
.adjustment-wrap-main .row .col-12.col-md-3.col-lg-3 {
    padding: 0;
}
.adjustment-wrap-main .row .col-12.col-md-6.col-lg-6 {
    padding-left: 0;
}
.adjustment-wrap-2{
    border:2px solid #ff0000;
}
.reason-wrap {
    padding: 20px 0px 0 25px;
    background: #f9f9f9;
    min-height:325px;
    border-top: 2px solid #0066cc;
    border-radius: 0 15px 15px 0px;
}
.textarea-wrap {
    padding: 0 25px 0px 0px;
}
.contact-textarea.form-control {
    border: 1px solid #cccccc;
    box-shadow: none !important;
    min-height: 130px;
    max-height: 130px;
    resize: none;
    border-radius: 15px !important;
}
.textarea-wrap p {
    color: #003333;
    font-size: 18px;
    font-weight: 500;
}
.evidence-text {
    color: #003333;
    font-size: 18px;
    font-weight: 500;
}
.evidence-text2 {
    color: #666666;
    font-size: 13px;
    font-weight: 500;
}
.mtt-10{
    margin:30px 0 0 0;
}
.image-text{
    color:#666666;
    font-size:14px;
    font-weight:400;
}
.evidence-main-wrap {
    padding: 0 25px 0px 20px;
}
.choose-file-wrap {
    border: 2px solid #ccc;
    padding: 10px 0 10px 10px;
    border-radius: 50px;
}
.file-chosen:focus {
    outline: none !important;
}
.leave-1{
    display:flex;
}
.leaves-detail-1 {
    margin: 10px 0px 10px 20px;
}
.leaves-detail-2 {
    margin: 5px 0px 10px 30px;
}
.input-field {
    outline: none;
    border: none;
    padding: 10px 30px 5px 10px;
}
.mt-5{
    margin-top: 10px;
    margin-bottom: 7px;
}
.col-red-1 {
    background-color: #ffcccc;
    border: 2px solid #ff0000;
    border-left: none;
    border-right: none;
}
.reason-wrap.col-red-2 {
    background-color: #fce8e9;
    border: 2px solid #ff0000;
    border-left: none;
}
.pt-15{
    padding:15px 0 !important;
}
.file-chosen {
    border-radius: 0 !important;
}
.evi-img1{
    max-width:188px;
    width:100%;
    object-fit:cover;
    object-position:center;
    max-height:100px;
}
.textarea-evi{
    border: 1px solid #cccccc;
    box-shadow: none !important;
    min-height: 85px;
    max-height: 85px;
    width: 100%;
    resize: none;
    border-radius: 15px !important;
    margin-left: 20px;
    padding: 10px;
}
.remarks-text{
    margin-left:20px;
}
.textarea-evi:focus {
    outline: none !important;
}
/*table-css-end*/
/*hrms-second-screen-start*/
.adjustment-approval {
    display: flex;
    padding: 20px 0 0 30px;
    background: #142d4b;
    padding: 42px 0 42px 30px;
    border-radius: 0px 0px 0 12px;
    min-height: 178px;
}
.remark-mandatory{
    color:#ff0000;
    font-size:9px;
    font-weight:500;
}
.manage-screewrap .adjustment-content {
    margin-top: 5px;
    background: #000033;
    padding: 0px 0px 0 25px;
}
.manage-screewrap .adjustment-wrap {
    border-radius: 12px 0px 0px 12px;
    min-height: 420px;
    padding: 20px 0px 0 0px;
}
.approval-text{
    color: #a7c2e1;
    font-size:15px;
    font-weight:500;
}
@media only screen and (max-width:1440px) {
    .annual-content.text-2 {
    font-size: 12px;
}
}
.modal-guide:hover{
    background:#0a2d0c !important;
    color:#fff !important;
}
.modal-headerwrap{
    display:flex;
    justify-content:space-between;
}
.guide-text{
    color:#0066cc;
    font-weight:600;
    font-size:18px;
    margin: 0;
}
.modal-textcolor{
    color:#0066cc;
    font-weight:600;
    font-size:13px;
}
.modal-header .close.close-btn {
    margin-top: -2px;
    background: transparent !important;
    color: #287ed4 !important;
    font-size: 30px;
    font-weight: 700 !important;
}
.modal-adjust .modal-body {
    position: relative;
    padding: 20px;
    background: #ecf3fd;
}
/*hrms-second-screen-end*/
</style>



</head>
<body class="skin-blue">
<!-- header logo: style can be found in header.less -->
<?php
		include_once("Header.php");
		?>
<div class="wrapper row-offcanvas row-offcanvas-left">
  <!-- Left side column. contains the logo and sidebar -->
  <?php
	include_once("Sidebar.php");
?>
  <!-- Right side column. Contains the navbar and content of the page -->
  <aside class="right-side">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1> Time Adjust Request</h1>
      <ol class="breadcrumb">
        <li><a href="MyAttendanceLedger.php"><i class="fa fa-dashboard"></i>My Attendance</a></li>
        <li class="active">Time Adjust Request</li>
      </ol>
    </section>
    <!-- Main content -->
    <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" name="frmPage">
      <section class="content">
       <div class="box-footer" style="text-align:right;">
                <button class="btn btn-primary margin modal-guide" data-toggle="modal" data-target="#myModal" type="button">Guide for Adjustment</button> 
                <button type="submit" class="btn btn-success margin">Send</button>
                <button class="btn btn-primary margin" type="button" onClick="location.href='MyAttendanceLedger.php'">Cancel</button>
                
            </div>
              <?php
		  		echo $msg;
				if(isset($_SESSION["msg"]))
				{
					echo $_SESSION["msg"];
					$_SESSION["msg"]="";
				}
				?>
<!--hrms-new-screen-start-->
   <!--5-table-start-->
   <div class="head-tables mtt-10">
      <div class="table-wrap-1">
         <!--table-start-->
         <div class="table-content">
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3 annual-content text-2" colspan="2">Annual Leaves<span class="annual-days"><?php echo $Anual ; ?></span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="taken-text pt-15">Taken  <?php echo $TAnual ; ?></td>
                        <td class="taken-text pt-15">Balance  <?php echo $CAnual ; ?></td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
         <!--table-end-->
      </div>
      <div class="table-wrap-1">
         <!--table-start-->
         <div class="table-content">
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3 annual-content text-2" colspan="2">Casual Leaves<span class="annual-days"><?php echo $Casual ; ?></span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="taken-text pt-15">Taken  <?php echo $TCasual ; ?></td>
                        <td class="taken-text pt-15">Balance  <?php echo $CCasual ; ?></td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
         <!--table-end-->
      </div>
      <div class="table-wrap-1">
         <!--table-start-->
         <div class="table-content">
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3 annual-content text-2" colspan="2">Sick Leaves<span class="annual-days"><?php echo $Sick ; ?></span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="taken-text pt-15">Taken  <?php echo $TSick ; ?></td>
                        <td class="taken-text pt-15">Balance  <?php echo $CSick ; ?></td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
         <!--table-end-->
      </div>
      <div class="table-wrap-1">
         <!--table-start-->
         <div class="table-content">
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3 annual-content text-2" colspan="2">Probationary Leaves<span class="annual-days"><?php echo $Probation ; ?></span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="taken-text pt-15">Taken  <?php echo $TProbation ; ?></td>
                        <td class="taken-text pt-15">Balance  <?php echo $CProbation ; ?></td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
         <!--table-end-->
      </div>
      <div class="table-wrap-1">
         <!--table-start-->
         <div class="table-content">
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3 annual-content total-color text-2" colspan="2">Total Leaves<span class="annual-days"><?php echo ($Anual + $Casual + $Sick + $Probation) ; ?></span></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="text-total pt-15">Taken  <?php echo ($TAnual + $TCasual + $TSick + $TProbation) ; ?></td>
                        <td class="text-total pt-15">Balance  <?php echo ($CAnual + $CCasual + $CSick + $CProbation) ; ?></td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
         <!--table-end-->
      </div>
   </div>
   <!--5-table-end-->
   <!--table-salary-status-start-->
   <div class="row">
      <div class="col-12 col-md-6 col-lg-6">
         <div class="table-content">
            <p class="table-content-1">Salary Status</p>
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3 text-2">Current Salary</th>
                        <th scope="col" class="text-2" >Deduction Salary</th>
                        <th scope="col" class="text-2">Balance Salary without Deduction</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="text-1 pt-15">PKR <?php echo number_format($CurrentSalary); ?></td>
                        <td class="head-left text-1">PKR <?php echo number_format($deductionSalary); ?> <span class="day-timing">(<?php echo ($TotalLateEarliesDedDays + $rosterhalfdays + $rosterabsent); ?> Days)</span></td>
                        <td class="head-left text-1">PKR <?php echo number_format($CurrentSalary - $deductionSalary); ?></td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
      </div>
      <div class="col-12 col-md-6 col-lg-6">
         <div class="table-content">
            <p class="table-content-1">Adjustment Criteria</p>
            <div class="table-responsive table-rad">
               <!--table-start-->
               <table class="table table-dash table-hover">
                  <thead class="table-header">
                     <tr>
                        <th scope="col" class="head-3">Late Ins</th>
                        <th scope="col" >Early Outs</th>
                        <th scope="col">Half Days</th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr>
                        <td scope="row" class="head-1 text-right">4 Late ins = 1 Absent</td>
                        <td class="head-1 text-right">4 Early outs = 1 absent</td>
                        <td class="head-1 text-right">1 Half day = 0.5 absent</td>
                     </tr>
                  </tbody>
               </table>
               <!--table-end-->
            </div>
         </div>
      </div>
   </div>
   <!--table-salary-status-start-end-->
   <!--adjustment-div-start-->
   <?php
			$i=0;
			while($row = mysql_fetch_array($result1,MYSQL_ASSOC))
			{
			   $waveOffAdjust = 0;
			if($row["TArrivalTime"] != Null && $row["TDepartTime"] != Null)
					  {
					   list($hours, $minutes) = explode(':', $row["TArrivalTime"]);
							$startTimestamp = mktime($hours, $minutes);

							list($hours, $minutes) = explode(':', $row["TDepartTime"]);
							$endTimestamp = mktime($hours, $minutes);
							
							$newTimestamp1 = mktime(00, 00);
							$newTimestamp2 = mktime(23, 59);

                            $T = explode(' ',$row["DepartTime"]);
							$T1 = explode(' ',$row["ArrivalTime"]);
		                    $AMPM = $T[1];
							$AMPM1 = $T1[1];
		                    
							if($AMPM1 == "AM" && $AMPM == "AM")
		                    {
		                     $seconds =  $endTimestamp - $startTimestamp;   
		                    }
							else if($AMPM1 == "AM"  && $AMPM == "PM")
		                    {
		                     $seconds = $endTimestamp - $startTimestamp;    
		                    }
							else if($AMPM1 == "PM"  && $AMPM == "PM")
		                    {
		                     $seconds = $endTimestamp - $startTimestamp;   
		                    }
							else if($AMPM1 == "PM"  && $AMPM == "AM")
		                    {
							$seconds2=0;
		                     $seconds =  $endTimestamp - $newTimestamp1;
							 $seconds2 =  $newTimestamp2 - $startTimestamp;
							 $seconds2 = abs($seconds2);
							 $seconds = $seconds + $seconds2;
							 $seconds += 60;
							 
		                    }
                            
							
							$minutes = ($seconds / 60) % 60;
							$hours = floor($seconds / (60 * 60));
							
							if($AMPM1 == "PM"  && $AMPM == "PM")
		                    {
		                     //$hours = ($hours + 24);
		                    }
							else if($AMPM1 == "PM"  && $AMPM == "AM")
		                    {
		                      //$hours = ($hours + 24);
		                    }
							
							if($hours < 0)
							$hours = ($hours + 24);
							
							if($hours > 8)
							{
							    $waveOffAdjust = 1;
							}
							
					  }
		?>
   <div class="adjustment-wrap-main">
      <div class="row">
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="adjustment-wrap">
               <!--adjustment-start-->
               <div class="ajustment-checkbox">
                  <input type="checkbox" name="Adjust[<?php echo $i; ?>]" value="Yes" <?php echo (array_key_exists($i,$Adjust) ? 'checked' : ''); ?> class="check-adjust"> 
                  <label class="adjust-text">Adjustment:</label>
                    <input type="hidden" name="RosterID[<?php echo $i; ?>]" value="<?php echo $row["ID"]; ?>" />
    				<input type="hidden" name="RosterDate[<?php echo $i; ?>]" value="<?php echo $row["ActualDate"]; ?>" />
    				<input type="hidden" name="Late[<?php echo $i; ?>]" value="<?php echo $row["Late"]; ?>" />
    				<input type="hidden" name="EarlyDep[<?php echo $i; ?>]" value="<?php echo $row["EarlyDep"]; ?>" />
    				<input type="hidden" name="HalfDay[<?php echo $i; ?>]" value="<?php echo $row["HalfDay"]; ?>" />
    				<input type="hidden" name="Absent[<?php echo $i; ?>]" value="<?php echo ($row["Status"] == 'Absent' ? 1 : 0 ); ?>" />
               </div>
               <div class="adjustment-content">
                  <p class="adjust-text1">Date : <span class="adjust-text2">  <?php echo dboutput($row["Day"])." ".dboutput($row["Date"]); ?></span></p>
                  <p class="adjust-text1">Status : <span class="adjust-text2"> <?php echo ($row["Late"] == 1 ? 'Li,' : '' ); ?><?php echo ($row["HalfDay"] == 1 ? 'HD,' : '' ); ?><?php echo ($row["EarlyDep"] == 1 ? 'Eo,' : '' ); ?><?php echo dboutput($row["Status"]); ?></span></p>
                  <p class="adjust-text1">Shift Timing : <br><span class="adjust-text2"><?php echo revert_time_format_gracetime($row["ScheduleArrivalTime"]); ?> to <?php echo revert_time_format_gracetime($row["ScheduleDepartTime"]); ?> </span></p>
                  <p class="adjust-text1">Reported Shift Time : <br><span class="adjust-text2"><?php echo revert_time_format_gracetime($row["LoginAdjust"]); ?> to <?php echo revert_time_format_gracetime($row["LogoutAdjust"]); ?></span> </p>
                  <p class="adjust-text1">Reported Time Calculate : <br><span class="adjust-text2"><?php echo $hours; ?>hrs and <?php echo $minutes; ?>mins</span> </p>
               </div>
               <!--adjustment-end-->
            </div>
         </div>
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="leave-wrap">
                <?php if($row["Late"] == 1 || $row["EarlyDep"] == 1 || $row["HalfDay"] == 1 || $row["Status"] == 'Absent'){ ?>
        							<input type="radio"  class="form-control" /> 
        							<?php } ?>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="AdjustmentType[<?php echo $i; ?>]" value="LeaveAdjust" id="LAradio-<?php echo $i; ?>">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1" for="LAradio-<?php echo $i; ?>">
                        Leave Adjust
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-1" style="display:none">
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-3">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1">
                           Annual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-4">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-4">
                           Sick Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-5">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-5">
                           Casual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-6">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-6">
                           Probationary Leave
                        </p>
                     </div>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-58">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2" for="radio-58">
                        Waive Off
                     </p>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-7">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2">
                        Correction
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-2">
                  <div class="leave-field">
                     <p class="time-text mb-2">
                        Adjusted Time In : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
                  <div class="leave-field">
                     <p class="time-text mt-5 mb-2">
                        Adjusted Time Out : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-12 col-md-6 col-lg-6 p-0">
            <form>
               <div class="reason-wrap">
                  <div class="textarea-wrap">
                     <p>Reason:</p>
                     <textarea class="contact-textarea form-control" placeholder="Message"></textarea>
                  </div>
                  <div class="evidence-main-wrap">
                     <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                           <p class="evidence-text mtt-10">Evidence : <span class="image-text">(jpg or png)</span> </p>
                           <p class="evidence-text2">without evidence,correction will not be accepted.</p>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                           <!--choose-file-start-->
                           <div class="choose-file-wrap mtt-10">
                              <div class="file-wrap">
                                 <input type="file" class="file-chosen">                                          				
                              </div>
                           </div>
                           <!--choose-file-end-->
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
   <?php
        $i++;
			}
		?>
	<!--adjustment-div-end-->
   <!--wrap-2-start-->
   <div class="adjustment-wrap-main">
      <div class="row">
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="adjustment-wrap col-redd">
               <!--adjustment-start-->
               <div class="ajustment-checkbox">
                  <input type="checkbox"  class="check-adjust"> 
                  <label class="adjust-text">Adjustment:</label>
               </div>
               <div class="adjustment-content">
                  <p class="adjust-text1">Date : <span class="adjust-text2">  Friday 28th Jan 2022</span></p>
                  <p class="adjust-text1">Status : <span class="adjust-text2"> Late Present</span></p>
                  <p class="adjust-text1">Shift TIming : <br><span class="adjust-text2">09:00 PM to 06:00 AM </span></p>
                  <p class="adjust-text1">Reported Shift Time : <br><span class="adjust-text2">09:20 PM to 06:20 AM</span> </p>
                  <p class="adjust-text1">Reported Time Calculate : <br><span class="adjust-text2">5hrs and 22mins</span> </p>
               </div>
               <!--adjustment-end-->
            </div>
         </div>
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="leave-wrap col-red-1">
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-8">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1" for="radio-8">
                        Leave Adjust
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-1" style="display:none">
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-9">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1">
                           Annual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-10">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-10">
                           Sick Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-11">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-11">
                           Casual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-12">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-12">
                           Probationary Leave
                        </p>
                     </div>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-13">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2" for="radio-13">
                        Waive Off
                     </p>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-14">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2">
                        Correction
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-2">
                  <div class="leave-field">
                     <p class="time-text mb-2">
                        Adjusted Time In : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
                  <div class="leave-field">
                     <p class="time-text mt-5 mb-2">
                        Adjusted Time Out : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-12 col-md-6 col-lg-6 p-0">
            <form>
               <div class="reason-wrap col-red-2">
                  <div class="textarea-wrap">
                     <p>Reason:</p>
                     <textarea class="contact-textarea form-control"></textarea>
                  </div>
                  <div class="evidence-main-wrap">
                     <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                           <p class="evidence-text mtt-10">Evidence : <span class="image-text">(jpg or png)</span> </p>
                           <p class="evidence-text2">without evidence,correction will not be accepted.</p>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                           <!--choose-file-start-->
                           <div class="choose-file-wrap mtt-10">
                              <div class="file-wrap">
                                 <input type="file" class="file-chosen">                                          				
                              </div>
                           </div>
                           <!--choose-file-end-->
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
   <!--wrap-2-end-->
   <!--wrap-3-start-->
   <div class="adjustment-wrap-main">
      <div class="row">
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="adjustment-wrap">
               <!--adjustment-start-->
               <div class="ajustment-checkbox">
                  <input type="checkbox"  class="check-adjust"> 
                  <label class="adjust-text">Adjustment:</label>
               </div>
               <div class="adjustment-content">
                  <p class="adjust-text1">Date : <span class="adjust-text2">  Friday 28th Jan 2022</span></p>
                  <p class="adjust-text1">Status : <span class="adjust-text2"> Late Present</span></p>
                  <p class="adjust-text1">Shift TIming : <br><span class="adjust-text2">09:00 PM to 06:00 AM </span></p>
                  <p class="adjust-text1">Reported Shift Time : <br><span class="adjust-text2">09:20 PM to 06:20 AM</span> </p>
                  <p class="adjust-text1">Reported Time Calculate : <br><span class="adjust-text2">5hrs and 22mins</span> </p>
               </div>
               <!--adjustment-end-->
            </div>
         </div>
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="leave-wrap">
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-15">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1" for="radio-15">
                        Leave Adjust
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-1" style="display:none">
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-16">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1">
                           Annual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-17">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-17">
                           Sick Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-18">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-18">
                           Casual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-19">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-19">
                           Probationary Leave
                        </p>
                     </div>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-20">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2" for="radio-20">
                        Waive Off
                     </p>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-21">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2">
                        Correction
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-2">
                  <div class="leave-field">
                     <p class="time-text mb-2">
                        Adjusted Time In : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
                  <div class="leave-field">
                     <p class="time-text mt-5 mb-2">
                        Adjusted Time Out : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-12 col-md-6 col-lg-6 p-0">
            <form>
               <div class="reason-wrap">
                  <div class="textarea-wrap">
                     <p>Reason:</p>
                     <textarea class="contact-textarea form-control"></textarea>
                  </div>
                  <div class="evidence-main-wrap">
                     <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                           <p class="evidence-text mtt-10">Evidence : <span class="image-text">(jpg or png)</span> </p>
                           <p class="evidence-text2">without evidence,correction will not be accepted.</p>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                           <!--choose-file-start-->
                           <div class="choose-file-wrap mtt-10">
                              <div class="file-wrap">
                                 <input type="file" class="file-chosen">                                          				
                              </div>
                           </div>
                           <!--choose-file-end-->
                        </div>
                     </div>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
   <!--wrap-3-end-->
   <!--wrap-4-start-->
   <div class="adjustment-wrap-main">
      <div class="row">
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="adjustment-wrap">
               <!--adjustment-start-->
               <div class="ajustment-checkbox">
                  <input type="checkbox"  class="check-adjust"> 
                  <label class="adjust-text">Adjustment:</label>
               </div>
               <div class="adjustment-content">
                  <p class="adjust-text1">Date : <span class="adjust-text2">  Friday 28th Jan 2022</span></p>
                  <p class="adjust-text1">Status : <span class="adjust-text2"> Late Present</span></p>
                  <p class="adjust-text1">Shift TIming : <br><span class="adjust-text2">09:00 PM to 06:00 AM </span></p>
                  <p class="adjust-text1">Reported Shift Time : <br><span class="adjust-text2">09:20 PM to 06:20 AM</span> </p>
                  <p class="adjust-text1">Reported Time Calculate : <br><span class="adjust-text2">5hrs and 22mins</span> </p>
               </div>
               <!--adjustment-end-->
            </div>
         </div>
         <div class="col-12 col-md-3 col-lg-3 p-0">
            <div class="leave-wrap">
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-22">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1" for="radio-22">
                        Leave Adjust
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-1" style="display:none">
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-23">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1">
                           Annual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-24">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-24">
                           Sick Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-25">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-25">
                           Casual Leave
                        </p>
                     </div>
                  </div>
                  <div class="leave-1">
                     <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-26">
                     <div class="radio-text-wrap">
                        <p class="radio-text-1" for="radio-26">
                           Probationary Leave
                        </p>
                     </div>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-27">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2" for="radio-27">
                        Waive Off
                     </p>
                  </div>
               </div>
               <div class="radio-wrap">
                  <input class="form-check-input radio-input" type="radio" name="flexRadioDefault" id="radio-28">
                  <div class="radio-text-wrap">
                     <p class="radio-text-1 mb-2">
                        Correction
                     </p>
                  </div>
               </div>
               <div class="leaves-detail-2">
                  <div class="leave-field">
                     <p class="time-text mb-2">
                        Adjusted Time In : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
                  <div class="leave-field">
                     <p class="time-text mt-5 mb-2">
                        Adjusted Time Out : 
                     </p>
                     <div class="adjust-input">
                        <input class="input-field" type="text">
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-12 col-md-6 col-lg-6 p-0">
            <form>
               <div class="reason-wrap">
                  <div class="textarea-wrap">
                     <p>Reason:</p>
                     <textarea class="contact-textarea form-control"></textarea>
                  </div>
               </div>
            </form>
         </div>
      </div>
   </div>
   <!--wrap-4-end-->
<!--hrms-new-screen-end-->
<!--modal-guides-for-adjustment-start-->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-lg modal-adjust">

      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <!-- modal header  -->
              <div class="modal-headerwrap">
                  <p class="guide-text">Guide for Adjustments:</p>
                  <button type="button" class="close close-btn" data-dismiss="modal">&times;</button>
              </div>
        </div>
        <div class="modal-body">
        <!-- begin modal body content  -->
          <section class="stage">
           <div class="modal-content-wrap">
               <p><span class="modal-textcolor">Leave Adjustment:</span> While availing for leave adjustment, please review your leave quota and don’t forget to provide evidence in case of sick leave.</p>
               <p>In case of Half Day status, you may get it adjusted through leaves or you may add correction if there is any error in system (reason is necessary)</p>
               <p><span class="modal-textcolor">Waive off:</span> if your standard working hours are more than 9 hours, than system will provide you with waive off option which will result in no deduction and no leaves adjustment.</p>
               <p>While sending a Correction request, please ensure to review your Shift Hours mentioned in your HRMS Portal. In case your timings are different from shift hours mentioned in portal then your correction request may not be adjusted.</p>
               <p>Please ensure to mark required checkboxes, in case of ignorance your request will not be processed</p>
               <p>As per the defined attendance criteria 3 late-ins and 3 early-outs adjustments are not required to be sent.</p>
               <p>Once you are done with the whole process, please request your line manager to check the HRMS Portal.  </p>
           </div>
          </section>
        <!-- end modal body content  -->
        </div>
      </div>

    </div>
  </div>
<!--modal-guides-for-adjustment-end-->


      </section>
    </form>
    <!-- /.content -->
  </aside>
  <!-- /.right-side -->
</div>
<!-- ./wrapper -->

<!-- add new calendar event modal -->
<!-- jQuery 2.0.2 -->
<!-- jQuery UI 1.10.3 -->

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script>                         
    $("#radio-1").on('ifChecked', function(event){
        $('.leaves-detail-1').show();
        $('.leaves-detail-2').hide();
    });  
    
    $("#radio-7").on('ifChecked', function(event){
        $('.leaves-detail-2').show();
        $('.leaves-detail-1').hide();
    }); 
    $("#radio-58").on('ifChecked', function(event){
        // alert("append");
        $(this).closest().find(".contact-textarea").val('9hrs Completed');
        $('.leaves-detail-2').hide();
        $('.leaves-detail-1').hide();
    }); 
    
    
</script>
<script>
	$(function(){
	var sidebar = $('.sidebar-menu');  // cache sidebar to a variable for performance

	sidebar.delegate('.treeview','click',function(){ 
	  if($(this).hasClass('active')){
		$(this).removeClass('active');
		sidebar.find('.inactive > .treeview-menu').hide(200);
		sidebar.find('.inactive').removeClass('inactive');
	   $(this).addClass('inactive');
	   $(this).find('.treeview-menu').show(200);
	 }else{
	  sidebar.find('.active').addClass('inactive');          
	  sidebar.find('.active').removeClass('active'); 
	   $(this).Class('treeview-menu').hide(200);
	 }
	});

	});
	
	$(document).click(function (event) {   
        $('.treeview-menu:visible').hide();
	});
	

	</script>

<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
<!-- InputMask -->
<script src="js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
<script src="js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
<script src="js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
<!-- date-range-picker -->
<script src="js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
<!-- bootstrap color picker -->
<script src="js/plugins/colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>
<!-- bootstrap time picker -->
<script src="js/plugins/timepicker/bootstrap-timepicker.min.js" type="text/javascript"></script>
<!-- AdminLTE App -->
<script src="js/AdminLTE/app2.js" type="text/javascript"></script>
<!-- AdminLTE for demo purposes -->
<script src="../js/AdminLTE/demo.js" type="text/javascript"></script>

<script type="text/javascript">
            $(function() {
                //Datemask dd/mm/yyyy
                $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
                //Datemask2 mm/dd/yyyy
                $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
                //Money Euro
                $("[data-mask]").inputmask();

                //Date range picker
                $('#reservation').daterangepicker({format: 'YYYY-MM-DD'});
                //Date range picker with time picker
                $('#reservationtime').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'MM/DD/YYYY h:mm A'});
                //Date range as a button
                $('#daterange-btn').daterangepicker(
                        {
                            ranges: {
                                'Today': [moment(), moment()],
                                'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
                                'Last 7 Days': [moment().subtract('days', 6), moment()],
                                'Last 30 Days': [moment().subtract('days', 29), moment()],
                                'This Month': [moment().startOf('month'), moment().endOf('month')],
                                'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                            },
                            startDate: moment().subtract('days', 29),
                            endDate: moment()
                        },
                function(start, end) {
                    $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                }
                );

                //iCheck for checkbox and radio inputs
                $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                    checkboxClass: 'icheckbox_minimal',
                    radioClass: 'iradio_minimal'
                });
                //Red color scheme for iCheck
                $('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
                    checkboxClass: 'icheckbox_minimal-red',
                    radioClass: 'iradio_minimal-red'
                });
                //Flat red color scheme for iCheck
                $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
                    checkboxClass: 'icheckbox_flat-red',
                    radioClass: 'iradio_flat-red'
                });

                //Colorpicker
                $(".my-colorpicker1").colorpicker();
                //color picker with addon
                $(".my-colorpicker2").colorpicker();

                //Timepicker
                $(".timepicker").timepicker({
                    showInputs: false,
                });
            });
        </script>
</body>
</html>
