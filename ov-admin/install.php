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
    <title>Install openvoter 3.2</title>
	<link rel="stylesheet" type="text/css" href="/ov-admin/css/master.css" />
	<script type="text/javascript" src="/js/jquery-1.6.4.min.js"></script>
</head>
<body>
	<div class="content">
		<div class="margin_tb_10 align_center"><img src="/ov-admin/img/openvoter-logo.png" alt="" /></div>	
		<?php if (isset($_GET['success']) && $_GET['success'] == "yes") { ?>
			<h1>Installation Complete</h1>
			<h6>OpenVoter 3.2 Has Been Successfully Installed on Your Site.</h6>
			<?php DeleteInstallUpdateScripts(); ?>
			<div class="margin_tb_10">
				<div class="padding_tb_5">Here is your login information for the admin panel</div>
				<div class="padding_tb_5" style="font-size:14px"><strong>Username:</strong> admin</div>
				<div class="padding_tb_5" style="font-size:14px"><strong>Password:</strong> <?php echo $_SESSION['admin_password']; ?></div>
				<div class="padding_tb_5" style="font-size:14px">
					Go Here to Login:<br/>
					<a href="/ov-admin/login" target="_blank"><?php echo $_SESSION['root_url']; ?>/ov-admin/login</a>
				</div>
			</div>
		<?php } elseif (CheckInstall()) { ?>
			<h1 style="color:#3a3ad2">Installation Complete</h1>
			<h6>OpenVoter Is Already Installed on Your Site.</h6>
		<?php } else { ?>
			<div id="install-form">
				<h1 style="color:#3a3ad2">Install openvoter 3.2</h1>
				<h6>Fill out the fields to get openvoter 3.2 Installed on Your Site.</h6>
				<?php
					include 'ov-config.php';
					$error_message = "";
					$is_installed = false;
			
					if (DB_NAME == '' || DB_USERNAME == '' || DB_PASSWORD == '' || DB_HOST == '' || SITE_ID == '') {
						// MUST SET THESE
						$error_message = "Site Variables Not Set, You Must Set DB_NAME, DB_USERNAME, DB_PASSWORD, DB_HOST, and SITE_ID. Please check the ovconfig.php file.<br/>";
					}
			
					$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
					if ($mysqli->connect_error) {
						$error_message .= "---------<br/>Error connecting to MySQL Database, please make sure that the ovconfig.php file has the proper credentials.";
					}
					$mysqli->close();
				?>
			
				<?php				
					if ($error_message != "") {
						echo "<div class=\"error_div\">$error_message</div>";
					}
			
					if (isset($_GET['error'])) {
						if ($_GET['error'] == "already_installed") {
							$error_message = "OpenVoter 3.0 is Already Installed";
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
			
				<form action="/ov-admin/php/site_install.php" method="post" class="uniForm">
						<div class="form-field">
							<label for="root_url">Root URL</label><br/>
							<input type="text" id="root_url" name="root_url" size="35" class="textInput">
							<p class="form-hint">This the full URL of your site (e.g. http://www.example.com)</p>
						</div>

						<div class="form-field">
							<label for="site_title">Title</label><br/>
							<input type="text" id="site_title" name="site_title" size="35" class="textInput">
							<p class="form-hint">This is your site's title.</p>
						</div>
				
						<div class="form-field">
							<label for="admin_email">Email</label><br/>
							<input type="text" id="admin_email" name="admin_email" size="35" class="textInput">
							<p class="form-hint">This is your email address to set up your admin account.</p>
						</div>

						<div class="button-field">
							<button type="submit" <?php if ($error_message != "") { echo "disabled"; } ?> class="normal-button">Install</button>
						</div>
				</form>
			</div>
		<?php } ?>
	</div>
</body>
</html>

<?php
	function CheckInstall()
	{
		$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
		if ($mysqli->connect_error) {
			return false;
		}
		
		$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'å AND table_name = '" . DB_PREFIX . "site'";
		$result = $mysqli->query($query);
		
		if ($result) {
			if ($result->num_rows > 0) {
				$query = "SELECT title FROM " . DB_PREFIX . "site WHERE id = " . SITE_ID;
				$result = $mysqli->query($query);
				if ($result->num_rows > 0) {
					$mysqli->close();
					return true;
				} else {
					$mysqli->close();
					return false;
				}
			} else {
				$mysqli->close();
				return false;
			}
		} else {
			$mysqli->close();
			return true;
		}
	}
	
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