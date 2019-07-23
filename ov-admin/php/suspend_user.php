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
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();
	
	if (isset($_SERVER['HTTP_REFERER'])) {
		$prev_page = $_SERVER['HTTP_REFERER'];
	} else {
		$prev_page = "/ov-admin";
	}
	
	if (isset($_GET['user_id'])) {
		$user_id = $_GET['user_id'];
	}
	
	if (isset($_GET['username'])) {
		$username = $_GET['username'];
	}
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessContent())
	{
		if (isset($user_id)) {
			$ovAdminContent->SuspendUserByID($user_id);
		} elseif (isset($username)) {
			$ovAdminContent->SuspendUserByUsername($username);
		}

		header("Location: $prev_page");
		exit();
	}
	elseif ($ovAdminSecurity->IsAdminLoggedIn() && !$ovAdminSecurity->CanAccessContent())
	{
		header("Location: /ov-admin");
		exit();
	}
	else
	{
		header("Location: /ov-admin/login?redirecturl=" . urlencode($prev_page));
		exit();
	}
?>