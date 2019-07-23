<?php
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
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

	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();

	if ($ovUserSecurity->IsUserLoggedIn())
	{
		$password = $_POST['confirm_password'];
		
		if ($ovUserSecurity->ConfirmPassword($password)) {
			$ovUserSecurity->DeleteAccount($password);
			header("Location: /");
			exit();
		} else {
			// invalid password
			header("Location: /account/delete-account/error/1");
			exit();
		}
	}
	else
	{
		header("Location: /login?redirecturl=" . urlencode("/account/delete"));
		exit();
	}
?>