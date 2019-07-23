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
	 * OpenVoter User Object Class
	 * Class holding the data from a user
	 *
	 * @package OpenVoter
	 * @subpackage oUser
	 * @since 3.0
	 */
	class ovoUser
	{
		/**
		 * Instantiates the object
		 * @param int $user_id The PK of the user, pass false if you want to get user data from username
		 * @param string $identifier Either the username or email of the user, pass in false to get data from PK
		 * @return false Returns false if error loading user data
		 * @access public
		 * @since 3.0
		 */
		function __construct($user_id = false, $identifier = false)
		{
			require_once 'ovusersecurity.php';
			$this->ovUserSecurity = new ovUserSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			if ($this->LoadUserData($user_id, $identifier) == false) {
				return false;
			}
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * @return int The PK of the user
		 * @access public
		 * @since 3.0
		 */
		public function ID() { return $this->_id; }
		
		/**
		 * @return string The username of the user
		 * @access public
		 * @since 3.0
		 */
		public function Username() { return $this->_username; }
		
		/**
		 * @return string The encrypted password of the user
		 * @access public
		 * @since 3.0
		 */
		public function Password() { return $this->_password; }
		
		/**
		 * @return string The email address of the user
		 * @access public
		 * @since 3.0
		 */
		public function Email() { return $this->_email; }
		
		/**
		 * @return string The avatar of the user
		 * @access public
		 * @since 3.0
		 */
		public function Avatar() { return $this->_avatar; }
		
		/**
		 * @return string The details of the user
		 * @access public
		 * @since 3.0
		 */
		public function Details() { return $this->_details; }
		
		/**
		 * @return string The details (unformatted) of the user
		 * @access public
		 * @since 3.0
		 */
		public function UnformattedDetails() { return $this->_unformatted_details; }
		
		/**
		 * @return string The website of the user
		 * @access public
		 * @since 3.0
		 */
		public function Website() { return $this->_website; }
		
		/**
		 * @return string The location of the user
		 * @access public
		 * @since 3.0
		 */
		public function Location() { return $this->_location; }

		/**
		 * @return string The twitter username of the user
		 * @access public
		 * @since 3.3
		 */
		public function TwitterUsername() { return $this->_twitter_username; }
		
		/**
		 * @return int The karma points of the user
		 * @access public
		 * @since 3.0
		 */
		public function KarmaPoints() { return $this->_karma_points; }
		
		/**
		 * @return bool Suspended flag
		 * @access public
		 * @since 3.0
		 */
		public function Suspended() { return $this->_suspended; }
		
		/**
		 * @return bool Banned flag
		 * @access public
		 * @since 3.0
		 */
		public function Banned() { return $this->_banned; }
		
		/**
		 * @return string Reason for Ban
		 * @access public
		 * @since 3.0
		 */
		public function BanReason() { return $this->_ban_reason; }
		
		/**
		 * @return string Username of admin who banned user
		 * @access public
		 * @since 3.0
		 */
		public function BanAdminUsername() { return $this->_ban_admin_username; }
		
		/**
		 * @return string Full name of admin who banned user
		 * @access public
		 * @since 3.0
		 */
		public function BanAdminFullName() { return $this->_ban_admin_full_name; }
		
		/**
		 * @return array User Stats
		 * @access public
		 * @since 3.2
		 */
		public function UserStats() { return $this->_user_stats; }
		
		/**
		 * Loads the user data
		 * @param $user_id int The PK of the user
		 * @param $identifier Email or Username of user
		 * @return false Returns false if error
		 * @access public
		 * @since 3.0
		 */		
		public function LoadUserData($user_id = false, $identifier = false)
		{
			if ($user_id) {
				$user_id = mysql_escape_string($user_id);
				$query = "CALL GetUserInfoByID(" . ovDBConnector::SiteID() . ", $user_id)";
			} else {
				$identifier = mysql_escape_string($identifier);
				$query = "CALL GetUserInfoByIdentifier(" . ovDBConnector::SiteID() . ", '$identifier')";
			}
			
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				$this->_id = $row['id'];
				$this->_username = stripslashes($row['username']);
				$this->_password = $row['password'];
				$this->_email = stripslashes($row['email']);
				$this->_avatar = $row['avatar'];
				$this->_details = $this->ovUtilities->FormatBody($this->ovUtilities->parseURL($row['details']));
				$this->_unformatted_details = $this->ovUtilities->FormatBody($row['details'], false);
				$this->_website = stripslashes($row['website']);
				$this->_location = stripslashes($row['location']);
				$this->_twitter_username = stripslashes($row['twitter_username']);
				$this->_karma_points = floor($row['karma_points']);
				
				if ($row['suspended'] == 1) {
					$this->_suspended = true;
				} else {
					$this->_suspended = false;
				}
				
				if ($row['banned'] == 1) {
					$this->_banned = true;
				} else {
					$this->_banned = false;
				}
				
				$this->_ban_reason = $this->ovUtilities->FormatBody($row['ban_reason']);
				$this->_ban_admin_username = stripslashes($row['banned_by_admin_username']);
				$this->_ban_admin_full_name = stripslashes($row['banned_by_admin_full_name']);
				
				
				ovDBConnector::FreeResult();
				
				$this->_user_stats = $this->GetUserStats();
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return false;
			}			
		}
		
		/**
		 * Gets the stats of the user
		 * @return array User Stats
		 * @access public
		 * @since 3.2
		 */
		public function GetUserStats()
		{
			$query = "CALL GetUserStats(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				$stats['num_submissions'] = 0;
				$stats['num_comments'] = 0;
				$stats['num_likes'] = 0;
				$stats['num_dislikes'] = 0;
				$stats['num_votes'] = 0;
				$stats['num_favorites'] = 0;
				$stats['join_date'] = "Unknown";
				
				return $stats;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				$stats['num_submissions'] = $row['num_submissions'];
				$stats['num_comments'] = $row['num_comments'];
				$stats['num_likes'] = $row['num_likes'];
				$stats['num_dislikes'] = $row['num_dislikes'];
				$stats['num_votes'] = $row['num_votes'];
				$stats['num_favorites'] = $row['num_favorites'];
				$stats['join_date'] = $row['join_date'];
				
				ovDBConnector::FreeResult($result);
			
				return $stats;
			}
			else 
			{
				ovDBConnector::FreeResult();
				$stats['num_submissions'] = 0;
				$stats['num_comments'] = 0;
				$stats['num_likes'] = 0;
				$stats['num_dislikes'] = 0;
				$stats['num_votes'] = 0;
				$stats['num_favorites'] = 0;
				$stats['join_date'] = "Unknown";
				
				return $stats;
			}
		}
		
		/**
		 * Gets the karma points of the user
		 * @return int Karma points user has
		 * @access public
		 * @since 3.0
		 */
		protected function GetKarmaPoints()
		{
			$query = "CALL GetKarmaPoints(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
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
		 * Gets the count of the user's favorites
		 * @return int Count of favorites
		 * @access public
		 * @since 3.0
		 */
		public function GetFavoriteCount()
		{
			$query = "CALL GetUserFavoriteCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_favorites'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the favorite comments and submissions of the user
		 * @param int $page_number Current page number
		 * @return array|false Returns an array of favorites or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetFavorites($page_number)
		{
			$num_favs = $this->GetFavoriteCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_favs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserFavorites(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('favorites' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0) 
			{
				$favorites = array();
				while ($row = $result->fetch_assoc())
				{
					$favorite['favorite_type'] = $row['favorite_type'];
					$favorite['submission_id'] = $row['submission_id'];
					$favorite['submission_type'] = stripslashes($row['submission_type']);
					$favorite['submission_title'] = stripslashes($row['submission_title']);
					$favorite['submission_summary'] = $this->ovUtilities->FormatBody($row['submission_summary'], false);
					$favorite['favorite_date'] = $row['favorite_date'];
					$favorite['comment_username'] = stripslashes($row['comment_username']);
					$favorite['comment_id'] = $row['comment_id'];
					$favorite['comment_body'] = $this->ovUtilities->parseURL($this->ovUtilities->FormatBody($row['comment_body']));
					
					array_push($favorites, $favorite);
				}
				ovDBConnector::FreeResult($result);
				return array('favorites' => $favorites, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return array('favorites' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the count of submissions posted by the user
		 * @return int Number of submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetSubmissionCount()
		{
			$query = "CALL GetUserSubmissionCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_submissions'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the submissions posted by the user
		 * @param $offset int The offset for the SQL query
		 * @param $limit int Number of results to return
		 * @return array|false Returns an array of submissions or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetSubmissions($page_number)
		{
			$num_subs = $this->GetSubmissionCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserSubmissions(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('submissions' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0) 
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = stripslashes($row['score']);
					$submission['thumbnail'] = $row['thumbnail'];

					if ($row['popular'] == 1) {
						$submission['popular'] = true;
					} else {
						$submission['popular'] = false;
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];

					if ($row['can_edit'] == 1) {
						$submission['can_edit'] = true;
					} else {
						$submission['can_edit'] = false;
					}

					$submission['location'] = $row['location'];
					$submission['user_id'] = $row['user_id'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $row['avatar'];
					
					array_push($submissions, $submission);
				}
				ovDBConnector::FreeResult($result);
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the count of comments posted by the user
		 * @return int Number of comments
		 * @access public
		 * @since 3.0
		 */
		public function GetCommentCount()
		{
			$query = "CALL GetUserCommentCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_comments'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the comments posted the user
		 * @param int Current page number
		 * @return array|false Returns an array of comments or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetComments($page_number)
		{
			$num_comments = $this->GetCommentCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_comments);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserComments(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return array('comments' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0)
			{
				$comments = array();
				while ($row = $result->fetch_assoc())
				{
					$comment['id'] = $row['comment_id'];
					$comment['body'] = $this->ovUtilities->FormatBody($row['body']);
					$comment['score'] = $row['score'];
					$comment['submission_id'] = $row['submission_id'];
					$comment['submission_type'] = $row['type'];
					$comment['submission_title'] = stripslashes($row['title']);
					$comment['date'] = $this->ovUtilities->CalculateTimeAgo($row['date_created']);
					$comment['submission_url'] = "/" . strtolower($comment['submission_type']) . "/" . $comment['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($comment['submission_title']) . "#comment-" . $comment['id'];
					
					array_push($comments, $comment);
				}
				ovDBConnector::FreeResult();
				return array('comments' => $comments, 'last-page' => $limits[2]);
			}
			else 
			{
				ovDBConnector::FreeResult();
				return array('comments' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the count of votes by the user
		 * @return int Number of votes
		 * @access public
		 * @since 3.0
		 * @deprecated Deprecated in version 3.2
		 */
		public function GetVoteCount()
		{
			$query = "CALL GetUserVoteCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult();
				return $row['num_votes'];
			}
			else {
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Gets the votes made by the user
		 * @param $offset int The offset for the SQL query
		 * @param $limit int Number of results to return
		 * @return array|false Returns an array of votes or FALSE on error
		 * @access public
		 * @deprecated Deprecated in version 3.2
		 * @since 3.0
		 */
		public function GetVotes($offset, $limit)
		{
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			$query = "CALL GetUserVotes(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$votes = array();
				while ($row = $result->fetch_assoc())
				{
					$vote['direction'] = $row['direction'];
					$vote['submission_id'] = $row['submission_id'];
					$vote['submission_type'] = $row['type'];
					$vote['submission_title'] = stripslashes($row['title']);
					$vote['date'] = $this->ovUtilities->CalculateTimeAgo($row['date_created']);
					$vote['submission_url'] = "/" . strtolower($vote['submission_type']) . "/" . $vote['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($vote['submission_title']);
					
					array_push($votes, $vote);
				}
				ovDBConnector::FreeResult();
				return $votes;
			}
			else 
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets 14 random users the user is following
		 * @return array|false Returns an array of users or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetRandomFollowing()
		{
			$query = "CALL GetUserRandomFollowing(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$following = array();
				while ($row = $result->fetch_assoc())
				{
					$user['id'] = $row['id'];
					$user['username'] = stripslashes($row['username']);
					$user['avatar'] = stripslashes($row['avatar']);
					
					array_push($following, $user);
				}
				ovDBConnector::FreeResult();
				return $following;
			}
			else 
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets 14 random users the user is being followed by
		 * @return array|false Returns an array of users or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetRandomFollowers()
		{
			$query = "CALL GetUserRandomFollowers(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$followers = array();
				while ($row = $result->fetch_assoc())
				{
					$user['id'] = $row['id'];
					$user['username'] = stripslashes($row['username']);
					$user['avatar'] = stripslashes($row['avatar']);
					
					array_push($followers, $user);
				}
				
				ovDBConnector::FreeResult();
				
				return $followers;
			}
			else 
			{
				ovDBConnector::FreeResult();
				
				return false;
			}
		}
		
		/**
		 * Gets the users the user is following
		 * @return array|false Returns an array of users or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetFollowing()
		{
			$query = "CALL GetUserFollowing(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$following = array();
				while ($row = $result->fetch_assoc())
				{
					$user['id'] = $row['id'];
					$user['username'] = stripslashes($row['username']);
					$user['avatar'] = $row['avatar'];
					$user['details'] = $this->ovUtilities->FormatBody($row['details'], false);
					
					if (strlen($user['details']) > 140) {
						$user['details'] = substr($user['details'], 0, 140) . "...";
					}
					
					array_push($following, $user);
				}
				
				ovDBConnector::FreeResult();				
				
				return $following;
			}
			else 
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets users following the user
		 * @return array|false Returns an array of users or FALSE on error
		 * @access public
		 * @since 3.0
		 */
		public function GetFollowers()
		{
			$query = "CALL GetUserFollowers(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$followers = array();
				while ($row = $result->fetch_assoc())
				{
					$user['id'] = $row['id'];
					$user['username'] = stripslashes($row['username']);
					$user['avatar'] = $row['avatar'];
					$user['details'] = $this->ovUtilities->FormatBody($row['details']);
					
					if (strlen($user['details']) > 140) {
						$user['details'] = substr($user['details'], 0, 140) . "...";
					}
					
					array_push($followers, $user);
				}
				
				ovDBConnector::FreeResult();
				
				return $followers;
			}
			else 
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/**
		 * Gets users following the user in a given list
		 * @param $list_id int PK of list
		 * @return array|false Returns an array of users or FALSE on error
		 * @access public
		 * @since 3.3
		 */
		public function GetFollowersInList($list_id)
		{
			$list_id = mysql_escape_string($list_id);

			$query = "CALL GetUserFollowersInList(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $list_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$followers = array();
				while ($row = $result->fetch_assoc())
				{
					$user['id'] = $row['id'];
					$user['username'] = stripslashes($row['username']);
					$user['avatar'] = $row['avatar'];
					$user['details'] = $this->ovUtilities->FormatBody($row['details']);
					
					if (strlen($user['details']) > 140) {
						$user['details'] = substr($user['details'], 0, 140) . "...";
					}
					
					array_push($followers, $user);
				}
				
				ovDBConnector::FreeResult();
				
				return $followers;
			}
			else 
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Checks to see if the user is following the specified user
		 * @param $user_id int The PK of the user to check
		 * @return bool Following flag
		 * @access public
		 * @since 3.0
		 */
		public function IsUserFollowing($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL IsUserFollowing(" . ovDBConnector::SiteID() . ", " . $this->ovUserSecurity->LoggedInUserID() . ", $user_id)";
				$result = ovDBConnector::Query($query);
			
				if (!$result) {
					// ERROR
					return false;
				}
			
				if ($result->num_rows > 0) {
					ovDBConnector::FreeResult();
					return true;
				} else {
					ovDBConnector::FreeResult();
					return false;
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Checks to see if the specified user is following the user
		 * @param $user_id int The PK of the user to check
		 * @return bool Following flag
		 * @access public
		 * @since 3.0
		 */
		public function IsUserFollowingYou($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL IsUserFollowing(" . ovDBConnector::SiteID() . ", $user_id, " . $this->ovUserSecurity->LoggedInUserID() . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// ERROR
					return false;
				}
			
				if ($result->num_rows > 0) {
					ovDBConnector::FreeResult();
					return true;
				} else {
					ovDBConnector::FreeResult();
					return false;
				}
			} else {
				return false;
			}
		}
		
		/**
		 * Saves the user's profile
		 * @param $details string Details for the user
		 * @param $website string Website for the user
		 * @param $location string Location for the user
		 * @param $twitter_username string Twitter Username
		 * @access public
		 * @since 3.0
		 */
		public function SaveProfile($details, $email, $website, $location, $twitter_username)
		{
			$details = mysql_escape_string($details);
			$website = mysql_escape_string($website);
			$email = mysql_escape_string($email);
			$location = mysql_escape_string($location);
			$twitter_username = mysql_escape_string($twitter_username);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL UpdateUserProfile(" . ovDBConnector::SiteID() . ", " . $this->_id . ", '$details', '$email', '$website', '$location', '$twitter_username')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			} else {
				header("Location: /login?redirecturl=" . urlencode("/settings/profile"));
				exit();
			}
		}
		
		/**
		 * Changes the user's password
		 * @param string $current_password user's current password
		 * @param string $new_password_1 user's new password
		 * @param string $new_password_2 user's new password re-entered
		 * @return string response code
		 * @access public
		 * @since 3.0
		 */
		public function ChangePassword($current_password, $new_password_1, $new_password_2)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				require_once 'ovcryptography.php';
				$ovCryptography = new ovCryptography();
			
				$query = "CALL GetUserLoginInfo(" . ovDBConnector::SiteID() . ", '" . mysql_escape_string($this->_username) . "')";
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
				
					if ($db_password != $ovCryptography->OVEncrypt($current_password, $row['password_salt'], $row['password_key'])) {
						return "invalidpassword";
					}
					
					$salt = $ovCryptography->GetSalt();
					$key = $ovCryptography->GetKey();
				
					$query = "CALL UpdateUserPassword(" . ovDBConnector::SiteID() . ", " . $this->_id . ", '" . $ovCryptography->OVEncrypt($new_password_1, $salt, $key) . "', '$salt', '$key')";
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
				header("Location: /login?redirecturl=" . urlencode("/settings/password"));
				exit();
			}
		}
		
		/**
		 * Saves the user's settings
		 * @param $reset_start_page bool Reset the start page of the user (Not currently in use, just pass in false)
		 * @param $open_links_in string Where should the links be opened in (_blank for new tab, _self for same window)
		 * @param $subscribe_submit bool Subscribe to submission on submitting it
		 * @param $subscribe_comment bool Subscribe to submission on posting a comment to it
		 * @param $hide_comments int Score threshold to hide a comment 
		 * @param $prepopulate_reply int Prepopulate the reply textbox
		 * @param $publicly_display_likes int Publicly display likes and dislikes (1 = YES, 0 = NO) 
		 * @access public
		 * @since 3.0
		 */
		public function SaveSettings($reset_start_page, $open_links_in, $subscribe_submit, $subscribe_comment, $hide_comments, $prepopulate_reply, $publicly_display_likes)
		{
			$open_links_in = mysql_escape_string($open_links_in);
			$subscribe_submit = mysql_escape_string($subscribe_submit);
			$subscribe_comment = mysql_escape_string($subscribe_comment);
			$prepopulate_reply = mysql_escape_string($prepopulate_reply);
			$publicly_display_likes = mysql_escape_string($publicly_display_likes);
			
			//if ($reset_start_page) {
			//	$query = "CALL ResetUserStartPage(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			//	ovDBConnector::ExecuteNonQuery($query);
			//	ovDBConnector::FreeResult();
			//}
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL UpdateUserSettings(" . ovDBConnector::SiteID() . ", " . $this->_id . ", '$open_links_in', $subscribe_submit, $subscribe_comment, $hide_comments, $prepopulate_reply, $publicly_display_likes)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			} else {
				header("Location: /login?redirecturl=" . urlencode("/settings/preferences"));
				exit();
			}
		}
		
		/**
		 * Saves the user's notification settings
		 * @param $alert_comments string How the user is alerted to new comments on submissions subscribed to (NONE, SITE, EMAIL, BOTH)
		 * @param $alert_shares string How the user is alerted to new shares (NONE, SITE, EMAIL, BOTH)
		 * @param $alert_messages string How the user is alerted to new messages (NONE, SITE, EMAIL, BOTH) (Not currently in use, just pass in SITE)
		 * @param $alert_followers string How the user is alerted to new followers (NONE, SITE, EMAIL, BOTH)
		 * @param $alert_favorites string How the user is alerted to their submission being favorited (NONE, SITE, EMAIL, BOTH)
		 * @access public
		 * @since 3.0
		 */
		public function SaveNotificationSettings($alert_comments, $alert_shares, $alert_messages, $alert_followers, $alert_favorites)
		{
			$alert_comments = mysql_escape_string($alert_comments);
			$alert_shares = mysql_escape_string($alert_shares);
			$alert_messages = mysql_escape_string($alert_messages);
			$alert_followers = mysql_escape_string($alert_followers);
			$alert_favorites = mysql_escape_string($alert_favorites);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL UpdateUserNotificationSettings(" . ovDBConnector::SiteID() . ", " . $this->_id . ", '$alert_comments', '$alert_shares', '$alert_messages', '$alert_followers', '$alert_favorites')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			} else {
				header("Location: /login?redirecturl=" . urlencode("/settings/notifications"));
				exit();
			}
		}
		
		/**
		 * Saves the user's avatar
		 * @param $filename string The filename of the avatar
		 * @access public
		 * @since 3.0
		 */
		public function SaveAvatar($filename)
		{
			$filename = mysql_escape_string($filename);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL UpdateAvatar(" . ovDBConnector::SiteID() . ", " . $this->_id . ", '" . $filename . "')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			} else {
				header("Location: /login?redirecturl=" . urlencode("/settings/avatar"));
				exit();
			}
		}
		
		/**
		 * Deletes the user's avatar
		 * @access public
		 * @since 3.0
		 */
		public function DeleteAvatar()
		{			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL UpdateAvatar(" . ovDBConnector::SiteID() . ", " . $this->_id . ", '/img/default_user.jpg')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
				
				if (file_exists("./.." . strtolower($this->_avatar))) {
					unlink("./.." . strtolower($this->_avatar));
				}
			} else {
				header("Location: /login?redirecturl=" . urlencode("/settings/avatar"));
				exit();
			}
		}
		
		/**
		 * Gets the users that the user is blocking
		 * @return array|false Array of blocked users or false on Empty or Error
		 * @access public
		 * @since 3.0
		 */
		public function GetBlockedUsers()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL GetBlockedUsers(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
				$result = ovDBConnector::Query($query);

				if (!$result) {
					// ERROR
					return false;
				}

				if ($result->num_rows > 0)
				{
					$blocked_users = array();
					while ($row = $result->fetch_assoc())
					{
						$user['id'] = $row['id'];
						$user['username'] = stripslashes($row['username']);
						$user['avatar'] = $row['avatar'];

						array_push($blocked_users, $user);
					}

					ovDBConnector::FreeResult();

					return $blocked_users;
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
		 * Unblocks a user
		 * @param $user_to_unblock int PK of user to unblock
		 * @access public
		 * @since 3.0
		 */
		public function UnblockUser($user_to_unblock)
		{
			$user_to_unblock = mysql_escape_string($user_to_unblock);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL UnblockUser(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $user_to_unblock)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the count of submissions liked by the user
		 * @return int Number of submissions
		 * @access public
		 * @since 3.2
		 */
		public function GetLikedSubmissionCount()
		{
			$query = "CALL GetUserLikedSubmissionCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_submissions'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the submissions liked by the user
		 * @param $page_number int Current page number
		 * @return array|false Returns an array of submissions or FALSE on error
		 * @access public
		 * @since 3.2
		 */
		public function GetLikedSubmissions($page_number)
		{
			$num_subs = $this->GetLikedSubmissionCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserLikedSubmissions(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('submissions' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0) 
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = stripslashes($row['score']);
					$submission['thumbnail'] = $row['thumbnail'];

					if ($row['popular'] == 1) {
						$submission['popular'] = true;
					} else {
						$submission['popular'] = false;
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];

					if ($row['can_edit'] == 1) {
						$submission['can_edit'] = true;
					} else {
						$submission['can_edit'] = false;
					}

					$submission['location'] = $row['location'];
					$submission['user_id'] = $row['user_id'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $row['avatar'];
					
					array_push($submissions, $submission);
				}
				ovDBConnector::FreeResult($result);
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the count of submissions disliked by the user
		 * @return int Number of submissions
		 * @access public
		 * @since 3.2
		 */
		public function GetDislikedSubmissionCount()
		{
			$query = "CALL GetUserDislikedSubmissionCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_submissions'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the submissions disliked by the user
		 * @param $page_number int Current page number
		 * @return array|false Returns an array of submissions or FALSE on error
		 * @access public
		 * @since 3.2
		 */
		public function GetDislikedSubmissions($page_number)
		{
			$num_subs = $this->GetDislikedSubmissionCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserDislikedSubmissions(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('submissions' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0) 
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = stripslashes($row['score']);
					$submission['thumbnail'] = $row['thumbnail'];

					if ($row['popular'] == 1) {
						$submission['popular'] = true;
					} else {
						$submission['popular'] = false;
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];

					if ($row['can_edit'] == 1) {
						$submission['can_edit'] = true;
					} else {
						$submission['can_edit'] = false;
					}

					$submission['location'] = $row['location'];
					$submission['user_id'] = $row['user_id'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $row['avatar'];
					
					array_push($submissions, $submission);
				}
				ovDBConnector::FreeResult($result);
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the count of recent activities by the user
		 * @return int Number of submissions
		 * @access public
		 * @since 3.2
		 */
		public function GetRecentActivityCount()
		{
			$query = "CALL GetUserRecentActivityCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_activities'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the recent activities by the user
		 * @param $page_number int Current page number
		 * @return array|false Returns an array of submissions or FALSE on error
		 * @access public
		 * @since 3.2
		 */
		public function GetRecentActivity($page_number)
		{
			$num_activities = $this->GetRecentActivityCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_activities);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserRecentActivity(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('activities' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0) 
			{
				$activities = array();
				while ($row = $result->fetch_assoc())
				{
					$activity['activity_type'] = $row['activity_type'];
					$activity['activity_sub_type'] = $row['activity_sub_type'];
					$activity['submission_id'] = $row['submission_id'];
					$activity['submission_type'] = strtolower(stripslashes($row['submission_type']));
					$activity['submission_title'] = stripslashes($row['submission_title']);
					$activity['submission_summary'] = $this->ovUtilities->FormatBody($row['submission_summary'], false);
					$activity['submission_url'] = stripslashes($row['submission_url']);

					$activity['date'] = $row['activity_date'];

					$activity['comment_id'] = stripslashes($row['comment_id']);
					$activity['comment_body'] = stripslashes($row['comment_body']);
					$activity['comment_username'] = stripslashes($row['comment_username']);
					
					array_push($activities, $activity);
				}
				ovDBConnector::FreeResult($result);
				return array('activities' => $activities, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return array('activities' => false, 'last-page' => 1);
			}
		}

		/**
		 * Gets the count of recent activities by the user excluding likes/dislikes
		 * @return int Number of submissions
		 * @access public
		 * @since 3.3
		 */
		public function GetRecentActivityNoLikesCount()
		{
			$query = "CALL GetUserRecentActivityNoLikesCount(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0) 
			{
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult($result);
				return $row['num_activities'];
			}
			else {
				ovDBConnector::FreeResult($result);
				return 0;
			}
		}
		
		/**
		 * Gets the recent activities by the user excluding likes/dislikes
		 * @param $page_number int Current page number
		 * @return array|false Returns an array of submissions or FALSE on error
		 * @access public
		 * @since 3.3
		 */
		public function GetRecentActivityNoLikes($page_number)
		{
			$num_activities = $this->GetRecentActivityCount();
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_activities);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$query = "CALL GetUserRecentNoLikesActivity(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('activities' => false, 'last-page' => 1);
			}
			
			if ($result->num_rows > 0) 
			{
				$activities = array();
				while ($row = $result->fetch_assoc())
				{
					$activity['activity_type'] = $row['activity_type'];
					$activity['activity_sub_type'] = $row['activity_sub_type'];
					$activity['submission_id'] = $row['submission_id'];
					$activity['submission_type'] = strtolower(stripslashes($row['submission_type']));
					$activity['submission_title'] = stripslashes($row['submission_title']);
					$activity['submission_summary'] = $this->ovUtilities->FormatBody($row['submission_summary'], false);
					$activity['submission_url'] = stripslashes($row['submission_url']);

					$activity['date'] = $row['activity_date'];

					$activity['comment_id'] = stripslashes($row['comment_id']);
					$activity['comment_body'] = stripslashes($row['comment_body']);
					$activity['comment_username'] = stripslashes($row['comment_username']);
					
					array_push($activities, $activity);
				}
				ovDBConnector::FreeResult($result);
				return array('activities' => $activities, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult($result);
				return array('activities' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * PK of user
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_id;
		
		/**
		 * Username of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_username;
		
		/**
		 * Password of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_password;
		
		/**
		 * Email of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_email;
		
		/**
		 * Avatar of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_avatar;
		
		/**
		 * Details of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_details;
		
		/**
		 * Unformatted details of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_unformatted_details;
		
		/**
		 * Website of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_website;
		
		/**
		 * Location of user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_location;

		/**
		 * User's Twitter Username
		 * @access protected
		 * @var string
		 * @since 3.3
		 */
		protected $_twitter_username;
		
		/**
		 * Karma points of user
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_karma_points;
		
		/**
		 * Suspended flag
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_suspended;
		
		/**
		 * Banned flag
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_banned;
		
		/**
		 * Reason for ban
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_ban_reason;
		
		/**
		 * Username of admin who banned user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_ban_admin_username;
		
		/**
		 * Full name of admin who banned user
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_ban_admin_full_name;
		
		/**
		 * User Stats Array
		 * @access protected
		 * @var array
		 * @since 3.2
		 */
		protected $_user_stats;
		
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