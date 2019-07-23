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
	
	$password1 = $_POST['password1'];
	$password2 = $_POST['password2'];
	$security_question = $_POST['securityquestion'];
	$security_answer = $_POST['securityanswer'];

	$error = 0;

	// CHECK PASSWORDS
	if (trim($password1) == "" || trim($password2) == "") {
		// no password
		$error = 1;
	}
	
	if ($password1 != $password2) {
		// don't match
		$error = 2;
	}
	
	if (strlen($password1) < 6 || strlen($password1) > 20) {
		// not long enough (or too long)
		$error = 3;
	}
	
	if (trim($security_answer) == "") {
		// no answer
		$error = 4;
	}
	
	if ($error == 0) {
		$result = $ovUserSecurity->ResetUserPassword($password1, $security_question, $security_answer);
		if ($result) {
			header("Location: /");
			exit();
		} else {
			header("Location: /password-reset?error=5");
			exit();
		}
	} else {
		header("Location: /password-reset?error=$error");
		exit();
	}
?>