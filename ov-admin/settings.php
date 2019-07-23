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
	
	if (!$ovAdminSecurity->CanAccessPreferences()) {
		header("Location: /ov-admin");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadminsettings.php';
	$ovAdminSettings = new ovAdminSettings();
	
	if (isset($_GET['page'])) {
		$settings_page = $_GET['page'];
	} else {
		$settings_page = "base";
	}
	
	switch($settings_page) {
		case "karma":
			$karma_settings = $ovAdminSettings->GetKarmaSettings();
			break;
		case "algorithm":
			$algorithm_settings = $ovAdminSettings->GetAlgorithmSettings();
			break;
		case "submission":
			$submission_settings = $ovAdminSettings->GetSubmissionSettings();
			break;
		case "captcha":
			$captcha_settings = $ovAdminSettings->GetCaptchaSettings();
			break;
		case "policies":
			$policies = $ovAdminSettings->GetPolicies();
			break;
		case "ads":
			$ads = $ovAdminSettings->GetAds();
			break;
		case "comments":
			$comment_settings = $ovAdminSettings->GetCommentSettings();
			break;
		case "base":
		default:
			$base_settings = $ovAdminSettings->GetBaseSettings();
			break;
	}
	
	$current_section = "settings";
	$current_page = $settings_page;
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
	<script type="text/javascript" src="/js/tinymce/tiny_mce.js"></script>
</head>
<body>
	<?php include 'admin_header.php'; ?>
	<div class="content">
		<hr class="space"/>
		<div id="sidebar"><?php include 'admin_sidebar.php'; ?></div>
		<div id="main-content">
			<?php
				switch($settings_page) {
					case "karma":
						include 'settings/karma.php';
						break;
					case "algorithm":
						include 'settings/algorithm.php';
						break;
					case "submission":
						include 'settings/submission.php';
						break;
					case "captcha":
						include 'settings/captcha.php';
						break;
					case "policies":
						include 'settings/policies.php';
						break;
					case "ads":
						include 'settings/ads.php';
						break;
					case "comments":
						include 'settings/comment.php';
						break;
					case "themes":
						include 'settings/themes.php';
						break;
					case "base":
					default:
						include 'settings/base.php';
						break;
				}
			?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
</body>
</html>