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
	
	/**
	 * OpenVoter Admin Settings Class
	 * Class dealing with handling settings for the site
	 *
	 * @package OpenVoter
	 * @subpackage AdminSettings
	 * @since 3.0
	 */
	class ovAdminManagement
	{
		function __construct()
		{
			require_once 'ovadminsecurity.php';
			$this->ovAdminSecurity = new ovAdminSecurity();
			
			require_once 'ovcryptography.php';
			$this->ovCryptography = new ovCryptography();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets The Site's Admins
		 * @access public
		 * @return array|false Returns array of admins or false if no permission, error, or no rows
		 * @since 3.0
		 */
		public function GetAdmins()
		{
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessAdmins()) {
				$query = "CALL GetAdmins(" . ovDBConnector::SiteID() . ")";
				$result = ovDBConnector::Query($query);

				if (!$result) {
					// ERROR
					return false;
				}

				if ($result->num_rows > 0)
				{
					$admins = array();
					while ($row = $result->fetch_assoc())
					{
						$admin['id'] = $row['id'];
						$admin['username'] = stripslashes($row['username']);
						$admin['full_name'] = stripslashes($row['full_name']);
						$admin['email'] = stripslashes($row['email']);
						
						if ($row['can_delete'] == 1) {
							$admin['can_delete'] = true;
						} else {
							$admin['can_delete'] = false;
						}
						
						$admin['role_name'] = stripslashes($row['role_name']);
						
						if ($row['site_preferences'] == 1) {
							$admin['preferences'] = true;
						} else {
							$admin['preferences'] = false;
						}

						if ($row['content_management'] == 1) {
							$admin['content'] = true;
						} else {
							$admin['content'] = false;
						}
						
						if ($row['manage_admins'] == 1) {
							$admin['admins'] = true;
						} else {
							$admin['admins'] = false;
						}

						array_push($admins, $admin);
					}

					ovDBConnector::FreeResult();

					return $admins;
				}
				else 
				{
					ovDBConnector::FreeResult();

					return false;
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Edits an admin
		 * @access public
		 * @param int $admin_id The PK of the admin user
		 * @param string $full_name The full name of the admin user
		 * @param string $email The email address of the admin user
		 * @param int $role The PK to the admin_role table specifying role
		 * @since 3.0
		 */
		public function EditAdmin($admin_id, $full_name, $email, $role)
		{
			$admin_id = mysql_escape_string($admin_id);
			$full_name = mysql_escape_string($full_name);
			$email = mysql_escape_string($email);
			$role = mysql_escape_string($role);
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessAdmins()) {
				$query = "CALL UpdateAdmin(" . ovDBConnector::SiteID() . ", $admin_id, '$full_name', '$email', $role)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Adds an admin
		 * @access public
		 * @param string $username The username of the admin
		 * @param string $full_name The full name of the admin user
		 * @param string $email The email address of the admin user
		 * @param string $password The password for the admin user
		 * @param int $role The PK to the admin_role table specifying role
		 * @since 3.0
		 */
		public function AddAdmin($username, $full_name, $email, $password, $role)
		{
			$username = mysql_escape_string($username);
			$full_name = mysql_escape_string($full_name);
			$email = mysql_escape_string($email);
			$role = mysql_escape_string($role);
			
			require_once 'ovcryptography.php';
			$ovCryptography = new ovCryptography();
			
			$salt = $ovCryptography->GetSalt();
			$key = $ovCryptography->GetKey();
			
			$password = $ovCryptography->OVEncrypt($password, $salt, $key);
			
			$query = "CALL AddAdmin(" . ovDBConnector::SiteID() . ", '$username', '$full_name', '$email', '$password', '$salt', '$key', $role)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Checks to see if a username is available
		 * @access public
		 * @param string $username The username to check
		 * @return bool False if taken, true if available
		 * @since 3.0
		 */
		public function IsUsernameAvailable($username)
		{
			$username = mysql_escape_string($username);
			
			$query = "CALL DoesAdminExist(" . ovDBConnector::SiteID() . ", '$username')";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0) {
				ovDBConnector::FreeResult();
				return false;
			} else {
				ovDBConnector::FreeResult();
				return true;
			}
		}
		
		/**
		 * Checks to see if an email is available
		 * @access public
		 * @param string $email The email to check
		 * @return bool True if available, false if taken
		 * @since 3.0
		 */
		public function IsEmailAvailable($email)
		{
			$email = mysql_escape_string($email);
			
			$query = "CALL DoesAdminExist(" . ovDBConnector::SiteID() . ", '$email')";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0) {
				ovDBConnector::FreeResult();
				return false;
			} else {
				ovDBConnector::FreeResult();
				return true;
			}
		}
		
		/**
		 * Reset's an admin's password
		 * @access public
		 * @param int $admin_id The email to check
		 * @param string $password1 The password
		 * @param string $password2 The password re-entered
		 * @return bool True if successful, false if not
		 * @since 3.0
		 */
		public function ResetPassword($admin_id, $password1, $password2)
		{
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessAdmins()) {
				$admin_id = mysql_escape_string($admin_id);
			
				if ($password1 != $password2) {
					return false;
				}
		
				if (strlen($password1) < 6 || strlen($password2) > 20) {
					return false;
				}
			
				$salt = $this->ovCryptography->GetSalt();
				$key = $this->ovCryptography->GetKey();
			
				$query = "CALL UpdateAdminPassword($admin_id, '" . $this->ovCryptography->OVEncrypt($password1, $salt, $key) . "', '$salt', '$key')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			
				return true;
			} else {
				return false;
			}
		}
		
		public function DeleteAdmin($admin_id)
		{
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessAdmins()) {
				$query = "CALL DeleteAdmin($admin_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		protected $ovAdminSecurity;
		protected $ovCryptography;
	}
?>