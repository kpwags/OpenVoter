<?php
	ini_set("include_path", ".:./:./ov-include:./../ov-include:./../../ov-include:./ov-admin/ov-include:./../ov-admin/ov-include:./../:./../../:./usercontrols:./../usercontrols:./../../usercontrols");
	
	/*
		Copyright 2008-2011 OpenVoter
		
		This file is part of OpenVoter.
	
		OpenVoter is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, version 3.
	
		OpenVoter is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.
	
		You should have received a copy of the GNU General Public License
		along with OpenVoter.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	require_once 'ov-config.php';
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	if (!$ovAdminSecurity->IsAdminLoggedIn()) {
		header("Location: /ov-admin/login");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();
	
	$new_feedback = $ovAdminContent->GetUnreadFeedbackCount();
	$new_reports = $ovAdminReporting->GetReportCount();
	
	$current_section = "";
	$current_page = "";
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Admin Console | <?php echo $ovSettings->Title(); ?></title>
	<link rel="stylesheet" href="/css/jquery-ui/jquery-ui-1.8.5.custom.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="/ov-admin/css/master.css" />
	<link rel="shortcut icon" href="/img/favicon.ico" />
	<script type="text/javascript" src="/js/jquery-1.6.4.min.js"></script>
	<script type="text/javascript" src="/js/jquery-ui-1.8.5.custom.min.js"></script>
	<script type="text/javascript" src="/ov-admin/js/openvoteradmin.js"></script>
</head>
<body>
	<?php include 'admin_header.php'; ?>
	<div class="content">

		<div id="sidebar"><?php include 'admin_sidebar.php'; ?></div>
		<div id="main-content">
			<div class="align_center margin_b_10"><img src="/ov-admin/img/openvoter-logo.png" alt="OpenVoter" /></div>
			
			<h1>Alerts</h1>
			
			<?php if ($new_feedback > 1) { ?>
				<div class="home_alert"><a href="/ov-admin/feedback" title="Feedback"><?php echo $new_feedback; ?> New Feedback Messages</a></div>
			<?php } elseif ($new_feedback == 1) { ?>
				<div class="home_alert"><a href="/ov-admin/feedback" title="Feedback">1 New Feedback Message</a></div>
			<?php } ?>
			
			<?php if ($new_reports > 1) { ?>
				<div class="home_alert"><a href="javascript:ShowAdminSidebar('sub_reports')" title="Reports"><?php echo $new_reports; ?> New Moderation Reports</a></div>
			<?php } elseif ($new_reports == 1) { ?>
				<div class="home_alert"><a href="javascript:ShowAdminSidebar('sub_reports')" title="Reports">1 New Moderation Report</a></div>
			<?php } ?>
			
			<?php if ($new_reports == 0 && $new_feedback == 0) { ?>
				<div class="margin_tb_20">No New Alerts</div>
			<?php } ?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
</body>
</html>