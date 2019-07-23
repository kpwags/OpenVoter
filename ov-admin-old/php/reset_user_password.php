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
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();

	$user_id = $_POST['reset_pw_user_id'];
	$password_1 = $_POST['reset_password_1'];
	$password_2 = $_POST['reset_password_2'];
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessContent()) {
		if ($password_1 != $password_2) {
			header("Location: /ov-admin/content?type=user&id=$user_id&passwordreset=no");
			exit();
		}
	
		if (strlen($password_1) < 6 || strlen($password_1) > 20) {
			header("Location: /ov-admin/content?type=user&id=$user_id&passwordreset=no");
			exit();
		}
		
		$ovAdminContent->ResetUserPassword($user_id, $password_1);

		header("Location: /ov-admin/content?type=user&id=$user_id&passwordreset=yes");
		exit();
	} else {
		header("Location: /ov-admin/login?redirecturl=" . urlencode("/ov-admin/content?type=user&id=$user_id"));
		exit();
	}
?>