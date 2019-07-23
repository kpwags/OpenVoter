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
	
	require_once 'ovadminmanagement.php';
	$ovAdminManagement = new ovAdminManagement();

	$debug_mode = true;

	$username = strip_tags($_GET['username']);
	$full_name = strip_tags($_GET['name']);
	$password = urldecode($_GET['password']);
	$email = strip_tags($_GET['email']);
	$role = strip_tags($_GET['role']);
	
	if ($debug_mode) {
		echo "<p>INPUT</p>";
		echo "<div>USERNAME: $username</div>";
		echo "<div>FULL NAME: $full_name</div>";
		echo "<div>PASSWORD: $password</div>";
		echo "<div>EMAIL: $email</div>";
		echo "<div>ROLE: $role</div>";
	}
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessAdmins() && $ovAdminManagement->IsUsernameAvailable($username) && $ovAdminManagement->IsEmailAvailable($email))
	{		
		if (IsValidInput($username, $full_name, $password, $email, $role)) {
			$ovAdminManagement->AddAdmin($username, $full_name, $email, $password, $role);
		}
	}
	
	echo "OK";
	
	function IsValidInput($username, $full_name, $password, $email, $role)
	{
		$is_valid = true;
		
		$usernameRegex = "/^[a-zA-Z0-9_]{5,20}$/";
		if (!preg_match($usernameRegex, $username)) {
			echo "<p>BAD USERNAME</p>";
			$is_valid = false;
		}
		
		if (trim($full_name) == "") {
			echo "<p>BAD FULL NAME</p>";
			$is_valid = false;
		}
		
		if (trim($password) == "") {
			echo "<p>BAD PW</p>";
			$is_valid = false;
		}
		
		$emailRegex = "/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/";
		if (!preg_match($emailRegex, $email)) {
			echo "<p>BAD EMAIL</p>";
			$is_valid = false;
		}
		
		if ($role != 1 && $role != 2) {
			echo "<p>BAD ROLE</p>";
			$is_valid = false;
		}
		
		return $is_valid;
	}
?>