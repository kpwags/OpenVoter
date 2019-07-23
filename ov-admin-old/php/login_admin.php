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

	$debug_mode = false;

	$username = strip_tags($_POST['username']);
	$password = $_POST['password'];
	$redirect_url = $_POST['redirect_url'];
	
	if (isset($_POST['remember']) && $_POST['remember'] == "yes") {
		$remember = true;
	} else {
		$remember = false;
	}
	
	if ($debug_mode) {
		echo "<p>POST VALUES</p>";
		echo "<div>USERNAME: $username</div>";
		echo "<div>PW: $password</div>";
		echo "<div>REDIRECT: $redirect_url</div>";
		echo "<div>REMEMBER: $remember</div>";
	}
	
	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	if ($debug_mode) { echo "<p>PAST REQUIRE</p>"; }
	
	$login_result = $ovAdminSecurity->CheckLogin($username, $password, $remember);
	
	if ($debug_mode) { echo "<p>GOT LOGIN RESULT</p>"; }

	switch ($login_result)
	{
		// find out what the login result was and act accordingly
		case "INVALID":
			header("LOCATION: /ov-admin/login?error=1&redirecturl=" . urlencode($redirect_url));
			exit();
			break;
		case "OK":
			header("LOCATION: $redirect_url");
			exit();
			break;
		case "UNKNOWN":
		default:
			header("LOCATION: /ov-admin/login?error=1&redirecturl=" . urlencode($redirect_url));
			exit();
			break;
	}
	
?>