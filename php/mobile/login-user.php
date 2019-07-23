<?php
	ini_set("include_path", ".:./:./../:./../../:./ov-include:./../ov-include:./../../ov-include");
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

	$user_identifier = strip_tags($_POST['username']);
	$password = $_POST['password'];
	$previous_page = $_POST['previous_page'];
	
	if ($_POST['remember'] == "yes") {
		$remember = true;
	} else {
		$remember = false;
	}
	
	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();
	
	$login_result = $ovUserSecurity->CheckLogin($user_identifier, $password, $remember);
	
	switch ($login_result)
	{
		// find out what the login result was and act accordingly
		case "SUSPENDED":
			header("LOCATION: /m/login?error=2&redirecturl=" . urlencode($previous_page));
			exit();
			break;
		case "BANNED":
			header("LOCATION: /m/login?error=3&redirecturl=" . urlencode($previous_page));
			exit();
			break;
		case "IP BANNED":
			header("LOCATION: /m/login?error=4&redirecturl=" . urlencode($previous_page));
			exit();
			break;
		case "INVALID":
			header("LOCATION: /m/login?error=1&redirecturl=" . urlencode($previous_page));
			exit();
			break;
		case "OK":
			header("LOCATION: $previous_page");
			exit();
			break;
		case "UNKNOWN":
			header("LOCATION: /m/login?error=1&redirecturl=" . urlencode($previous_page));
			exit();
			break;
	}
	
?>