<?php
//ini_set("include_path", ".:./:./include:./../include:./../../include:./ov-admin/include:./../ov-admin/include:./../usercontrols:./usercontrols:./ov-admin/usercontrols:./../ov-admin/usercontrols:./themes:./../themes");
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
 * OpenVoter User Class
 * Class handling User Calls
 *
 * @package OpenVoter
 * @subpackage User
 * @since 3.0
 */
class ovUser
{
	function __construct()
	{
		require_once('ovdbconnector.php');
		
		require_once('ovutilities.php');
		$this->ovUtilities = new ovUtilities();
		
		require_once('ovusersecurity.php');
		$this->ovUserSecurity = new ovUserSecurity();
	}
	
	function __destruct() 
	{
		
	}
	
	/**
	 * Checks to see if the username or email already exists in the system
	 * @param $identifier string Username or Email to check
	 * @return bool Exists flag
	 * @access public
	 * @since 3.0
	 */
	public function DoesUserExist($identifier)
	{
		$identifier = mysql_escape_string($identifier);
	
		$query = "CALL GetUserInfoByIdentifier(" . ovDBConnector::SiteID() . ", '$identifier')";
		$result = ovDBConnector::Query($query);
		
		if (!$result)
		{
			// ERROR
			echo "ERROR IN QUERY: $query";
			return true;
		}
		
		if ($result->num_rows > 0) {
			ovDBConnector::FreeResult();
			return true;
		} else {
			ovDBConnector::FreeResult();
			return false;
		}
	}
	
	/**
	 * Registers a user in the system
	 * @param $username string The Username
	 * @param $password string The password
	 * @param $email string The email address
	 * @param $question string The security question
	 * @param $answer string The security question's answer
	 * @return bool Success flag
	 * @access public
	 * @since 3.0
	 */
	public function RegisterUser($username, $password, $email, $question, $answer)
	{
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$site_id = ovDBConnector::SiteID();
		
		require_once 'ovcryptography.php';
		$ovCryptography = new ovCryptography();
	
		$salt = $ovCryptography->GetSalt();
		$key = $ovCryptography->GetKey();
		
		$password = $ovCryptography->OVEncrypt($password, $salt, $key);
		$answer = $ovCryptography->OVEncrypt($answer, $salt, $key);
		
		$username = mysql_escape_string($username);
		$email = mysql_escape_string($email);
		$question = mysql_escape_string($question);

		$query = "CALL AddUser($site_id, '$username', '$password', '$salt', '$key', '$email', '$question', '$answer', '$ip_address')";
		$result = ovDBConnector::Query($query);

		if (!$result) {
			// ERROR
			return false;
		} else {
			ovDBConnector::FreeResult();
			return true;
		}
	}
	
