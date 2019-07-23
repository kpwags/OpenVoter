<?php
	//ini_set("include_path", ".:./:./include:./../include:./../../include:./ov-admin/include:./../ov-admin/include:./../usercontrols:./usercontrols:./ov-admin/usercontrols:./../ov-admin/usercontrols:./themes:./../themes");
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
	
	/**
	 * OpenVoter User Security Class
	 * Class handling User Security
	 *
	 * @package OpenVoter
	 * @subpackage UserSecurity
	 * @since 3.0
	 */
	class ovUserSecurity
	{
		function __construct()
		{
			require_once 'ovdbconnector.php';
		
			require_once 'ovcryptography.php';
			$this->ovCryptography = new ovCryptography();
			
			if ($this->IsUserLoggedIn()) {
				$this->GetLoggedInUserInfo();
			} else {
				$ovoLoggedInUser = null;
			}
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Checks to see if the user is logged in
		 * @return bool Flag if user is logged in
		 * @access public
		 * @since 3.0
		 */
		public function IsUserLoggedIn()
		{
			if (isset($_COOKIE['ov_user'])) {
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Gets the basic info of the logged in user
		 * @access public
		 * @since 3.0
		 */
		public function GetLoggedInUserInfo()
		{
			if ($this->IsUserLoggedIn()) {
				$query = "CALL GetUserInfoByID(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($_COOKIE['ov_user']) . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					$this->logged_in_username = false;
					$this->logged_in_banned = false;
					$this->logged_in_suspended = false;
				} else {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						$this->logged_in_username = stripslashes($row['username']);
						
						if ($row['suspended'] == 1) {
							$this->logged_in_suspended = true;
						} else {
							$this->logged_in_suspended = false;
						}

						if ($row['banned'] == 1) {
							$this->logged_in_banned = true;
						} else {
							$this->logged_in_banned = false;
						}
						
					} else {
						$this->logged_in_username = false;
						$this->logged_in_banned = false;
						$this->logged_in_suspended = false;
					}

					require_once('ovouser.php');
					$ovoLoggedInUser = new ovoUser(false, $this->_logged_in_username);
					
					ovDBConnector::FreeResult();
				}
			}
		}
		
		/**
		 * Returns the ID of the logged in user
		 * @return int|false ID of logged in user or false if not logged in
		 * @access public
		 * @since 3.0
		 */
		public function LoggedInUserID() 
		{ 
			if (isset($_COOKIE['ov_user'])) {
				return mysql_escape_string($_COOKIE['ov_user']);
			} else {
				return false;
			}
		}
		
		/**
		 * Returns the username of the logged in user
		 * @return string|false Username of logged in user or false if not logged in
		 * @access public
		 * @since 3.0
		 */
		public function LoggedInUsername()
		{
			if (isset($_COOKIE['ov_user'])) {
				return $this->logged_in_username;
			} else {
				return false;
			}
		}

		/**
		 * Returns the user object of the logged in user
		 * @return ovoUser|null
		 * @access public
		 * @since 3.3
		 */
		public function LoggedInUser()
		{
			return $ovoLoggedInUser;
		}
		
		/**
		 * Validates the session to see if a logged in user is banned or suspended
		 * @access public
		 * @since 3.0
		 */
		public function ValidateSession()
		{
			if(!$this->CheckIPAddress()) {
				// IP is banned KILL SESSION
				$this->KillSession();
			}
			
			if ($this->IsUserLoggedIn()) {
				if ($this->logged_in_banned) {
					// user is banned, KILL SESSION
					$this->KillSession();
				}
			
				if ($this->logged_in_suspended) {
					// user is suspended, KILL SESSION
					$this->KillSession();
				}
				
				if ($this->logged_in_banned) {
					// user is banned, KILL SESSION
					$this->KillSession();
				}
				
				if ($this->EnforcePasswordReset()) {
					header("Location: /password-reset");
					exit();
				}
			}
		}
		
		/**
		 * Kills the session if user is banned/suspended
		 * @access public
		 * @since 3.0
		 */
		public function KillSession()
		{
			$this->LogoutUser();
		}
		
		/**
		 * Checks the username and password and logs in the user or why it failed
		 * @param $username string Username or Email of user
		 * @param $password string Password of user
		 * @param $remember bool Flag to remember login
		 * @return string Response code
		 * @access public
		 * @since 3.0
		 */
		public function CheckLogin($username, $password, $remember)
		{
			$login_result = "OK";
			$username = mysql_escape_string($username);
			
			
			$query = "CALL GetUserLoginInfo(" . ovDBConnector::SiteID() . ", '$username')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				$login_result = "UNKNOWN";
				return $login_result;
			}
			
			if ($result->num_rows > 0)
			{			
				$row = $result->fetch_assoc();
				
				$db_password = $row['password'];
				
				if ($row['suspended'] == 1) {
					// user is suspended
					$login_result = "SUSPENDED";
					return $login_result;
				}
				
				if ($row['banned'] == 1) {
					// user is banned
					$login_result = "BANNED";
					return $login_result;
				}
				
				$password = $this->ovCryptography->OVEncrypt($password, $row['password_salt'], $row['password_key']);
				$user_id = $row['id'];
				
				ovDBConnector::FreeResult();
				
				if (!$this->CheckIPAddress()) {
					// IP banned
					$login_result = "IP BANNED";
					return $login_result;
				}
				
				if ($password == $db_password) {
					// good password
					$this->LoginUser($row['id'], $remember);
					$login_result = "OK";
				} else {
					// invalid password
					$login_result = "INVALID";
					return $login_result;
				}
			}
			else
			{
				// no user found
				$login_result = "UNKNOWN";
				ovDBConnector::FreeResult();
			}
			
			return $login_result;
		}
		
		/**
		 * Confirms the password of the logged in user
		 * @param $password string Password for user
		 * @return bool Flag for password match
		 * @access public
		 * @since 3.0
		 */
		public function ConfirmPassword($password) 
		{
			if ($this->IsUserLoggedIn()) {
				$query = "CALL GetUserLoginInfo(" . ovDBConnector::SiteID() . ", '" . mysql_escape_string($this->logged_in_username) . "')";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// ERROR
					return false;
				}

				if ($result->num_rows > 0) {			
					$row = $result->fetch_assoc();
					$db_password = $row['password'];
					$password = $this->ovCryptography->OVEncrypt($password, $row['password_salt'], $row['password_key']);
					
					ovDBConnector::FreeResult();
					
					if ($password == $db_password) { 
						return true;
					} else {
						return false;
					}
				}
				else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Creates the cookie logging the user in
		 * @param $user_id int The ID of the user
		 * @param $remember bool Flag to remember login
		 * @access public
		 * @since 3.0
		 */
		protected function LoginUser($user_id, $remember)
		{
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			
			require_once 'ovutilities.php';
			$ovUtilities = new ovUtilities();
			
			$domain = "." . $ovUtilities->GetDomain($ovSettings->RootURL());
			
			if($remember)
				setcookie("ov_user", $user_id, time() + 31536000, "/", $domain);	// sets cookie for some insanely long period
			else
				setcookie("ov_user", $user_id, time() + 7200, "/", $domain);		// sets cookie for 2 hours
		}
		
		/**
		 * Deletes the cookie logging the user out
		 * @access public
		 * @since 3.0
		 */
		public function LogoutUser()
		{
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			
			require_once 'ovutilities.php';
			$ovUtilities = new ovUtilities();
			
			$domain = "." . $ovUtilities->GetDomain($ovSettings->RootURL());
			
			setcookie("ov_user", "", time() - 3600, "/", $domain);	
		}
		
		/**
		 * Checks the IP address of the user and sees if its on the banned list. Will also associate the user with the IP 
		 * if not previously linked
		 * @return bool Flag if IP is ok or not, Banned = False / OK = True
		 * @access public
		 * @since 3.0
		 */
		public function CheckIPAddress()
		{
			$ip_address = $_SERVER['REMOTE_ADDR'];
			
			$query = "CALL GetIPAddressInfo(" . ovDBConnector::SiteID() . ", '$ip_address')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return true;
			}
			
			if ($result->num_rows > 0)
			{
				while($row = $result->fetch_assoc())
				{
					if ($row['banned'] == 1) {
						ovDBConnector::FreeResult();
						return false;
					}
				}
			
				ovDBConnector::FreeResult();
			} else {
				ovDBConnector::FreeResult();
			}
			
			$ip_address_array = $this->GetIndependentIPAddresses();
			
			if (in_array($ip_address, $ip_address_array)) {
				return false;
			}
			
			for ($i = 0; $i < count($ip_address_array); $i++) {
				if (ereg($ip_address_array[$i], $ip_address)) {
				    return false;
				}
			}
			
			
			if ($this->IsUserLoggedIn() && !$this->IsUserLinkedToIP()) {
				$this->AddIPAddressForUser($this->LoggedInUserID());
			}
				
			return true;
		}
		
		protected function GetIndependentIPAddresses()
		{
			$query = "CALL GetIndependentBannedIPs()";
			$result = ovDBConnector::Query($query);

			$ips = array();

			if (!$result) {
				// ERROR
				ovDBConnector::FreeResult();
				return $ips;
			}
			
			if ($result->num_rows > 0)
			{	
				while ($row = $result->fetch_assoc()) {
					$ip = $row['ip_address'];
					array_push($ips, $ip);
				}
				
				ovDBConnector::FreeResult();
				
				return $ips;
			}
			else
			{
				ovDBConnector::FreeResult();
				return $ips;
			}
		}
		
		/**
		 * Checks to see if user is linked to IP connected to
		 * @access public
		 * @since 3.2
		 */
		public function IsUserLinkedToIP()
		{
			$user_id = $this->LoggedInUserID();
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$query = "CALL IsUserLinkedToIP($user_id, '$ip_address')";
			$result = ovDBConnector::Query($query);
			
			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$count_ip = $row['count_ip'];
				ovDBConnector::FreeResult();
				
				if ($count_ip > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Associates a user with an IP address
		 * @param $user_id int The ID of the user
		 * @access public
		 * @since 3.0
		 */
		public function AddIPAddressForUser($user_id)
		{
			$ip_address = $_SERVER['REMOTE_ADDR'];
			$user_id = mysql_escape_string($user_id);
			
			$query = "CALL AddIPAddress(" . ovDBConnector::SiteID() . ", $user_id, '$ip_address')";

			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Deletes a user account
		 * @param $password string User Password for confirmation
		 * @access public
		 * @since 3.0
		 */
		public function DeleteAccount($password)
		{
			if ($this->IsUserLoggedIn() && $this->ConfirmPassword($password)) {
				$query = "CALL DeleteUser(" . $this->LoggedInUserID() . ", 1)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
				
				$this->LogoutUser();
			}
		}
		
		/**
		 * Checks the security answer for the user
		 * @param $email string The email of the user
		 * @param $answer string The provided security answer
		 * @return bool Success flag
		 * @access public
		 * @since 3.0
		 */
		public function CheckecurityAnswer($email, $answer)
		{
			$email = mysql_escape_string($email);
			$query = "CALL GetSecurityAnswer('$email')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				$security_answer = $row['security_answer'];
				$security_salt = $row['security_answer_salt'];
				$security_key = $row['security_answer_key'];
				
				$user_answer = $this->ovCryptography->OVEncrypt($answer, $security_salt, $security_key);
				
				if ($user_answer == $security_answer) {
					$result = true;
				} else {
					$result = false;
				}
				
				ovDBConnector::FreeResult();
				
				return $result;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the security question for the specified user
		 * @param $email string The email of the user
		 * @return string|false The question or FALSE if error
		 * @access public
		 * @since 3.0
		 */
		public function GetSecurityQuestion($email)
		{
			$email = mysql_escape_string($email);
			$query = "CALL GetSecurityQuestion('$email')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				$security_question = stripslashes($row['security_question']);
				ovDBConnector::FreeResult();
				
				return $security_question;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Resets the password for the user
		 * @param $email string The email of user
		 * @param $password string New password
		 * @access public
		 * @since 3.0
		 */
		public function ResetPassword($email, $password)
		{
			$salt = $this->ovCryptography->GetSalt();
			$key = $this->ovCryptography->GetKey();

			$password = $this->ovCryptography->OVEncrypt($password, $salt, $key);
			
			$email = mysql_escape_string($email);
			
			$query = "CALL ResetPassword('$email', '$password', '$salt', '$key')";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Checks the user's karma level to see if affected by karma penalties and can post a submission
		 * @return bool Flag as to whether posting allowed
		 * @access public
		 * @since 3.0
		 */
		public function CanUserPostSubmission()
		{
			if ($this->IsUserLoggedIn()) {
				$user_id = $_COOKIE['ov_user'];
				$query = "CALL CanUserPostSubmission(" . ovDBConnector::SiteID() . ", $user_id)";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// ERROR
					return false;
				} else {
					$row = $result->fetch_assoc();
					
					if ($row['can_post'] == "YES") {
						ovDBConnector::FreeResult();
						return true;
					} else {
						ovDBConnector::FreeResult();
						return false;
					}
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Checks the user's karma level to see if affected by karma penalties and can post a comment
		 * @return bool Flag as to whether posting allowed
		 * @access public
		 * @since 3.0
		 */
		public function CanUserPostComment()
		{
			if ($this->IsUserLoggedIn()) {
				$user_id = $_COOKIE['ov_user'];
				$query = "CALL CanUserPostComment(" . ovDBConnector::SiteID() . ", $user_id)";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// ERROR
					return false;
				} else {
					$row = $result->fetch_assoc();
					
					if ($row['can_post'] == "YES") {
						ovDBConnector::FreeResult();
						return true;
					} else {
						ovDBConnector::FreeResult();
						return false;
					}
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Checks to see if the user has to reset their password
		 * @return bool Flag indicating Password Change is required
		 * @access public
		 * @since 3.2
		 */
		public function EnforcePasswordReset()
		{
			if ($this->IsUserLoggedIn()) {
				$user_id = $_COOKIE['ov_user'];
				
				$query = "CALL EnforcePasswordChange($user_id)";
				$result = ovDBConnector::Query($query);

				if (!$result) {
					// ERROR
					return false;
				} else {
					$row = $result->fetch_assoc();

					if ($row['force_password_reset'] == 1) {
						ovDBConnector::FreeResult();
						return true;
					} else {
						ovDBConnector::FreeResult();
						return false;
					}
				}
			} else {
				return false;
			}
		}
		
		public function ResetUserPassword($password, $question, $answer) 
		{
			if ($this->IsUserLoggedIn()) {
				$user_id = $_COOKIE['ov_user'];
				$question = mysql_escape_string($question);
				
				require_once 'ovcryptography.php';
				$ovCryptography = new ovCryptography();

				$salt = $ovCryptography->GetSalt();
				$key = $ovCryptography->GetKey();

				$password = $ovCryptography->OVEncrypt($password, $salt, $key);
				$answer = $ovCryptography->OVEncrypt($answer, $salt, $key);
				
				$query = "CALL ResetUserPasswordAndQuestion($user_id, '$password', '$question', '$answer', '$salt', '$key')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();

				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Logged in user's username
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $logged_in_username;
		
		/**
		 * Logged in user banned flag
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $logged_in_banned;
		
		/**
		 * Logged in user suspended flag
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $logged_in_suspended;
		
		/**
		 * Cryptography Object
		 * @access protected
		 * @var ovCryptography
		 * @since 3.0
		 */
		protected $ovCryptography;

		/**
		 * Logged in User Object
		 * @access protected
		 * @var ovoUser
		 * @since 3.3
		 */
		protected $ovoLoggedInUser;
	}
?>