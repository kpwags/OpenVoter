<?php 
	session_start();
	ini_set("include_path", ".:./:./ov-include:./../ov-include:./../../ov-include:./ov-admin/ov-include:./../ov-admin/ov-include:./../:./../../:./usercontrols:./../usercontrols:./../../usercontrols");
	ini_set('display_errors', 0);
	
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
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Update openvoter 3.2</title>
	<link rel="stylesheet" type="text/css" href="/ov-admin/css/master.css" />
	<script type="text/javascript" src="/js/jquery-1.6.4.min.js"></script>
</head>
<body>
	<div class="content">
		<div class="margin_tb_10 align_center"><img src="/ov-admin/img/openvoter-logo.png" alt="" /></div>
			
			<?php if (isset($_GET['success']) && $_GET['success'] == "yes") { ?>
				<h1>Update Complete</h1>
				<h6>OpenVoter 3.2 Has Been Successfully Installed on Your Site.</h6>
				<?php DeleteInstallUpdateScripts(); ?>
				<div class="margin_tb_10">
					
				</div>
			<?php } else { ?>
				<h1 class="align_center">Update OpenVoter to 3.2</h1>
			
				<?php				
					if (isset($_GET['error'])) {
						if ($_GET['error'] == "already_installed") {
							$error_message = "OpenVoter 3.2 is Already Installed";
						} elseif ($_GET['error'] == "missing_fields") {
							$error_message = "All Fields are Required";
						} else {
							$error_message = "Unknown Error.";
						}
					
						if ($error_message != "") {
							echo "<div class=\"error_div\">$error_message</div>";
						}
					}
				?>
				<form action="/ov-admin/php/site_update.php" method="post" class="margin_tb_20">
					<div class="align_center"><button type="submit" <?php if ($error_message != "") { echo "disabled"; } ?> class="normal-button">Update</button></div>
				</form>
			<?php } ?>
		</div>
	</div>
</body>
</html>

<?php
	function DeleteInstallUpdateScripts()
	{
		if (file_exists('./php/site_install.php')) {
			@unlink('./php/site_install.php');
		}
		
		if (file_exists('./php/site_update.php')) {
			@unlink('./php/site_update.php');
		}
	}

?>