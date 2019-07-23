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
	
	class ovAdminSecurity
	{
		function __construct()
		{
			require_once 'ovdbconnector.php';
			
			require_once 'ovcryptography.php';
			$this->ovCryptography = new ovCryptography();
			
			if ($this->IsAdminLoggedIn()) {
				$this->GetLoggedInAdminInfo();
			}
		}
		
		function __destruct() 
		{
			
		}
		
		public function IsAdminLoggedIn()
		{
			if (isset($_COOKIE['ov_admin'])) {
				return true;
			} else {
				return false;
			}
		}
		
		public function GetLoggedInAdminInfo()
		{
			if ($this->IsAdminLoggedIn()) {
				$query = "CALL GetAdminInfo(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($_COOKIE['ov_admin']) . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					//$this->LogoutAdmin();
					$this->_logged_in_admin_name = false;
					$this->_logged_in_access_preferences = false;
					$this->_logged_in_access_content = false;
					$this->_logged_in_access_admins = false;
				} else {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						$this->_logged_in_admin_name = stripslashes($row['full_name']);
						
						if ($row['site_preferences'] == 1) {
							$this->_logged_in_access_preferences = true;
						} else {
							$this->_logged_in_access_preferences = false;
						}

						if ($row['content_management'] == 1) {
							$this->_logged_in_access_content = true;
						} else {
							$this->_logged_in_access_content = false;
						}
						
						if ($row['manage_admins'] == 1) {
							$this->_logged_in_access_admins = true;
						} else {
							$this->_logged_in_access_admins = false;
						}
						
					} else {
						$this->LogoutAdmin();
						$this->_logged_in_admin_name = false;
						$this->_logged_in_access_preferences = false;
						$this->_logged_in_access_content = false;
						$this->_logged_in_access_admins = false;
					}
					
					ovDBConnector::FreeResult();
				}
			}	
			else 
			{
				//$this->LogoutAdmin();
				$this->_logged_in_admin_name = false;
				$this->_logged_in_access_preferences = false;
				$this->_logged_in_access_content = false;
				$this->_logged_in_access_admins = false;
			}
		}
		
		public function LoggedInAdminID() 
		{ 
			if (isset($_COOKIE['ov_admin'])) {
				return $_COOKIE['ov_admin'];
			} else {
				return false;
			}
		}
		
		public function AdminName() { return $this->_logged_in_admin_name; }
		public function CanAccessPreferences() { return $this->_logged_in_access_preferences; }
		public function CanAccessContent() { return $this->_logged_in_access_content; }
		public function CanAccessAdmins() { return $this->_logged_in_access_admins; }
				
		public function CheckLogin($username, $password, $remember)
		{
			$login_result = "OK";
			$username = mysql_escape_string($username);
						
			$query = "CALL GetAdminLoginInfo(" . ovDBConnector::SiteID() . ", '$username')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				$login_result = "UNKNOWN";
			}
			
			if ($result->num_rows > 0)
			{			
				$row = $result->fetch_assoc();
				
				$db_password = $row['password'];
				$password = $this->ovCryptography->OVEncrypt($password, $row['password_salt'], $row['password_key']);
				
				if ($password == $db_password) {
					// good password
					$this->LoginAdmin($row['id'], $remember);
					$login_result = "OK";
				} else {
					// invalid password
					$login_result = "INVALID";
				}
				ovDBConnector::FreeResult();
			}
			else
			{
				// no user found
				$login_result = "UNKNOWN";
				ovDBConnector::FreeResult();
			}
			
			return $login_result;
		}
		
		protected function LoginAdmin($admin_id, $remember)
		{
			if($remember)
				setcookie("ov_admin", $admin_id, time() + 172800, "/");	// sets cookie for 12 hours
			else
				setcookie("ov_admin", $admin_id, time() + 7200, "/");		// sets cookie for 2 hours
		}
		
		public function LogoutAdmin()
		{
			setcookie("ov_admin", "", time() - 3600, "/");	
		}
		
		public function GetProfileSettings()
		{
			if ($this->IsAdminLoggedIn()) {
				$query = "CALL GetAdminProfileSettings(" . $this->LoggedInAdminID() . ")";
				$result = ovDBConnector::Query($query);

				if (!$result) {
					// ERROR
					return false;
				}
			
				if ($result->num_rows > 0)
				{	
					$row = $result->fetch_assoc();
					$admin['full_name'] = stripslashes($row['full_name']);
					$admin['email'] = stripslashes($row['email']);
					
					if ($row['email_reports'] == 1) {
						$admin['email_reports'] = true;
					} else {
						$admin['email_reports'] = false;
					}
					
					if ($row['email_feedback'] == 1) {
						$admin['email_feedback'] = true;
					} else {
						$admin['email_feedback'] = false;
					}
				
					ovDBConnector::FreeResult();
				
					return $admin;
				}
				else
				{
					ovDBConnector::FreeResult();
					return false;
				}
			} else {
				// no one logged in
				return false;
			}
		}
		
		public function SaveProfileSettings($full_name, $email, $email_reports, $email_feedback)
		{
			if ($this->IsAdminLoggedIn()) {
				$full_name = mysql_escape_string($full_name);
				$email = mysql_escape_string($email);
				
				if ($email_reports) {
					$email_reports = 1;
				} else {
					$email_reports = 0;
				}
				
				if ($email_feedback) {
					$email_feedback = 1;
				} else {
					$email_feedback = 0;
				}
				
				$query = "CALL SaveAdminProfileSettings(" . $this->LoggedInAdminID() . ", '$full_name', '$email', $email_reports, $email_feedback)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		public function ChangePassword($current_password, $new_password_1, $new_password_2)
		{
			if ($this->IsAdminLoggedIn()) {
			
				$query = "CALL GetAdminLoginInfoByID(" . ovDBConnector::SiteID() . ", '" . $this->LoggedInAdminID() . "')";
				$result = ovDBConnector::Query($query);

				if (!$result)
				{
					// ERROR
					return "error";
				}
			
				if ($result->num_rows > 0)
				{			
					$row = $result->fetch_assoc();
				
					$db_password = $row['password'];
				
					ovDBConnector::FreeResult();
				
					if ($new_password_1 != $new_password_2) {
						return "nomatch";
					}
				
					if (strlen($new_password_1) < 6 || strlen($new_password_1) > 20) {
						return "invalidlength";
					}
				
					if ($db_password != $this->ovCryptography->OVEncrypt($current_password, $row['password_salt'], $row['password_key'])) {
						return "invalidpassword";
					}
					
					$salt = $this->ovCryptography->GetSalt();
					$key = $this->ovCryptography->GetKey();
				
					$query = "CALL UpdateAdminPassword(" . $this->LoggedInAdminID() . ", '" . $this->ovCryptography->OVEncrypt($new_password_1, $salt, $key) . "', '$salt', '$key')";
					ovDBConnector::ExecuteNonQuery($query);
					ovDBConnector::FreeResult();
				
					return "OK";
				}
				else
				{
					return "error";
					ovDBConnector::FreeResult();
				}
			} else {
				header("Location: /login?redirecturl=" . urlencode("/ov-admin/profile?page=password"));
				exit();
			}
		}
		
		protected $_logged_in_admin_name;
		protected $_logged_in_access_preferences;
		protected $_logged_in_access_content;
		protected $_logged_in_access_admins;
		
		protected $ovCryptography;
	}
?>