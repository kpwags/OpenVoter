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
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();
	
	// enable for DEBUG
	$debug_enabled = false;
	
	/*
	 * ERROR CODES
	 * 1 - UNKNOWN
	 * 2 - NO USERNAME
	 * 3 - INVALID USERNAME
	 * 4 - USERNAME TAKEN
	 * 5 - NO EMAIL
	 * 6 - INVALID EMAIL
	 * 7 - EMAIL TAKEN
	 * 8 - NO PASSWORD
	 * 9 - PASSWORDS DON'T MATCH
	 * 10 - PASSWORDS NOT LONG ENOUGH
	 * 11 - NO ANSWER
	 */
	
	$email 		= strip_tags($_POST['email']);
	$username 	= strip_tags($_POST['username']);
	$password1 	= $_POST['password1'];
	$password2 	= $_POST['password2'];
	$question 	= strip_tags($_POST['securityquestion']);
	$answer 	= strip_tags($_POST['securityanswer']);
	
	if ($debug_enabled) {
		echo "<p>EMAIL: $email</p>";
		echo "<p>USERNAME: $username</p>";
		echo "<p>PASSWORD1: $password1</p>";
		echo "<p>PASSWORD2: $password2</p>";
		echo "<p>QUESTION: $question</p>";
		echo "<p>ANSWER: $answer</p>";
	}
	
	require_once 'ovuser.php';
	$ovUser = new ovUser();
	
	$error = false;
	
	// CHECK USERNAME
	if (trim($username) == "") {
		$error = 2;
	}
	
	$usernameRegex = "/^[a-zA-Z0-9_]{5,20}$/";
	if (!preg_match($usernameRegex, $username)) {
		// invalid username
		$error = 3;
	}
	
	$exists = $ovUser->DoesUserExist($username);
	if ($exists == "ERROR") {
		$error = 1;
	} elseif ($exists) {
		// exists
		$error = 4;
	}
	
	// CHECK EMAIL
	if (trim($email) == "") {
		$error = 5;
	}
	
	$emailRegex = "/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/";
	if (!preg_match($emailRegex, $email)) {
		// invalid email
		$result = 6;
	}
	
	$exists = $ovUser->DoesUserExist($email);
	if ($exists == "ERROR") {
		$error = 1;
	} elseif ($exists) {
		// exists
		$error = 7;
	}
	
	// CHECK PASSWORDS
	if (trim($password1) == "" || trim($password2) == "") {
		// no password
		$error = 8;
	}
	
	if ($password1 != $password2) {
		// don't match
		$error = 9;
	}
	
	if (strlen($password1) < 6 || strlen($password1) > 20) {
		// not long enough (or too long)
		$error = 10;
	}
	
	// CHECK ANSWER
	if (trim($answer) == "") {
		// no answer
		$error = 11;
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
			$error = 12;
		}
	}
	
	if ($error) {
		// there is some problem with the registration
		if ($debug_enabled) {
			echo "ERROR: " . $error;
		} else {
			header("Location: /sign-up?error=" . $error);
			exit();
		}
	} else {
		if (!$ovUserSecurity->IsUserLoggedIn()) {
			// register the user, everything checks out	
			$password = $password1;
			$username = mysql_escape_string($username);
			$email = mysql_escape_string($email);
			$question = mysql_escape_string($question);
			$answer = mysql_escape_string($answer);
		
			$result = $ovUser->RegisterUser($username, $password, $email, $question, $answer);
		
			if ($result) {
				// user added
				header("Location: /registration-complete");
			} else {
				// error?
				header("Location: /sign-up?error=13");
				exit();
			}
		} else {
			header("Location: /users/" . strtolower($ovUserSecurity->LoggedInUser()->Username()));
			exit();
		}
	}
?>