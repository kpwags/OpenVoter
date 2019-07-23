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
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();
	
	if (!$ovAdminSecurity->IsAdminLoggedIn()) {
		header("Location: /ov-admin/login?redirecturl=/ov-admin/feedback");
		exit();
	}
	
	if (!$ovAdminSecurity->CanAccessContent()) {
		header("Location: /ov-admin");
		exit();
	}

	if (isset($_GET['id'])) {
		$feedback_id = $_GET['id'];
	}
	
	$current_section = "feedback";
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
			<?php
				if (isset($feedback_id)) {
					include 'feedback/message.php';
				} else {
					include 'feedback/message_list.php';
				}
			?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
</body>
</html>