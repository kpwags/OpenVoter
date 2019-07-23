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
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	if (!$ovAdminSecurity->IsAdminLoggedIn()) {
		header("Location: /ov-admin/login?redirecturl=/ov-admin/voting-report");
		exit();
	}
	
	if (!$ovAdminSecurity->CanAccessContent()) {
		header("Location: /ov-admin");
		exit();
	}
	
	$current_section = "content";
	$current_page = "voting";
	
	if (isset($_GET['username'])) {
		$username = $_GET['username'];
	} else {
		$username = false;
	}
	
	if (isset($_GET['type'])) {
		$type = $_GET['type'];
	} else {
		$type = "submission";
	}
	
	if (isset($_GET['direction'])) {
		$direction = $_GET['direction'];
	} else {
		$direction = 0;
	}
	
	if (isset($_GET['start_date'])) {
		$start_date = $_GET['start_date'];
	} else {
		$start_date = '';
	}
	
	if ($username) {
		if ($type == "comment") {
			$record = $ovAdminContent->GetCommentVotingRecord($username, $start_date, $direction);
		} else {
			$record = $ovAdminContent->GetSubmissionVotingRecord($username, $start_date, $direction);
		}
	}
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
	<script type="text/javascript">
		$(document).ready(function() {
			$("#start_date").datepicker({ dateFormat: 'yy-mm-dd' });
		});
	</script>
</head>
<body>
	<?php include 'admin_header.php'; ?>
	<div class="content">
		<div id="sidebar"><?php include 'admin_sidebar.php'; ?></div>
		<div id="main-content">
			<div class="search_form">
				<form action="#" method="get">
					Username:&nbsp;&nbsp;<input type="text" size="15" name="username" id="username" class="textbox_16" value="<?php echo $username; ?>" />
					<span class="search_field">
						Type:&nbsp;&nbsp;
						<select name="type">
							<option value="submission" <?php if($type == "submission") { echo "selected"; } ?>>Submission</option>
							<option value="comment" <?php if($type == "comment") { echo "selected"; } ?>>Comment</option>
						</select>
					</span>
					<span class="search_field">
						Dir:&nbsp;&nbsp;
						<select name="direction">
							<option value="0" <?php if($direction == "0") { echo "selected"; } ?>>All</option>
							<option value="1" <?php if($direction == "1") { echo "selected"; } ?>>Up</option>
							<option value="-1" <?php if($direction == "-1") { echo "selected"; } ?>>Down</option>
						</select>
					</span>
					<span class="search_field">
						From:&nbsp;&nbsp;<input type="text" size="10" name="start_date" id="start_date" class="textbox_16" value="<?php echo $start_date; ?>" />
					</span>
					<input type="submit" name="submit" value="Go" />
				</form>
			</div>
			<?php if ($username && $record) { ?>
				<?php
					if ($type == "comment") {
						include 'voting_record/comments.php';
					} else {
						include 'voting_record/submissions.php';
					}
				?>
			<?php } else { ?>
				<div class="margin_tb_20 error_text">No Record Found</div>
			<?php } ?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
</body>
</html>