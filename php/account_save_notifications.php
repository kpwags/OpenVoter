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

	$alert_comments = $_POST['alert_comments'];
	$alert_shares = $_POST['alert_shares'];
	$alert_messages = "SITE";
	$alert_followers = $_POST['alert_followers'];
	$alert_favorites = $_POST['alert_favorites'];
	
	if ($ovUserSecurity->IsUserLoggedIn())
	{
		require_once 'ovouser.php';
		$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
		
		$ovoUser->SaveNotificationSettings($alert_comments, $alert_shares, $alert_messages, $alert_followers, $alert_favorites);
		
		header("Location: /settings/notifications?success");
		exit();
	}
	else
	{
		header("Location: /login?redirecturl=" . urlencode("/settings/notifications"));
		exit();
	}
?>