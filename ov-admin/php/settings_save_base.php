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

	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	require_once 'ovadminsettings.php';
	$ovAdminSettings = new ovAdminSettings();

	$root_url = strip_tags($_POST['root_url']);
	$title = strip_tags($_POST['site_title']);

	if (substr($root_url, -1) == "/") {
		$root_url = substr($root_url, 0, (count($root_url) - 1));
	}
	
	if ($_POST['enable_api'] == "yes") {
		$enable_api = true;
	} else {
		$enable_api = false;
	}

	$blog = strip_tags($_POST['blog']);
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessPreferences())
	{
		$ovAdminSettings->SaveBaseSettings($root_url, $title, $blog, $enable_api);

		header("Location: /ov-admin/settings?page=base");
		exit();
	}
	elseif ($ovAdminSecurity->IsAdminLoggedIn() && !$ovAdminSecurity->CanAccessPreferences())
	{
		header("Location: /ov-admin");
		exit();
	}
	else
	{
		header("Location: /ov-admin/login?redirecturl=" . urlencode("/ov-admin/settings?page=base"));
		exit();
	}
?>