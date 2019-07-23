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

	$details = strip_tags($_POST['details']);
	$email = strip_tags($_POST['email_address']);
	$website = strip_tags($_POST['website']);
	$location = strip_tags($_POST['location']);
	$twitter_username = strip_tags($_POST['twitter_username']);
	
	if ($ovUserSecurity->IsUserLoggedIn())
	{
		require_once 'ovouser.php';
		$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
		
		$emailRegex = "/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/";
		if (!preg_match($emailRegex, $email)) {
			// invalid email
			header("Location: /settings/profile?error=1");
			exit();
		}
		
		$ovoUser->SaveProfile($details, $email, $website, $location, $twitter_username);
		
		header("Location: /settings/profile?success");
		exit();
	}
	else
	{
		header("Location: /login?redirecturl=" . urlencode("/settings/profile"));
		exit();
	}
?>