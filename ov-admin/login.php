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

	if (isset($_GET['redirecturl'])) {
		$redirect_url = $_GET['redirecturl'];
	} else {
		$redirect_url = "/ov-admin";
	}
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Login | Admin Console | <?php echo $ovSettings->Title(); ?></title>
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
		<div style="margin:20px 0">
			<div class="align_center margin_b_10"><img src="/ov-admin/img/openvoter-logo.png" alt="OpenVoter" /></div>
			<div class="login_form"><div class="login_form_content">
				<form action="/ov-admin/php/login_admin.php" method="post">
					<table width="500" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:0">
						<?php
							if (isset($_GET['error'])) {
								if ($_GET['error'] == 1) { $message = "Invalid Username or Password"; }	
						?>
								<tr>
									<td width="100">&nbsp;</td>
									<td width="400" class="error_text"><?php echo $message; ?></td>
								</tr>
						<?php
							}
						?>
						<tr>
							<td width="100"><label for="username">Username</label></td>
							<td width="400"><input type="text" class="textbox_22" style="width:100%" id="username" name="username" /></td>
						</tr>
						<tr>
							<td width="100"><label for="password">Password</label></td>
							<td width="400"><input type="password" class="textbox_22" style="width:100%" maxlength="20" id="password" name="password" /></td>
						</tr>
						<tr>
							<td width="100">&nbsp;</td>
							<td width="400"><input type="checkbox" class="textbox_22" id="remember" name="remember" value="yes" />&nbsp;&nbsp;<label for="remember">Remember Login</label></td>
						</tr>
						<tr>
							<td width="100">&nbsp;</td>
							<td width="400"><input type="submit" id="submit" name="submit" value="Log In" class="normal-button" /></td>
						</tr>
					</table>
					<input type="hidden" value="<?php echo $redirect_url; ?>" name="redirect_url" />
				</form>
			</div></div>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
</body>
</html>