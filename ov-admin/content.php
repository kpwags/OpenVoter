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
	
	if (!$ovAdminSecurity->CanAccessContent()) {
		header("Location: /ov-admin");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();
	
	require_once 'ovadminbans.php';
	$ovAdminBans = new ovAdminBans();
	
	require_once 'ovutilities.php';
	$ovUtilities = new ovUtilities();
	
	if (isset($_GET['id'])) {
		$details_page = true;	
		$content_id = $_GET['id'];
	} else {
		$details_page = false;
	}
	
	if (isset($_GET['type'])) {
		$content_page = $_GET['type'];
	} else {
		$content_page = "submission";
	}
	
	$current_section = "content";
	$current_page = $content_page;
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
				if ($details_page) {
					switch($content_page) {
						case "comment":
							include 'content/comment_details.php';
							break;
						case "user":
						case "suspended_user":
							include 'content/user_details.php';
							break;
						case "submission":
						default:
							include 'content/submission_details.php';
							break;
					}
				} else {
					switch($content_page) {
						case "comment":
							include 'content/comments.php';
							break;
						case "user":
							include 'content/users.php';
							break;
						case "suspended_user":
							include 'content/suspended_users.php';
							break;
						case "submission":
						default:
							include 'content/submissions.php';
							break;
					}
				}
			?>
		</div>
		<div class="clearfix"></div>
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
			<textarea ID="ban_domain_reason" name="ban_domain_reason" style="height:100px;width:100%" class="limit255" charsleft="add_ban_chars_left"></textarea>
			<div class="align_right" id="add_ban_chars_left">255 characters remaining</div>
		</div>

		<div class="align_right">
			<button onclick="AddBannedDomain()" class="normal-button">Add</button>
		</div>
	</div>
	
	<div class="modal_form" id="edit_submission_form" title="Edit Submission">
		<form action="/ov-admin/php/edit_submission.php" method="post">
			<div class="margin_tb_15">
				<label for="edit_title">Title</label>
				<br/>
				<input type="text" name="edit_title" id="edit_title" maxlength="255" style="width:402px"/>
			</div>
			<div class="margin_tb_15">
				<label for="edit_submission_url">URL</label>
				<br/>
				<input type="text" name="edit_submission_url" id="edit_submission_url" maxlength="255" style="width:402px"/>
			</div>
			<div class="margin_tb_15">
				<label for="edit_summary">Summary</label>
				<br/>
				<textarea ID="edit_summary" name="edit_summary" style="height:100px;width:100%" class="limit255" charsleft="edit_submission_chars_left"></textarea>
				<div class="align_right" id="edit_submission_chars_left">255 characters remaining</div>
			</div>
			<div class="align_right">
				<input type="hidden" id="edit_submission_id" name="edit_submission_id" />
				<button type="submit" class="normal-button">Save Changes</button>
			</div>
		</form>
	</div>
	
	<div class="modal_form" id="ban_user_form" title="Ban User">
		<form action="/ov-admin/php/ban_user.php" method="post">
			<h4>Ban <span id="ban_username"></span></h4>
			<div class="margin_tb_15">
				<label for="ban_user_reason">Reason for Ban</label>
				<br/>
				<textarea ID="ban_user_reason" name="ban_user_reason" style="height:100px;width:100%" class="limit255" charsleft="ban_user_reason_chars_left"></textarea>
				<div class="align_right" id="ban_user_reason_chars_left">255 characters remaining</div>
			</div>
			<div class="align_right">
				<input type="hidden" id="ban_user_id" name="ban_user_id" />
				<button type="submit" class="normal-button">Ban</button>
			</div>
		</form>
	</div>
	
	<div class="modal_form" id="reset_password_form" title="Reset User Password">
		<form action="/ov-admin/php/reset_user_password.php" method="post" onsubmit="return ValidateResetUserPassword()">
			<h4>Reset Password For <span id="reset_pw_user"></span></h4>
			<div class="margin_tb_15">
				<label for="reset_password_1">Password</label>
				<br/>
				<input type="password" name="reset_password_1" id="reset_password_1" style="width:402px" />
				<div class="error_text" id="pw1_error" style="display:none"></div>
			</div>
			<div class="margin_tb_15">
				<label for="reset_password_2">Re-Enter Password</label>
				<br/>
				<input type="password" name="reset_password_2" id="reset_password_2" style="width:402px" />
				<div class="error_text" id="pw2_error" style="display:none"></div>
			</div>
			<div class="align_right">
				<input type="hidden" id="reset_pw_user_id" name="reset_pw_user_id" />
				<button type="submit" class="normal-button">Reset Password</button>
			</div>
		</form>
	</div>
</body>
</html>