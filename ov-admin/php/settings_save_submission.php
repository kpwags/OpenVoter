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
	// pagination, show_votes, show_down_votes, friends_page_enabled, show_voting_buttons_friends_page, show_voting_buttons_user_profile
	require_once 'ov-config.php';

	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	require_once 'ovadminsettings.php';
	$ovAdminSettings = new ovAdminSettings();

	$pagination = strip_tags($_POST['pagination']);

	if ($_POST['show_votes'] == "yes") {
		$show_votes = true;
	} else {
		$show_votes = false;
	}
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessPreferences())
	{
		$ovAdminSettings->SaveSubmissionSettings($pagination, $show_votes);

		header("Location: /ov-admin/settings?page=submission");
		exit();
	}
	elseif ($ovAdminSecurity->IsAdminLoggedIn() && !$ovAdminSecurity->CanAccessPreferences())
	{
		header("Location: /ov-admin");
		exit();
	}
	else
	{
		header("Location: /ov-admin/login?redirecturl=" . urlencode("/ov-admin/settings?page=submission"));
		exit();
	}
?>