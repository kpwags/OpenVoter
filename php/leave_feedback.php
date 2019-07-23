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
	
	require_once 'ovcontent.php';
	$ovContent = new ovContent();
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();
	
	$name 		= strip_tags($_POST['username']);
	$email 		= strip_tags($_POST['email']);
	$reason		= strip_tags($_POST['reason']);
	$message 	= strip_tags($_POST['message']);
	
	$error = false;
	
	// CHECK NAME
	if (trim($name) == "") {
		$error = 1;
	}
	
	// CHECK EMAIL
	$emailRegex = "/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/";
	if (!preg_match($emailRegex, $email)) {
		// invalid email
		$result = 2;
	}
	
	// CHECK MESSAGE
	if (trim($message) == "") {
		$error = 3;
	}
	
	// CHECK RECAPTCHA IF ENABLED
	if ($ovSettings->EnableRecaptcha()) {
		require_once('./../recaptcha/recaptchalib.php');
		$privatekey = $ovSettings->RecaptchaPrivateKey();
		$resp = recaptcha_check_answer ($privatekey,
                       $_SERVER["REMOTE_ADDR"],
                       $_POST["recaptcha_challenge_field"],
                       $_POST["recaptcha_response_field"]);

		if (!$resp->is_valid) {
			// bad recaptcha response
			$error = 4;
		}
	}
	
	if ($error) {
		// there was an errror
		header("Location: /feedback/error/" . $error);
		exit();
	}
	
	$ovContent->LeaveFeedback($name, $email, $reason, $message);
	header("Location: /feedback/success");
	exit();
?>