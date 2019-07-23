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
	 * OpenVoter Admin Content Class
	 * Class dealing with handling the main content
	 *
	 * @package OpenVoter
	 * @subpackage AdminContent
	 * @since 3.0
	 */
	class ovAdminContent
	{
		function __construct()
		{
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			require_once 'ovadminsecurity.php';
			$this->ovAdminSecurity = new ovAdminSecurity();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets the number of submissions in the database
		 * @access public
		 * @return int The number of submissions
		 * @since 3.0
		 */
		public function GetSubmissionCount()
		{
			$query = "CALL ContentGetSubmissionCount(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return 0;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				$num_subs = $row['num_submissions'];
				ovDBConnector::FreeResult();
				return $num_subs;
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Gets Site's Submissions
		 * @access public
		 * @param int $offset Offset for SQL query
		 * @param int $limit Limit for SQL query
		 * @return array|false Array of submissions or false on error or no rows
		 * @since 3.0
		 */
		public function GetSubmissions($offset, $limit)
		{
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$query = "CALL ContentGetSubmissions(" . ovDBConnector::SiteID() . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$submissions = array();
				while($row = $result->fetch_assoc()) {
					$submission['id'] = $row['id'];
					$submission['title'] = stripslashes($row['title']);
					$submission['username'] = stripslashes($row['username']);
					$submission['date'] = $row['submission_date'];
					
					array_push($submissions, $submission);
				}
				
				ovDBConnector::FreeResult();
				
				return $submissions;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the details for a submission
		 * @access public
		 * @param int $submission_id PK of submission
		 * @return array|false Array of details or false on error or no rows
		 * @since 3.0
		 */
		public function GetSubmissionDetails($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			$query = "CALL ContentGetSubmissionDetails($submission_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				$submission['id'] = $row['id'];
				$submission['type'] = strtolower($row['type']);
				$submission['title'] = stripslashes($row['title']);
				$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], true);
				
				$submission['date'] = $row['submission_date'];
				$submission['url'] = stripslashes($row['url']);
				if ($row['popular'] == 1) {
					$submission['popular'] = "Yes";
				} else {
					$submission['popular'] = "No";
				}
				$submission['popular_date'] = $row['popular_date'];
				if ($row['location'] != "") {
					$submission['location'] = stripslashes($row['location']);
				} else {
					$submission['location'] = "None Specified";
				}
				$submission['user_id'] = $row['user_id'];
				$submission['username'] = stripslashes($row['username']);
				$submission['page_url'] = "/" . strtolower($row['type']) . "/" . $row['id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']);
				
				ovDBConnector::FreeResult();
				
				$submission['score'] = $this->GetSubmissionScore($submission['id']);
				return $submission;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Edits a submission
		 * @access public
		 * @param int $submission_id PK of submission
		 * @param string $title Title of submission
		 * @param string $summary Summary of submission
		 * @param string $url URL of submission
		 * @since 3.0
		 */
		public function EditSubmission($submission_id, $title, $summary, $url)
		{
			$submission_id = mysql_escape_string($submission_id);
			$title = mysql_escape_string($title);
			$summary = mysql_escape_string($summary);
			$url = mysql_escape_string($url);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL ContentEditSubmission($submission_id, '$title', '$summary', '$url')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Deletes a submission
		 * @access public
		 * @param int $submission_id The PK of the submission to delete
		 * @since 3.0
		 */
		public function DeleteSubmission($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL DeleteSubmission(" . ovDBConnector::SiteID() . ", $submission_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the number of comments in the database
		 * @access public
		 * @return int The number of comments
		 * @since 3.0
		 */
		public function GetCommentCount()
		{
			$query = "CALL ContentGetCommentCount(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return 0;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				$num_subs = $row['num_comments'];
				ovDBConnector::FreeResult();
				return $num_subs;
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Gets Site's Comments
		 * @access public
		 * @param int $offset Offset for SQL query
		 * @param int $limit Limit for SQL query
		 * @return array|false Array of comments or false on error or no rows
		 * @since 3.0
		 */
		public function GetComments($offset, $limit)
		{
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$query = "CALL ContentGetComments(" . ovDBConnector::SiteID() . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$comments = array();
				while($row = $result->fetch_assoc()) {
					$comment['id'] = $row['id'];
					$comment['date'] = $row['comment_date'];
					$comment['username'] = stripslashes($row['username']);
					$comment['title'] = stripslashes($row['title']);
					
					array_push($comments, $comment);
				}
				
				ovDBConnector::FreeResult();
				
				return $comments;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the details for a comment
		 * @access public
		 * @param int $comment_id PK of comment
		 * @return array|false Array of details or false on error or no rows
		 * @since 3.0
		 */
		public function GetCommentDetails($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			$query = "CALL ContentGetCommentDetails($comment_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$comment['id'] = $row['id'];
				$comment['date'] = $row['comment_date'];
				$comment['body'] = $this->ovUtilities->FormatBody($row['body']);
				$comment['submission_id'] = $row['submission_id'];
				$comment['type'] = strtolower($row['type']);
				$comment['title'] = stripslashes($row['title']);
				$comment['user_id'] = $row['user_id'];
				$comment['username'] = stripslashes($row['username']);
				$comment['page_url'] = "/" . strtolower($row['type']) . "/" . $row['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']);
				
				ovDBConnector::FreeResult();
				return $comment;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Deletes a comment
		 * @access public
		 * @param int $comment_id The PK of the comment to delete
		 * @since 3.0
		 */
		public function DeleteComment($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL DeleteComment(" . ovDBConnector::SiteID() . ", $comment_id, 0)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the number of users in the database
		 * @access public
		 * @return int The number of users
		 * @since 3.0
		 */
		public function GetUserCount($filter = "")
		{
			$filter = mysql_escape_string($filter);
			if ($filter != "") {
				$query = "CALL ContentGetUserCount(" . ovDBConnector::SiteID() . ", '$filter')";
			} else {
				$query = "CALL ContentGetUserCount(" . ovDBConnector::SiteID() . ", '')";
			}

			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return 0;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				$num_subs = $row['num_users'];
				ovDBConnector::FreeResult();
				return $num_subs;
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Gets Site's Users
		 * @access public
		 * @param int $offset Offset for SQL query
		 * @param int $limit Limit for SQL query
		 * @return array|false Array of users or false on error or no rows
		 * @since 3.0
		 */
		public function GetUsers($offset, $limit, $filter = "")
		{
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$filter = mysql_escape_string($filter);
			
			if ($filter != "") {
				$query = "CALL ContentGetUsers(" . ovDBConnector::SiteID() . ", $offset, $limit, '$filter')";
			} else {
				$query = "CALL ContentGetUsers(" . ovDBConnector::SiteID() . ", $offset, $limit, '')";
			}
			
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$users = array();
				while($row = $result->fetch_assoc()) {
					$user['id'] = $row['id'];
					$user['date_suspended'] = $row['date_suspended'];
					$user['username'] = stripslashes($row['username']);
					if ($row['suspended'] == 1) {
						$user['suspended'] = true;
					} else {
						$user['suspended'] = false;
					}
					
					array_push($users, $user);
				}
				
				ovDBConnector::FreeResult();
				
				return $users;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		public function GetUserIPAddresses($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$query = "CALL GetUserIPAddresses($user_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$ips = array();
				while($row = $result->fetch_assoc()) {
					array_push($ips, $row['ip_address']);
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
		 * Gets the number of suspended users in the database
		 * @access public
		 * @return int The number of suspended users
		 * @since 3.0
		 */
		public function GetSuspendedUserCount()
		{
			$query = "CALL ContentGetSuspendedUserCount(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return 0;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				$num_subs = $row['num_users'];
				ovDBConnector::FreeResult();
				return $num_subs;
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Gets Site's suspended Users
		 * @access public
		 * @param int $offset Offset for SQL query
		 * @param int $limit Limit for SQL query
		 * @return array|false Array of suspended users or false on error or no rows
		 * @since 3.0
		 */
		public function GetSuspendedUsers($offset, $limit)
		{
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$query = "CALL ContentGetSuspendedUsers(" . ovDBConnector::SiteID() . ", $offset, $limit)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$users = array();
				while($row = $result->fetch_assoc()) {
					$user['id'] = $row['id'];
					$user['date_suspended'] = $row['date_suspended'];
					$user['username'] = stripslashes($row['username']);
					if ($row['suspended'] == 1) {
						$user['suspended'] = true;
					} else {
						$user['suspended'] = false;
					}
					
					array_push($users, $user);
				}
				
				ovDBConnector::FreeResult();
				
				return $users;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the details for a user
		 * @access public
		 * @param int $user_id PK of user
		 * @return array|false Array of details or false on error or no rows
		 * @since 3.0
		 */
		public function GetUserDetails($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$query = "CALL ContentGetUserDetails($user_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$user['id'] = $row['id'];
				$user['date'] = $row['join_date'];
				$user['username'] = stripslashes($row['username']);
				$user['email'] = stripslashes($row['email']);
				$user['details'] = $this->ovUtilities->FormatBody($row['details']);
				$user['avatar'] = stripslashes($row['avatar']);
				$user['website'] = stripslashes($row['website']);
				$user['location'] = stripslashes($row['location']);
				
				if ($row['suspended'] == 1) {
					$user['suspended'] = "Yes";
				} else {
					$user['suspended'] = "No";
				}
				
				$user['date_suspended'] = $row['date_suspended'];
				
				ovDBConnector::FreeResult();
				
				$user['ip_addresses'] = $this->GetUserIPAddresses($row['id']);
				
				
				return $user;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Checks if a user is suspended
		 * @access public
		 * @param string $username The username of the user to check
		 * @return bool True if suspended, false if not
		 * @since 3.0
		 */
		public function IsUserSuspendedByUsername($username)
		{
			$username = mysql_escape_string($username);
			$query = "CALL IsUserSuspendedByUsername(" . ovDBConnector::SiteID() . ", '$username')";
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
		 * Checks if a user is suspended
		 * @access public
		 * @param int $user_id The PK of the user to check
		 * @return bool True if suspended, false if not
		 * @since 3.0
		 */
		public function IsUserSuspendedByID($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$query = "CALL IsUserSuspendedByID(" . ovDBConnector::SiteID() . ", $user_id)";
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
		 * Suspends a user
		 * @access public
		 * @param int $user_id The PK of the user to check
		 * @since 3.0
		 */
		public function SuspendUserByID($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL SuspendUserByID(" . ovDBConnector::SiteID() . ", $user_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Suspends a user
		 * @access public
		 * @param int $username The username of the user to check
		 * @since 3.0
		 */
		public function SuspendUserByUsername($username)
		{
			$username = mysql_escape_string($username);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL SuspendUserByUsername(" . ovDBConnector::SiteID() . ", '$username')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Removes the Suspension of a user
		 * @access public
		 * @param int $user_id The PK of the user to check
		 * @since 3.0
		 */
		public function UnsuspendUserByID($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnsuspendUserByID(" . ovDBConnector::SiteID() . ", $user_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Removes the Suspension of a user
		 * @access public
		 * @param int $username The username of the user to check
		 * @since 3.0
		 */
		public function UnsuspendUserByUsername($username)
		{
			$username = mysql_escape_string($username);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL UnsuspendUserByUsername(" . ovDBConnector::SiteID() . ", '$username')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Deletes a user
		 * @access public
		 * @param int $user_id The PK of the user to delete
		 * @since 3.0
		 */
		public function DeleteUser($user_id)
		{
			$user_id = mysql_escape_string($user_id);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL DeleteUser($user_id, 0)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Bans a user
		 * @access public
		 * @param int $user_id The PK of the user to ban
		 * @param string $reason Reason for banning
		 * @since 3.0
		 */
		public function BanUser($user_id, $reason)
		{
			$user_id = mysql_escape_string($user_id);
			$reason = mysql_escape_string($reason);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL BanUser($user_id, " . $this->ovAdminSecurity->LoggedInAdminID() . ", '$reason')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the feedback from users
		 * @access public
		 * @return array|false Returns an array of feedback or false on error or no rows
		 * @since 3.0
		 */
		public function GetFeedback()
		{
			$query = "CALL GetFeedback(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$feedback = array();
				while($row = $result->fetch_assoc()) {
					$fb['id'] = $row['id'];
					$fb['date'] = $row['message_date'];
					$fb['name'] = stripslashes($row['name']);
					$fb['email'] = stripslashes($row['email']);
					$fb['reason'] = stripslashes($row['reason']);
					$fb['message'] = $this->ovUtilities->FormatBody($row['message']);
					
					if ($row['unread'] == 1) {
						$fb['unread'] = true;
					} else {
						$fb['unread'] = false;
					}
					
					array_push($feedback, $fb);
				}
				
				ovDBConnector::FreeResult();
				
				return $feedback;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the feedback message
		 * @access public
		 * @param int $feedback_id PK of feedback message
		 * @return array|false Array with message or false on error or no rows
		 * @since 3.0
		 */
		public function GetFeedbackMessage($feedback_id)
		{
			$feedback_id = mysql_escape_string($feedback_id);
			$query = "CALL GetFeedbackMessage(" . ovDBConnector::SiteID() . ", $feedback_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$fb['id'] = $row['id'];
				$fb['date'] = $row['message_date'];
				$fb['name'] = stripslashes($row['name']);
				$fb['email'] = stripslashes($row['email']);
				$fb['reason'] = stripslashes($row['reason']);
				$fb['message'] = $this->ovUtilities->FormatBody($row['message']);

				ovDBConnector::FreeResult();
				
				return $fb;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Deletes a feedback message
		 * @access public
		 * @param int $feedback_id PK of feedback message
		 * @since 3.0
		 */
		public function DeleteFeedbackMessage($feedback_id)
		{
			$feedback_id = mysql_escape_string($feedback_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL DeleteFeedbackMessage($feedback_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Marks a feedback message read
		 * @access public
		 * @param int $feedback_id PK of feedback message
		 * @since 3.0
		 */
		public function MarkFeedbackRead($feedback_id)
		{
			$feedback_id = mysql_escape_string($feedback_id);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL MarkFeedbackRead($feedback_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Marks a feedback message unread
		 * @access public
		 * @param int $feedback_id PK of feedback message
		 * @since 3.0
		 */
		public function MarkFeedbackUnread($feedback_id)
		{
			$feedback_id = mysql_escape_string($feedback_id);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL MarkFeedbackUnread($feedback_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the count of unread feedback messages
		 * @access public
		 * @return int message count
		 * @since 3.0
		 */
		public function GetUnreadFeedbackCount()
		{
			$query = "CALL GetUnreadFeedbackCount(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return 0;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$num_messages = $row['num_messages'];

				ovDBConnector::FreeResult();
				
				return $num_messages;
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		public function GetCommentVotingRecord($username, $start_date, $direction)
		{
			$username = mysql_escape_string($username);
			$start_date = mysql_escape_string($start_date);
			$direction = mysql_escape_string($direction);
			
			$query = "CALL GetUserCommentVotingRecord(" . ovDBConnector::SiteID() . ", '$username', '$start_date', $direction)";
			$result = ovDBConnector::Query($query);
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$record = array();
				while($row = $result->fetch_assoc()) {
					$vote['username'] = stripslashes($row['username']);
					$vote['submission'] = stripslashes($row['title']);
					$vote['direction'] = $row['direction'];
					$vote['date'] = $this->ovUtilities->FormatDate($row['date_created']);
					$vote['type'] = strtolower($row['type']);
					$vote['id'] = $row['id'];
					$vote['link'] = "/" . $vote['type'] . "/" . $vote['id'] . "/" . $this->ovUtilities->ConvertToUrl($vote['submission']) . "#comment-" . $row['comment_id'];

					array_push($record, $vote);
				}
				
				ovDBConnector::FreeResult();
				
				return $record;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		public function GetSubmissionVotingRecord($username, $start_date, $direction)
		{
			$username = mysql_escape_string($username);
			$start_date = mysql_escape_string($start_date);
			$direction = mysql_escape_string($direction);
			
			$query = "CALL GetUserSubmissionVotingRecord(" . ovDBConnector::SiteID() . ", '$username', '$start_date', $direction)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$record = array();
				while($row = $result->fetch_assoc()) {
					$vote['username'] = stripslashes($row['username']);
					$vote['submission'] = stripslashes($row['title']);
					$vote['direction'] = $row['direction'];
					$vote['date'] = $this->ovUtilities->FormatDate($row['date_created']);
					$vote['type'] = strtolower($row['type']);
					$vote['id'] = $row['id'];
					$vote['link'] = "/" . $vote['type'] . "/" . $vote['id'] . "/" . $this->ovUtilities->ConvertToUrl($vote['submission']);

					array_push($record, $vote);
				}
				
				ovDBConnector::FreeResult();
				
				return $record;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the score of the submission based on the active votes
		 * @param $submission_id int Submission PK
		 * @return int Submission score
		 * @access public
		 * @since 3.2
		 */
		public function GetSubmissionScore($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			$query = "CALL GetSubmissionScore($submission_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return 0;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				$score = $row['score'];
				
				ovDBConnector::FreeResult();
				
				return $score;
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		public function ResetUserPassword($user_id, $password)
		{
			$user_id = mysql_escape_string($user_id);
			
			require_once 'ovcryptography.php';
			$ovCryptography = new ovCryptography();
			
			$salt = $ovCryptography->GetSalt();
			$key = $ovCryptography->GetKey();
			
			$query = "CALL ResetUserPassword($user_id, '" . $ovCryptography->OVEncrypt($password, $salt, $key) . "', '$salt', '$key')";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		protected $ovAdminSecurity;
		protected $ovUtilities;
	}
?>