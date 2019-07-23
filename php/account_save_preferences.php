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

	//if ($_POST['reset_srart_page'] == "yes") {
	//	$reset_start_page = true;
	//} else {
		$reset_start_page = false;
	//}
	
	$open_links_in = $_POST['open_links_in'];
	$hide_comments = $_POST['hide_comments'];
	
	if ($_POST['on_submit'] == "yes") {
		$subscribe_submit = 1;
	} else {
		$subscribe_submit = 0;
	}
	
	if ($_POST['on_comment'] == "yes") {
		$subscribe_comment = 1;
	} else {
		$subscribe_comment = 0;
	}
	
	if ($_POST['prepopulate_reply'] == "yes") {
		$prepopulate_reply = 1;
	} else {
		$prepopulate_reply = 0;
	}

	if ($_POST['publicly_display_likes'] == "yes") {
		$publicly_display_likes = 1;
	} else {
		$publicly_display_likes = 0;
	}

	if ($ovUserSecurity->IsUserLoggedIn())
	{
		require_once 'ovouser.php';
		$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
		
		$ovoUser->SaveSettings($reset_start_page, $open_links_in, $subscribe_submit, $subscribe_comment, $hide_comments, $prepopulate_reply, $publicly_display_likes);
		
		header("Location: /settings/preferences?success");
		exit();
	}
	else
	{
		header("Location: /login?redirecturl=" . urlencode("/settings/preferences"));
		exit();
	}
?>