	/**
	 * Checks to see if the user is following the specified user
	 * @param $user_id int The user to check's ID
	 * @return bool Following flag
	 * @access public
	 * @since 3.0
	 */
	public function IsFollowing($user_id)
	{
		if ($this->ovUserSecurity->IsUserLoggedIn())
		{
			$user_id = mysql_escape_string($user_id);

			$query = "CALL IsUserFollowing(" . ovDBConnector::SiteID() . ", " . $this->ovUserSecurity->LoggedInUserID() . ", $user_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return false;
			}
			
			if ($result->num_rows > 0) {
				ovDBConnector::FreeResult();
				return true;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Checks to see if a user is blocking another user
	 * @param $user_id int The user
	 * @param $user_being_blocked_id int The user potentially being blocked
	 * @return bool Blocked flag
	 * @access public
	 * @since 3.0
	 */
	public function IsBlocking($user_id, $user_being_blocked_id)
	{
		if ($this->ovUserSecurity->IsUserLoggedIn())
		{
			
			$user_id = mysql_escape_string($user_id);
			$user_being_blocked_id = mysql_escape_string($user_being_blocked_id);

			$query = "CALL IsUserBlocking(" . ovDBConnector::SiteID() . ", $user_id, $user_being_blocked_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return false;
			}
			
			if ($result->num_rows > 0) {
				ovDBConnector::FreeResult();
				return true;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Blocks a user
	 * @param $user_to_block_id int The user to block's ID
	 * @access public
	 * @since 3.0
	 */
	public function BlockUser($user_to_block_id)
	{
		if ($this->ovUserSecurity->IsUserLoggedIn()) {
			$user_to_block_id = mysql_escape_string($user_to_block_id);

			$query = "CALL BlockUser(" . ovDBConnector::SiteID() . ", " . $this->ovUserSecurity->LoggedInUserID() . ", $user_to_block_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
	}
	
	/**
	 * Unblocks a user
	 * @param $user_to_unblock_id int The user to unblock's ID
	 * @access public
	 * @since 3.0
	 */
	public function UnblockUser($user_to_unblock_id)
	{
		if ($this->ovUserSecurity->IsUserLoggedIn()) {
			$user_to_unblock_id = mysql_escape_string($user_to_unblock_id);

			$query = "CALL UnblockUser(" . ovDBConnector::SiteID() . ", " . $this->ovUserSecurity->LoggedInUserID() . ", $user_to_unblock_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
	}
	
	/**
	 * Follows a user
	 * @param $user_to_follow_id int The user to follow's ID
	 * @access public
	 * @since 3.0
	 */
	public function FollowUser($user_to_follow_id)
	{
		if ($this->ovUserSecurity->IsUserLoggedIn()) {
			$user_to_follow_id = mysql_escape_string($user_to_follow_id);

			$query = "CALL FollowUser(" . ovDBConnector::SiteID() . ", " . $this->ovUserSecurity->LoggedInUserID() . ", $user_to_follow_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return false;
			}
			
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				
				if ($row['result'] == "OK") {
					ovDBConnector::FreeResult();
					
					// do alerting
					require_once 'ovalerting.php';
					$ovAlerting = new ovAlerting();
					$ovAlerting->ProcessNewFollowerAlerts($user_to_follow_id, $this->ovUserSecurity->LoggedInUserID());
					
					return "OK";
				} elseif ($row['result'] == "BLOCKED") {
					ovDBConnector::FreeResult();
					return "BLOCKED";
				}
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Unfollows a user
	 * @param $user_to_unfollow int The user to unfollow's ID
	 * @access public
	 * @since 3.0
	 */
	public function UnfollowUser($user_to_unfollow)
	{
		if ($this->ovUserSecurity->IsUserLoggedIn()) {
			$user_to_unfollow = mysql_escape_string($user_to_unfollow);

			$query = "CALL UnfollowUser(" . ovDBConnector::SiteID() . ", " . $this->ovUserSecurity->LoggedInUserID() . ", $user_to_unfollow)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
	}
	
	/**
	 * Checks to see if the user subscribes to a submission on submitting it
	 * @param $user_id int The user's ID
	 * @return bool Subscribe flag
	 * @access public
	 * @since 3.0
	 */
	public function SubscribeOnSubmit($user_id)
	{
		$user_id = mysql_escape_string($user_id);
		
		$query = "CALL SubscribeOnSubmit(" . ovDBConnector::SiteID() . ", $user_id)";
		$result = ovDBConnector::Query($query);
		
		if (!$result) {
			// error
			return false;
		}
		
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			
			if ($row['subscribe_on_submit'] == 1) {
				$subscribe = true;
			} else {
				$subscribe = false;
			}
			
			ovDBConnector::FreeResult();
			return $subscribe;
		} else {
			ovDBConnector::FreeResult();
			return false;
		}
	}
	
	/**
	 * Checks to see if the user subscribes to a submission on commenting on it
	 * @param $user_id int The user's ID
	 * @return bool Subscribe flag
	 * @access public
	 * @since 3.0
	 */
	public function SubscribeOnComment($user_id)
	{
		$user_id = mysql_escape_string($user_id);
		
		$query = "CALL SubscribeOnComment(" . ovDBConnector::SiteID() . ", $user_id)";
		$result = ovDBConnector::Query($query);
		
		if (!$result) {
			// error
			return false;
		}
		
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			
			if ($row['subscribe_on_comment'] == 1) {
				$subscribe = true;
			} else {
				$subscribe = false;
			}
			
			ovDBConnector::FreeResult();
			return $subscribe;
		} else {
			ovDBConnector::FreeResult();
			return false;
		}
	}
	
	/**
	 * Gets the top 10 users
	 * @return array Array of top 10 users
	 * @access public
	 * @since 3.0
	 */
	public function GetTopTenUsers()
	{
		$query = "CALL GetTopTenUsers(" . ovDBConnector::SiteID() . ")";
		$result = ovDBConnector::Query($query);
		
		if (!$result) {
			// error
			return false;
		}
		
		if ($result->num_rows > 0) 
		{
			$users = array();
			while ($row = $result->fetch_assoc())
			{
				$user['id'] = $row['id'];
				$user['username'] = stripslashes($row['username']);
				$user['avatar'] = stripslashes($row['avatar']);
				$user['karma'] = floor($row['karma_points']);
				
				array_push($users, $user);
			}
			ovDBConnector::FreeResult($result);
			return $users;
		}
		else
		{
			ovDBConnector::FreeResult($result);
			return false;
		}
	}
	
	/**
	 * Gets the karma points for a user
	 * @param $user_id int The user's ID
	 * @return double The karma of the user
	 * @access public
	 * @since 3.0
	 */
	public function GetKarma($user_id)
	{
		$query = "CALL GetKarmaPoints(" . ovDBConnector::SiteID() . ", $user_id)";
		$result = ovDBConnector::Query($query);
		
		if (!$result) {
			// error
			return 0;
		}
		
		if ($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
		
			ovDBConnector::FreeResult($result);
		
			return floor($row['karma_points']);
		}
		else 
		{
			ovDBConnector::FreeResult();
			return 0;
		}
	}
	
	/**
	 * User Security Class Object
	 * @access protected
	 * @var ovUserSecurity
	 * @since 3.0
	 */
	protected $ovUserSecurity;
	
	/**
	 * Utilities Class Object
	 * @access protected
	 * @var ovUtilities
	 * @since 3.0
	 */
	protected $ovUtilities;
}
?>