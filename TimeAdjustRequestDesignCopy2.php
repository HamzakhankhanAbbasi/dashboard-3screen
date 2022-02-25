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
	
	if(isset($_SESSION["FromDateLedger"]) && isset($_SESSION["TillDateLedger"]) && isset($_SESSION["EmployeeLedger"]))
	{
	    $query1="SELECT li.ID,li.Status,li.MStatus,li.HalfDay,li.Late,li.EarlyDep,li.DateAdded AS CheckDate ,li.LoginDate,DATE_FORMAT(li.ActualDate, '%D %b %Y') AS Date , DATE_FORMAT(li.ActualDate, '%W') AS Day , DATE_FORMAT(li.LoginTime, '%h:%i %p') AS ArrivalTime ,DATE_FORMAT(li.LoginTime, '%T') AS TArrivalTime ,li.LoginTime AS LoginAdjust,DATE_FORMAT(li.MLoginTime, '%h:%i %p') AS MArrivalTime , DATE_FORMAT(lo.LogoutTime, '%h:%i %p') AS DepartTime,DATE_FORMAT(lo.LogoutTime, '%T') AS TDepartTime,lo.LogoutTime AS LogoutAdjust,DATE_FORMAT(lo.MLogoutTime, '%h:%i %p') AS MDepartTime,e.EmpID,e.FirstName,e.LastName,e.Department,e.Designation,e.LeavesDays,e.OvertimePolicy,li.LateArrival,li.EarlyDepart,li.ScheduleDepart AS Depart,sh.Name AS ScheduleName,li.ScheduleArrival AS ScheduleArrivalTime,li.ScheduleDepart AS ScheduleDepartTime FROM employees e LEFT JOIN schedules sh ON e.ScheduleID = sh.ID LEFT JOIN roster_login_history li ON li.UserID = e.ID  LEFT JOIN roster_logout_history lo ON li.UserID = lo.UserID AND li.ActualDate = lo.ActualDate  WHERE li.Adjusted = 0 AND e.ID = '".$_SESSION['UserID']."' AND (li.ActualDate BETWEEN '".date_format_Ymd($_SESSION['FromDateLedger'])."' AND '".date_format_Ymd($_SESSION['TillDateLedger'])."') AND (li.Late = 1 OR li.EarlyDep = 1 OR li.HalfDay = 1 OR li.Status = 'Absent') ".($ResignationDate <> '0000-00-00' ? 'AND li.ActualDate <= e.ResignationDate' : '')." ORDER BY li.ActualDate,e.EmpID ASC";
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

	echo '<pre>';
	print_r($_POST);
	print_r($_FILES);
	echo '</pre>';
	die();
	
// 	die('Working on it');
	
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
	if(isset($_POST["WaveOff"]))
		$WaveOff=$_POST["WaveOff"];
	if(isset($_POST["LeaveAdjust"]))
		$LeaveAdjust=$_POST["LeaveAdjust"];
	if(isset($_POST["Correction"]))
		$Correction=$_POST["Correction"];

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
			if($WaveOff[$k] == 'Yes' || $LeaveAdjust[$k] == 'Yes' || $Correction[$k] == 'Yes')
			{
			}
			else
			{
			    $msg='<div class="alert alert-danger alert-dismissable">
        		<i class="fa fa-ban"></i>
        		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
        		<b>Please select any one adjustment type out of below mention types. ex: Leave Adjust, Waive Off or Correction.</b>
        		</div>';
			}
		}
	}
		

	if($msg=="")
	{
	   // print_r($_POST["Adjust"]);
	   // echo '<br>';
	   // echo '<pre>';
	   // print_r($_POST); exit();
	   // echo '<br>';
		foreach($_POST["Adjust"] as $k => $TA)
		{
// 			echo $Reason[$k].'<br>';
			if($WaveOff[$k] == 'Yes' || $LeaveAdjust[$k] == 'Yes' || $Correction[$k] == 'Yes')
			{
			 
			$query="INSERT INTO timeadjust_requests SET 
					EmpID = '" . dbinput($_SESSION['UserID']) . "',
					RosterID = '" . (int)$RosterID[$k] . "',
					NotificationTo = '" . dbinput($_SESSION['MySupervisor']) . "',
					LoginTime = '" . time_format_gracetime($ArrivalTime[$k]) . "',
					LogoutTime = '" . time_format_gracetime($DepartTime[$k]) . "',
					Reason = '" . dbinput($Reason[$k]) . "',
					WaveOff = '" . ($WaveOff[$k] == 'Yes' ? 1 : 0) . "',
					Correction = '" . ($Correction[$k] == 'Yes' ? 1 : 0) . "',
					LeaveAdjust = '" . ($LeaveAdjust[$k] == 'Yes' ? 1 : 0) . "',
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
    max-width: 270px;
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
    min-height: 420px;
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
    min-height:420px;
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
    min-height:420px;
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
.evidance-wrap{
    max-width: 188px;
    min-height: 100px;
    max-height: 100px;
    background: #e9e5e6;
}
/*hrms-second-screen-end*/
/*screen-5-start*/
.dashboard-wrap-5 .events-heading {
    margin-top: 40px;
    margin-bottom: 0;
    padding-left: 20px;
}
.dashboard-wrap-5 .events-heading p {
    color: #011832;
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 0;
}
.dashboard-wrap-5 .content-wrap-2 {
    display: flex;
    justify-content: space-between;
    padding: 20px 20px 30px 20px;
}
.adjust-text{
    color:#0066cc;
    font-size:26px;
    font-weight:600;
}
.pr-text{
    color:#000033;
    font-weight:500;
    font-size:15px;
}
.dashboard-wrap-5 .teams-wrap {
    padding: 0px 20px 40px 20px;
}
.dashboard-wrap-5 .teams-wrap .teamsDetails {
    display: flex;
    align-items: center;
}
.dashboard-wrap-5 .teams-wrap .teamsDetails .teamimg {
    width: 125px;
    height: 125px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #d2d0d0;
    background: #dee5f7;
}
.dashboard-wrap-5 .teams-wrap .teamsDetails .teamimg img {
    max-width: 125px;
    max-height: 125px;
    object-fit: cover;
    object-position: center;
}
.dashboard-wrap-5 .teams-wrap .teamsDetails .teams-name  {
    margin-left: 25px;
}
.dashboard-wrap-5 .teams-wrap .teamsDetails .teams-name .designation {
    color: #0066cc;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 0;
}
.dashboard-wrap-5 .btn {
    border-radius: 50px !important;
    min-width: auto;
    background-color: #091829 !important;
    border-color: #091829 !important;
    padding: 10px 35px;
    text-align: end;
}
.cta-btn {
    text-align: end;
    padding: 0 0px 10px 0;
}
.dashboard-wrap-5 {
    padding: 20px 20px 20px 20px;
}
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
.text-1{
    color:#000033;
    font-size:12px;
    font-weight:500;
    padding: 15px 16px !important;
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
.mt-30{
    margin-top:30px !important;
}
.pr-30{
    padding-left:30px;
}
@media only screen and (max-width:1440px) {
    .table-header tr th {
        font-size: 12px;
    }
}
@media only screen and (max-width:1366px) {
      .text-1 {
        font-size: 10px;
    }
    .table-header tr th {
        font-size: 10px;
    }
        .report-contentext {
        font-size: 13px;
    }
    .depart-text {
        font-size: 18px;
    }
    .creat-text {
        font-size: 14px;
    }
    .emp-textwrap {
        font-size: 14px;
    }
    .team-info-name {
        font-size: 12px;
    }
    .team-info-date {
        font-size: 10px;
    }
}

/*screen-5-end*/
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
      <h1 class="mt-30 pr-30"> Time Adjust Request</h1>
      <ol class="breadcrumb">
        <li><a href="MyAttendanceLedger.php"><i class="fa fa-dashboard"></i>My Attendance</a></li>
        <li class="active">Time Adjust Request</li>
      </ol>
    </section>
    <!-- Main content -->
    <form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" name="frmPage">
      <section class="content">
              <?php
		  		echo $msg;
				if(isset($_SESSION["msg"]))
				{
					echo $_SESSION["msg"];
					$_SESSION["msg"]="";
				}
				?>
          <!--screen-5-start-->
        <div class="dashboard-wrap-5">
            <div class="head-text-wrap">
                <!--profile-start-->
                <div class="teams-wrap">
                    <div class="teamsDetails">
                        <span class="teamimg">
                            <img src="css/images/prof.png" alt="profile image">
                        </span>
                        <div class="teams-name">
                        <p class="designation">Employe ID : <span class="pr-text">1257</span></p>
                        <p class="designation">Name : <span class="pr-text">Muhammad Sheeraz Khan</span></p>
                        <p class="designation">Designation : <span class="pr-text">Head of Creative Production</span></p>
                        <p class="designation">Depertment : <span class="pr-text">Creative - Production</span></p>
                        </div>
                    </div>
                </div>
                <!--profile-end-->
                <!--submit-btn-start-->
                <div class="cta-btn">
                    <button href="#!" class="btn">Submit</button>
                </div>
                <!--submit-btn-end-->
                <!--table-1-wrap-start-->
                <div class="table-content">
                    <p class="table-content-1">Adjusted Time Sheet Preview</p>
                    <div class="table-responsive table-rad">
                        <!--table-start-->
                        <table class="table table-dash table-hover">
                              <thead class="table-header">
                                <tr>
                                  <th scope="col" class="head-3">Date</th>
                                  <th scope="col">Standard Shift Time</th>
                                  <th scope="col" >Reported Time</th>
                                  <th scope="col">Status</th>
                                  <th scope="col">Type Of Adjustment</th>
                                  <th scope="col">Adjusted Shift Time</th>
                                  <th scope="col">Approval</th>
                                  <th scope="col">Remarks</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td scope="row" class="text-1">2/18/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">09:40 pm to 06:40 am</td>
                                  <td class="head-2 text-1">Late in</td>
                                  <td class="head-1 text-1">Correction</td>
                                  <td class="head-1 text-1">09:20 pm to 06:20 am</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-1">2/19/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">12:40 pm to 06:00 am</td>
                                  <td class="head-2 text-1">Half day</td>
                                  <td class="head-1 text-1">Sick leave (0.5 )</td>
                                  <td class="head-1 text-1">09:20 pm to 06:20 am</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-1">2/20/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">01:40 pm to 06:00 am</td>
                                  <td class="head-2 text-1">Half day</td>
                                  <td class="head-1 text-1">Sick leave (0.5 )</td>
                                  <td class="head-1 text-1">09:20 pm to 06:20 am</td>
                                  <td class="head-2 text-1 col-red">Disapproved</td>
                                  <td class="head-2 text-1">Provide Evidence</td>
                                </tr>
                                 <tr>
                                  <td scope="row" class="text-1">2/21/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">09:40 pm to 05:48 am</td>
                                  <td class="head-2 text-1">Late in / Half day</td>
                                  <td class="head-1 text-1">Sick leave</td>
                                  <td class="head-1 text-1">09:30 pm to 06:20 am</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-1">2/22/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">09:40 pm to 06:40 am</td>
                                  <td class="head-2 text-1">Late in</td>
                                  <td class="head-1 text-1">Correction</td>
                                  <td class="head-1 text-1">09:20 pm to 06:20 am</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-1">2/23/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">10:00 pm to 08:00 am</td>
                                  <td class="head-2 text-1">Late in</td>
                                  <td class="head-1 text-1">Waived off</td>
                                  <td class="head-2 text-1">9 Hour Completed</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-1">2/24/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-2 text-1">-</td>
                                  <td class="head-2 text-1">Absent</td>
                                  <td class="head-1 text-1">Sick leave </td>
                                  <td class="head-2 text-1">-</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                                <tr>
                                  <td scope="row" class="text-1">2/25/2022</td>
                                  <td class="head-1 text-1">09:00 pm to 06:00 am</td>
                                  <td class="head-1 text-1">09:40 pm to 05:48 am</td>
                                  <td class="head-2 text-1">Late in / Half day</td>
                                  <td class="head-1 text-1">Sick leave </td>
                                  <td class="head-1 text-1">09:30 pm to 06:20 am</td>
                                  <td class="head-2 text-1 col-green">Approved</td>
                                  <td class="head-2"></td>
                                </tr>
                              </tbody>
                        </table>
                        <!--table-end-->
                        
                    </div>
                    
                </div>
                <!--table-1-wrap-end-->
                <!--table-2-start-->
                  <div class="row">
                      <div class="col-12 col-md-8 col-lg-8">
                          
                            <div class="table-content">
                    <p class="table-content-1">Salary Status</p>
                    <div class="table-responsive table-rad">
                        <!--table-start-->
                        <table class="table table-dash table-hover">
                              <thead class="table-header">
                                <tr>
                                  <th scope="col" class="head-3">Current Salary</th>
                                  <th scope="col" >Deduction Salary</th>
                                  <th scope="col">Balance Salary without Deduction</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr>
                                  <td scope="row" class="text-1">PKR 250,000</td>
                                  <td class="head-1 text-1">PKR 12,500 <span class="day-timing">(0.5 Days)</span></td>
                                  <td class="head-1 text-1">PKR 237,500</td>
                               
                                </tr>
                              </tbody>
                        </table>
                        <!--table-end-->
                        
                    </div>
                    
                </div>
                      </div>
                      <div class="col-12 col-md-6 col-lg-6">
                          
                      </div>
                      
                  </div>
                <!--table-2-end-->
            </div> 
               
        </div>
        <!--screen-5-end-->  
				
				
		<div class="col-md-12">
          <div class="box">
          
            <!-- general form elements -->
            <div style="padding:15px;" class="box-primary">
            
              <!-- form start -->
              <div class="box-body">
				<div class="row">
				    <div class="col-md-12">
				        <table id="dataTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th width="90%"><b>Leaves Type</b></th>
					  <th><b>Entitled</b></th>
					  <th><b>Taken</b></th>
					  <th><b>Balance</b></th>
                    </tr>
                  </thead>
				  
                  <tbody>
                    <tr>
                      
                      
					  <td width="50%">Annual Leaves</td>
					  <td><?php echo $Anual ; ?></td>
                      <td class="defultBox" width="5%"><?php echo $TAnual ; ?></td>
					  <td><?php echo $CAnual ; ?></td>
                    </tr>
					<tr>
                      
                      
					  <td width="50%">Casual Leaves</td>
					  <td><?php echo $Casual ; ?></td>
                      <td class="defultBox" width="5%"><?php echo $TCasual ; ?></td>
					  <td><?php echo $CCasual ; ?></td>
                    </tr>
                    <tr>
                      
                      
					  <td width="50%">Sick Leaves</td>
					  <td><?php echo $Sick ; ?></td>
                      <td class="defultBox" width="5%"><?php echo $TSick ; ?></td>
					  <td><?php echo $CSick ; ?></td>
                    </tr>
                    <tr>
                      
                      
					  <td width="50%">Probationary Leaves</td>
					  <td><?php echo $Probation ; ?></td>
                      <td class="defultBox" width="5%"><?php echo $TProbation ; ?></td>
					  <td><?php echo $CProbation ; ?></td>
                    </tr>
                    <tr>
                      
                      
					  <td width="50%"><b>Total</b></td>
					  <td><?php echo ($Anual + $Casual + $Sick + $Probation) ; ?></td>
                      <td class="defultBox" width="5%"><?php echo ($TAnual + $TCasual + $TSick + $TProbation) ; ?></td>
					  <td><?php echo ($CAnual + $CCasual + $CSick + $CProbation) ; ?></td>
                    </tr>
                  </tbody>
                </table>
				    </div>
				</div>
              </div>
              <!-- /.box-body -->
            
            </div>
            <!-- /.box -->
            <!-- Form Element sizes -->
          </div>
        </div>
        <div class="col-md-12">
          <div class="box">
          
            <!-- general form elements -->
            <div style="padding:15px;" class="box-primary">
              <!-- form start -->
              <div class="box-body">
				<div class="row">
				    <div class="col-md-12">
				        <table id="dataTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th width="34%"><b>Current Salary</b></th>
					  <th width="33%"><b>Deduction Salary</b></th>
					  <th width="33%"><b>Balance Salary without extra Deductions</b></th>
                    </tr>
                  </thead>
				  
                  <tbody>
                    <tr>
					  <td width="34%">PKR <?php echo number_format($CurrentSalary); ?></td>
					  <td width="33%">PKR <?php echo number_format($deductionSalary); ?> (<?php echo ($TotalLateEarliesDedDays + $rosterhalfdays + $rosterabsent); ?> Days)</td>
					  <td width="33%">PKR <?php echo number_format($CurrentSalary - $deductionSalary); ?></td>
                    </tr>
                  </tbody>
                </table>
				    </div>
				</div>
              </div>
              <!-- /.box-body -->
            
            </div>
            <!-- /.box -->
            <!-- Form Element sizes -->
          </div>
        </div>
        <div class="col-md-12">
          <div class="box">
          
            <!-- general form elements -->
            <div style="padding:15px;" class="box-primary">
                <div class="box-header">
                    <b>Adjustment Criteria:</b>
                </div>
              <!-- form start -->
              <div class="box-body">
				<div class="row">
				    <div class="col-md-12">
				        <table id="dataTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th width="34%"><b>Late Ins</b></th>
					  <th width="33%"><b>Early Outs</b></th>
					  <th width="33%"><b>Half Days</b></th>
                    </tr>
                  </thead>
				  
                  <tbody>
                    <tr>
					  <td width="34%">4 late ins = 1 absent</td>
					  <td width="33%">3 early outs = 1 absent</td>
					  <td width="33%">1 half day = 0.5 absent</td>
                    </tr>
                    <tr>
					  <td colspan="3"><b>"Please check mark the adjustment and the reason boxes" before submit the request.</b></td>
                    </tr>
                  </tbody>
                </table>
				    </div>
				</div>
              </div>
              <!-- /.box-body -->
            
            </div>
            <!-- /.box -->
            <!-- Form Element sizes -->
          </div>
        </div>
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
        <div class="col-md-12">
          <div class="box">
          
            <!-- general form elements -->
            <div style="padding:15px;" class="box-primary">
            
              <!-- form start -->
              <div class="box-body">
				<div class="row">
				    <div class="col-md-1">
				        
        					<div class="form-group">
        						<label id="labelimp">Adjustment:</label>
        							<input type="checkbox" name="Adjust[<?php echo $i; ?>]" value="Yes" checked class="form-control" /> 
        							<input type="hidden" name="RosterID[<?php echo $i; ?>]" value="<?php echo $row["ID"]; ?>" class="form-control" /> 
        					</div><!-- /.form group -->

				    </div>
				    <div class="col-md-1">
				        
        					<div class="form-group">
        						<label id="labelimp">Date:</label>
        							  <?php echo dboutput($row["Day"])."<br>".dboutput($row["Date"]); ?>
        					</div><!-- /.form group -->

				    </div>
				    <div class="col-md-1">
				        
        					<div class="form-group">
        						<label id="labelimp">Status:</label>
        						<?php echo ($row["HalfDay"] == 1 ? 'HalfDay<br>' : '' ); ?><?php echo ($row["Late"] == 1 ? 'Late<br>' : '' ); ?><?php echo ($row["EarlyDep"] == 1 ? 'Early<br>' : '' ); ?><?php echo dboutput($row["Status"]); ?>	 
        					</div><!-- /.form group -->

				    </div>
				    <div class="col-md-1">
        					<div class="form-group">
        						<label id="labelimp">Arrive:</label>
        							<?php echo revert_time_format_gracetime($row["LoginAdjust"]); ?>
        					</div><!-- /.form group -->
        					<div class="form-group">
        						<label id="labelimp">Schedule Arrive:</label>
        							<?php echo revert_time_format_gracetime($row["ScheduleArrivalTime"]); ?>
        					</div><!-- /.form group -->
				    </div>
				    <div class="col-md-1">
        					<div class="form-group">
        						<label id="labelimp">Depart:</label>
        							<?php echo revert_time_format_gracetime($row["LogoutAdjust"]); ?>
        					</div><!-- /.form group -->
        					<div class="form-group">
        						<label id="labelimp">Schedule Depart:</label>
        							<?php echo revert_time_format_gracetime($row["ScheduleDepartTime"]); ?>
        					</div><!-- /.form group -->
				    </div>
				    <div class="col-md-7">
				        <div class="form-group">
                          <label id="labelimp" class="labelimp" for="Reason">Reason:</label>
                          <?php 
        					echo '<textarea rows="5" maxlength="400" id="Reason" name="Reason['.$i.']" class="form-control">'.($waveOffAdjust == 1 ? '9 hours completed' : '').'</textarea>';
        				  ?>
                        </div>
				    </div>
				    <div class="col-md-12">
				        <div class="form-group">
                          <hr>
                        </div>
				    </div>
				    <div class="col-md-1">
        					<div class="form-group">
        						<label id="labelimp">Leave Adjust:</label>
        						    <?php //if($row["HalfDay"] == 1 || $row["Status"] == 'Absent'){ ?>
        							<input type="radio" name="AdjustmentType[<?php echo $i; ?>]" value="Yes" class="form-control" /> 
        							<?php //} ?>
        					</div><!-- /.form group -->
        					<div class="form-group">
        							<p><input type="radio" name="LeaveType[<?php echo $i; ?>]" value="Annual" checked class="form-control" /> Annual</p>
        							<p><input type="radio" name="LeaveType[<?php echo $i; ?>]" value="Casual" class="form-control" /> Casual</p>
        							<p><input type="radio" name="LeaveType[<?php echo $i; ?>]" value="Sick" class="form-control" /> Sick</p>
        							<p><input type="radio" name="LeaveType[<?php echo $i; ?>]" value="Probationary" class="form-control" /> Probationary</p>
        					</div><!-- /.form group -->
				    </div>
				    <div class="col-md-1">
        					<div class="form-group">
        						<label id="labelimp">Waive Off:</label>
        						<?php 
        						
				// 			if($waveOffAdjust == 1)
				// 			{
							   ?>
							   <input type="radio" name="AdjustmentType[<?php echo $i; ?>]" value="Yes" checked class="form-control" />
							   <?php
							    
				// 			}
        						?>
        					</div><!-- /.form group -->
				    </div>
				    <div class="col-md-1">
        					<div class="form-group">
        						<label id="labelimp">Correction:</label>
        							<input type="radio" for="div<?php echo $i; ?>" name="AdjustmentType[<?php echo $i; ?>]" value="Yes" class="form-control" /> 
        					</div><!-- /.form group -->
				    </div>
				    <div id="div<?php echo $i; ?>">
				    <div class="col-md-1">
				        <div class="bootstrap-timepicker">
        					<div class="form-group">
        						<label id="labelimp" id="ArrivalTime">Arrive:</label>
        							<input type="text" name="ArrivalTime[<?php echo $i; ?>]" id="ArrivalTime" <?php echo 'value="'.revert_time_format_gracetime($row["LoginAdjust"]).'"' ?> class="form-control timepicker" />
        					</div><!-- /.form group -->
        				</div>
				    </div>
				    <div class="col-md-1">
				        <div class="bootstrap-timepicker">
        					<div class="form-group">
        						<label id="labelimp" id="DepartTime">Depart:</label>
        							<input type="text" name="DepartTime[<?php echo $i; ?>]" id="DepartTime" <?php echo 'value="'.revert_time_format_gracetime($row["LogoutAdjust"]).'"' ?> class="form-control timepicker" />
        					</div><!-- /.form group -->
        				</div>
				    </div>
				    <div class="col-md-2">
				        <div class="bootstrap-timepicker">
        					<div class="form-group">
        						<label id="labelimp">Evidence (jpg or png):</label>
        							<input type="file" id="document" name="Evidence[<?php echo $i; ?>]" />
        					</div><!-- /.form group -->
        				</div>
				    </div>
				    <div class="col-md-4">
        					<div class="form-group">
        						<label id="labelimp"><b>* without evidence, correction will not be accepted.</b></label>
        					</div><!-- /.form group -->
				    </div>
				    </div>
				</div>
				
                <input type="hidden" name="action" value="submit_form" />
              </div>
              <!-- /.box-body -->
            
            </div>
            <!-- /.box -->
            <!-- Form Element sizes -->
          </div>
        </div>
        <?php
        $i++;
			}
		?>
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
