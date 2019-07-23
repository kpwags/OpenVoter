<?php
	ini_set("include_path", ".:./:./ov-include:./../ov-include:./../../ov-include:./ov-admin/ov-include:./../ov-admin/ov-include:./../:./../../:./usercontrols:./../usercontrols:./../../usercontrols");
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

	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();

	$current_password = $_POST['current_password'];
	$new_password_1 = $_POST['new_password_1'];
	$new_password_2 = $_POST['new_password_2'];
	
	if ($ovAdminSecurity->IsAdminLoggedIn()) {
		$result = $ovAdminSecurity->ChangePassword($current_password, $new_password_1, $new_password_2);
		
		switch ($result) {
			case "OK":
				header("Location: /ov-admin/profile?page=password&success=true");
				exit();
				break;
			case "invalidpassword":
				header("Location: /ov-admin/profile?page=password&error=1");
				exit();
				break;
			case "invalidlength":
				header("Location: /ov-admin/profile?page=password&error=2");
				exit();
				break;
			case "nomatch":
				header("Location: /ov-admin/profile?page=password&error=3");
				exit();
				break;
			case "error":
			default:
				header("Location: /ov-admin/profile?page=password&error=4");
				exit();
				break;
		}
	} else {
		header("Location: /ov-admin/login?redirecturl=" . urlencode("/ov-admin/profile?page=password"));
		exit();
	}
?>