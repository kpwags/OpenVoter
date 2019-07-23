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
	 * OpenVoter Admin Bans Class
	 * Class dealing with handling bans
	 *
	 * @package OpenVoter
	 * @subpackage AdminBans
	 * @since 3.0
	 */
	class ovAdminBans
	{
		function __construct()
		{
			require_once 'ovadminsecurity.php';
			$this->ovAdminSecurity = new ovAdminSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets the users who have been banned from the site
		 * @access public
		 * @return array|false Returns an array of the users or false on error, no bans
		 * @since 3.0
		 */
		public function GetBannedUsers()
		{
			$query = "CALL GetBannedUsers(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$bans = array();
				while ($row = $result->fetch_assoc()) {
					$ban['id'] = $row['id'];
					$ban['username'] = stripslashes($row['username']);
					$ban['email'] = stripslashes($row['email']);
					$ban['banned_by'] = stripslashes($row['banned_by']);
					$ban['ban_reason'] = $this->ovUtilities->FormatBody($row['reason'], false);

					array_push($bans, $ban);
				}
				
				ovDBConnector::FreeResult();
				return $bans;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Unbans a user
		 * @access public
		 * @param int $user_id The PK of the User to unban
		 * @since 3.0
		 */
		public function UnbanUser($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnbanUser(" . ovDBConnector::SiteID() . ", $user_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the users who have been banned from the site
		 * @access public
		 * @return array|false Returns an array of the users or false on error, no bans
		 * @since 3.0
		 */
		public function GetBannedIPs()
		{
			$query = "CALL GetBannedIPs(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$ips = array();
				$bans = array();
				while ($row = $result->fetch_assoc()) {
					$ip['ip_address'] = $row['ip_address'];
					
					array_push($ips, $ip);
				}
				
				ovDBConnector::FreeResult();
				
				foreach ($ips as $ip) {
					$users = $this->GetAssociatedUsersForIP($ip['ip_address']);
					
					if (!$users) {
						$users = "Unknown";
					}
					
					$ban['ip_address'] = $ip['ip_address'];
					$ban['users'] = $users;
					
					array_push($bans, $ban);
				}
				
				return $bans;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the users associated with an IP Address
		 * @access protected
		 * @param string $ip IP address to look up
		 * @return string|false Returns a string of usernames or false on error, no bans
		 * @since 3.0
		 */
		protected function GetAssociatedUsersForIP($ip) 
		{
			$users = array();
			
			$query = "CALL GetAssociatedUsersFromIP(" . ovDBConnector::SiteID() . ", '$ip')";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// error
				return false;
			}

			if ($result->num_rows > 0)
			{
				$users = "";
				while ($row = $result->fetch_assoc()) {
					$users .= " " . stripslashes($row['username']) . " ";
				}
				ovDBConnector::FreeResult();
				return $users;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Bans an IP Address
		 * @access public
		 * @param string $ip_address The IP Address to ban
		 * @since 3.0
		 */
		public function BanIPAddress($ip_address)
		{
			$ip_address = mysql_escape_string($ip_address);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL BanIPAddress(" . ovDBConnector::SiteID() . ", '$ip_address')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Unbans an IP Address
		 * @access public
		 * @param string $ip_address The IP Address to unban
		 * @since 3.0
		 */
		public function UnbanIPAddress($ip_address)
		{
			$ip_address = mysql_escape_string($ip_address);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnbanIPAddress(" . ovDBConnector::SiteID() . ", '$ip_address')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the IP addresses banned independently
		 * @access public
		 * @return array|false Returns an array of the ips or false on error, no bans
		 * @since 3.2.3
		 */
		public function GetIndependentBannedIPs()
		{
			$query = "CALL GetIndependentBannedIPs()";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$ips = array();
				while ($row = $result->fetch_assoc()) {
					$ip['id'] = $row['id'];
					$ip['ip_address'] = $row['ip_address'];
					$ip['reason'] = $row['reason'];
					
					array_push($ips, $ip);
				}
				
				ovDBConnector::FreeResult();
				
				return $ips;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Bans an IP Address Independent of User
		 * @access public
		 * @param string $ip The IP Address to ban
		 * @param strint $reason The reason for the ban
		 * @since 3.2.3
		 */
		public function BanIndependentIPAddress($ip, $reason)
		{
			$ip = mysql_escape_string($ip);
			$reason = mysql_escape_string($reason);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL BanIndependentIPAddress('$ip', '$reason')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Unbans an IP Address Independent of User
		 * @access public
		 * @param int $id The PK of the IP Address to unban
		 * @since 3.2.3
		 */
		public function UnbanIndependentIPAddress($id)
		{
			$id = mysql_escape_string($id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnbanIndependentIPAddress($id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the domains banned from the site
		 * @access public
		 * @return array|false Returns an array of the domains or false on error, no bans
		 * @since 3.0
		 */
		public function GetBannedDomains()
		{
			$query = "CALL GetBannedDomains(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$bans = array();
				while ($row = $result->fetch_assoc()) {
					$ban['id'] = $row['id'];
					$ban['domain'] = stripslashes($row['domain_name']);
					$ban['reason'] = $this->ovUtilities->FormatBody($row['reason'], false);

					array_push($bans, $ban);
				}
				
				ovDBConnector::FreeResult();
				return $bans;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Unbans a domain
		 * @access public
		 * @param int $domain_id The PK of the domain to unban
		 * @since 3.0
		 */
		public function UnbanDomain($domain_id)
		{
			$domain_id = mysql_escape_string($domain_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnbanDomain(" . ovDBConnector::SiteID() . ", $domain_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Adds a domain to the banned list
		 * @access public
		 * @param string $domain_name The domain name to be banned
		 * @param string $reason The reason to be banned
		 * @since 3.0
		 */
		public function AddBannedDomain($domain_name, $reason)
		{
			$domain_name = mysql_escape_string($domain_name);
			$reason = mysql_escape_string($reason);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL AddBannedDomain(" . ovDBConnector::SiteID() . ", '$domain_name', '$reason')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Checks if a domain is banned
		 * @access public
		 * @param string $url The URL to get the domain to check
		 * @return bool True if banned, false if not
		 * @since 3.0
		 */
		public function IsDomainBanned($url)
		{
			$domain = $this->ovUtilities->GetDomain($url);
			
			$query = "CALL IsDomainBanned(" . ovDBConnector::SiteID() . ", '$domain')";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				ovDBConnector::FreeResult();
				return true;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the domains restricted from the site
		 * @access public
		 * @return array|false Returns an array of the domains or false on error, no bans
		 * @since 3.0
		 */
		public function GetRestrictedDomains()
		{
			$query = "CALL GetRestrictedDomains(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$bans = array();
				while ($row = $result->fetch_assoc()) {
					$ban['id'] = $row['id'];
					$ban['domain'] = stripslashes($row['domain_name']);
					$ban['reason'] = $this->ovUtilities->FormatBody($row['reason'], false);

					array_push($bans, $ban);
				}
				
				ovDBConnector::FreeResult();
				return $bans;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Unrestricts a domain
		 * @access public
		 * @param int $domain_id The PK of the domain to unban
		 * @since 3.0
		 */
		public function UnrestrictDomain($domain_id)
		{
			$domain_id = mysql_escape_string($domain_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnrestrictDomain(" . ovDBConnector::SiteID() . ", $domain_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Adds a domain to the restricted list
		 * @access public
		 * @param string $domain_name The domain name to be restricted
		 * @param string $reason The reason to be restricted
		 * @since 3.0
		 */
		public function AddRestrictedDomain($domain_name, $reason)
		{
			$domain_name = mysql_escape_string($domain_name);
			$reason = mysql_escape_string($reason);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL AddRestrictedDomain(" . ovDBConnector::SiteID() . ", '$domain_name', '$reason')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Checks if a domain is banned
		 * @access public
		 * @param string $url The URL to get the domain to check
		 * @return bool True if banned, false if not
		 * @since 3.0
		 */
		public function IsDomainRestricted($url)
		{
			$domain = $this->ovUtilities->GetDomain($url);
			
			$query = "CALL IsDomainRestricted(" . ovDBConnector::SiteID() . ", '$domain')";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				ovDBConnector::FreeResult();
				return true;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		protected $ovAdminSecurity;
		protected $ovUtilities;
	}
?>