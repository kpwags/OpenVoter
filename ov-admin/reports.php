<?php
	ini_set("include_path", ".:./:./ov-include:./../ov-include:./../../ov-include:./ov-admin/ov-include:./../ov-admin/ov-include:./../:./../../:./usercontrols:./../usercontrols:./../../usercontrols");
	
	/*
		Copyright 2008-2010 OpenVoter
		
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
	
	if (!$ovAdminSecurity->CanAccessContent()) {
		header("Location: /ov-admin");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadminbans.php';
	$ovAdminBans = new ovAdminBans();
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();
	
	if (isset($_GET['id'])) {
		$is_details_page = true;
		$report_id = $_GET['id'];
	} else {
		$is_details_page = false;
	}
	
	if (isset($_GET['type'])) {
		$reports_page = $_GET['type'];
	} else {
		$reports_page = "submission";
	}
	
	if (!$is_details_page) {
		switch($reports_page) {
			case "comment":
				$reports = $ovAdminReporting->GetCommentReports();
				break;
			case "user":
				$reports = $ovAdminReporting->GetUserReports();
				break;
			case "submission":
			default:
				$reports = $ovAdminReporting->GetSubmissionReports();
				break;
		}
	}
	
	$current_section = "reports";
	$current_page = $reports_page;
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
			<?php
				if ($is_details_page) {
					switch($reports_page) {
						case "comment":
							include 'reports/comment_details.php';
							break;
						case "user":
							include 'reports/user_details.php';
							break;
						case "submission":
						default:
							include 'reports/submission_details.php';
							break;
					}
				} else {
					switch($reports_page) {
						case "comment":
							include 'reports/comments.php';
							break;
						case "user":
							include 'reports/users.php';
							break;
						case "submission":
						default:
							include 'reports/submissions.php';
							break;
					}
				}
			?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
	<div class="modal_form" title="Ban Domain" id="add_banned_domain">
		<div class="margin_tb_15">
			<label for="domain_name">Domain Name</label>
			<br/>
			<input type="text" name="domain_name" id="domain_name" value="" maxlength="255" style="width:402px"/>
		</div>
		<div class="margin_tb_15">
			<label for="ban_reason">Reason for Ban</label>
			<br/>
			<textarea id="ban_domain_reason" name="ban_domain_reason" style="height:100px;width:100%" class="limit255" charsleft="add_ban_chars_left"></textarea>
			<div class="align_right" id="add_ban_chars_left">255 characters remaining</div>
		</div>

		<div class="align_right">
			<button onclick="AddBannedDomain()">Add</button>
		</div>
	</div>
	
	<div class="modal_form" id="ban_user_form" title="Ban User">
		<form action="/ov-admin/php/ban_user.php" method="post">
			<h4>Ban <span id="ban_username"></span></h4>
			<div class="margin_tb_15">
				<label for="ban_user_reason">Reason for Ban</label>
				<br/>
				<textarea id="ban_user_reason" name="ban_user_reason" style="height:100px;width:100%" class="limit255" charsleft="ban_user_reason_chars_left"></textarea>
				<div class="align_right" id="ban_user_reason_chars_left">255 characters remaining</div>
			</div>
			<div class="align_right">
				<input type="hidden" id="ban_user_id" name="ban_user_id" />
				<button type="submit">Ban</button>
			</div>
		</form>
	</div>
</body>
</html>