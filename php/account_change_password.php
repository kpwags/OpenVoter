<?php
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
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

	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();

	$current_password = $_POST['current_password'];
	$new_password_1 = $_POST['new_password_1'];
	$new_password_2 = $_POST['new_password_2'];
	
	if ($ovUserSecurity->IsUserLoggedIn())
	{
		require_once 'ovouser.php';
		$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
		
		$result = $ovoUser->ChangePassword($current_password, $new_password_1, $new_password_2);
		
		switch ($result) {
			case "OK":
				header("Location: /settings/password?success");
				exit();
				break;
			case "invalidpassword":
				header("Location: /settings/password?error=1");
				exit();
				break;
			case "invalidlength":
				header("Location: /settings/password?error=2");
				exit();
				break;
			case "nomatch":
				header("Location: /settings/password?error=3");
				exit();
				break;
			case "error":
			default:
				header("Location: /settings/password?error=4");
				exit();
				break;
		}
	}
	else
	{
		header("Location: /login?redirecturl=" . urlencode("/settings/password"));
		exit();
	}
?>