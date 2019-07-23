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
	 * OpenVoter Comment Class
	 * Class dealing with comments and their respective actions
	 *
	 * @package OpenVoter
	 * @subpackage Comment
	 * @since 3.0
	 */
	class ovComment
	{
		function __construct()
		{
			require_once 'ovdbconnector.php';
			
			require_once 'ovusersecurity.php';
			$this->ovUserSecurity = new ovUserSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets The Comment Count for a Specific Submission
		 * @access public
		 * @param int $submission_id PK of the submission
		 * @return int The number of comments
		 * @since 3.1
		 */
		public function GetCommentCount($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			$query = "CALL GetCommentCount($submission_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return 0;
			}
			
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$comment_count = $row['num_comments'];
				ovDBConnector::FreeResult();
				return $comment_count;
			}
			else {
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Gets The Comments Posted on a specific Submission
		 * @access public
		 * @param int $submission_id PK of the submission
		 * @return array|false returns array of comments or false on error or no comments
		 * @since 3.0
		 */
		public function GetComments($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = mysql_escape_string($this->ovUserSecurity->LoggedInUserID());
			} else {
				$logged_in_user = "NULL";
			}
			
			$query = "CALL GetComments(" . ovDBConnector::SiteID() . ", $submission_id, $logged_in_user)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$comments = array();
				while ($row = $result->fetch_assoc())
				{
					$comment['id'] = $row['comment_id'];
					$comment['body'] = $this->ovUtilities->FormatBody($row['body']);
					$comment['score'] = $row['score'];
					$comment['date'] = $row['date_created'];
					$comment['user_id'] = $row['user_id'];
					$comment['username'] = $row['username'];
					$comment['avatar'] = $row['avatar'];
					$comment['active'] = $row['active'];
					$comment['is_blocked'] = $row['is_blocked'];
					$comment['edited'] = $row['edited'];
					$comment['deleted_by_user'] = $row['deleted_by_user'];
					
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
		 * Gets The Comments Posted as Replies to a given comment
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return array|false returns array of comments or false on error or no comments
		 * @since 3.0
		 */
		public function GetCommentReplies($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = mysql_escape_string($this->ovUserSecurity->LoggedInUserID());
			} else {
				$logged_in_user = "NULL";
			}
			
			$query = "CALL GetCommentReplies(" . ovDBConnector::SiteID() . ", $comment_id, $logged_in_user)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$comments = array();
				while ($row = $result->fetch_assoc())
				{
					$comment['id'] = $row['comment_id'];
					$comment['body'] = $this->ovUtilities->FormatBody($row['body']);
					$comment['score'] = $row['score'];
					$comment['date'] = $row['date_created'];
					$comment['user_id'] = $row['user_id'];
					$comment['username'] = $row['username'];
					$comment['avatar'] = $row['avatar'];
					$comment['active'] = $row['active'];
					$comment['is_blocked'] = $row['is_blocked'];
					$comment['edited'] = $row['edited'];
					$comment['deleted_by_user'] = $row['deleted_by_user'];
					
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
		 * Gets The Details of a specific Comment
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return array|false returns array of the details or false on error or no comments
		 * @since 3.0
		 */
		public function GetCommentDetails($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);

			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = mysql_escape_string($this->ovUserSecurity->LoggedInUserID());
			} else {
				$logged_in_user = "NULL";
			}

			$query = "CALL GetCommentDetails(" . ovDBConnector::SiteID() . ", $comment_id, $logged_in_user)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				$comment['id'] = $row['comment_id'];
				$comment['body'] = $this->ovUtilities->FormatBody($row['body']);
				$comment['score'] = $row['score'];
				$comment['date'] = $this->ovUtilities->CalculateTimeAgo($row['date_created']);
				$comment['user_id'] = $row['user_id'];
				$comment['username'] = $row['username'];
				$comment['avatar'] = $row['avatar'];
				$comment['active'] = $row['active'];
				$comment['is_blocked'] = $row['is_blocked'];
				$comment['edited'] = $row['edited'];
				$comment['deleted_by_user'] = $row['deleted_by_user'];
				
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
		 * Posts a Comment to the Database
		 * @access public
		 * @param string $body Body of the comment
		 * @param int $submission_id PK of submission
		 * @param bool $is_reply Boolean noting if comment is a reply
		 * @param int $comment_replied_to_id The PK of the comment being replied to
		 * @return bool returns true if successful, false if not
		 * @since 3.0
		 */
		public function PostComment($body, $submission_id, $is_reply = false, $comment_replied_to_id = false)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn())
			{
				$original_body = $body;
				$body = mysql_escape_string($body);
				$submission_id = mysql_escape_string($submission_id);
				$comment_replied_to_id = mysql_escape_string($comment_replied_to_id);
			
				if (trim($body) == "") {
					return false;
				}
				
				if (trim ($submission_id) == "") {
					return false;
				}
				
				if ($comment_replied_to_id && trim($comment_replied_to_id) == "") {
					return false;
				}
			
				if ($is_reply) {
					$query = "CALL PostCommentReply(" . ovDBConnector::SiteID() . ", $submission_id, " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ", '$body', $comment_replied_to_id)";
				} else {
					$query = "CALL PostComment(" . ovDBConnector::SiteID() . ", $submission_id, " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ", '$body')";
				}
			
				try
				{
					$result = ovDBConnector::Query($query);
					ovDBConnector::FreeResult();
				}
				catch (Exception $e)
				{
					return false;
				}
				
				require_once 'ovuser.php';
				$ovUser = new ovUser();

				if ($ovUser->SubscribeOnComment($this->ovUserSecurity->LoggedInUserID())) {

					require_once 'ovsubmission.php';
					$ovSubmission = new ovSubmission();
					$ovSubmission->Subscribe($this->ovUserSecurity->LoggedInUserID(), $submission_id);
					
				}
				
				// do alerting
				
				if ($result && $result->num_rows > 0) {
					$row = $result->fetch_assoc();
					$comment_id = $row['comment_id'];
					
					if ($comment_id != 0) {
						require_once 'ovalerting.php';
						$ovAlerting = new ovAlerting();
						$ovAlerting->ProcessNewCommentAlerts($this->ovUserSecurity->LoggedInUserID(), $submission_id, $comment_id);
					} else {
						return array('status' => 'ERROR', 'errorMessage' => 'Your comment may not have been posted, please refresh the page and try again if needed.');
					}


				} else {
					return array('status' => 'ERROR', 'errorMessage' => 'Your comment may not have been posted, please refresh the page and try again if needed.');
				}
				require_once('ovouser.php');
				$loggedInUser = new ovoUser(false, $this->ovUserSecurity->LoggedInUsername());
				
				return array('status' => 'OK', 'commentId' => $comment_id, 'username' => $loggedInUser->Username(), 'userAvatar' => $loggedInUser->Avatar(), 'body' => $this->ovUtilities->FormatBody($original_body));
			}
			else
			{
				return array('status' => 'ERROR', 'errorMessage' => 'You must be logged in to post a comment.');
			}
		}
		
		/**
		 * Adds a vote to a comment
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @param int $direction direction of vote (1 for up vote / -1 for downvote)
		 * @since 3.0
		 */
		public function AddVote($comment_id, $direction)
		{	
			$comment_id = mysql_escape_string($comment_id);
			$direction = mysql_escape_string($direction);
					
			if ($this->ovUserSecurity->IsUserLoggedIn())
			{
				$user_id = mysql_escape_string($this->ovUserSecurity->LoggedInUserID());
				
				$previous_vote = $this->CheckForVote($comment_id);
				
				/*if ($previous_vote) {
					
				} else {
					$query = "CALL CommentVote(" . ovDBConnector::SiteID() . ", $user_id, $comment_id, $direction)";
					ovDBConnector::ExecuteNonQuery($query);
					ovDBConnector::FreeResult();
				}*/
				
				$query = "CALL CommentVote(" . ovDBConnector::SiteID() . ", $user_id, $comment_id, $direction)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Checks to see if the logged in user has voted on the comment
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return int|false returns the direction of the vote or false on error or no vote
		 * @since 3.0
		 */
		public function CheckForVote($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn())
			{
				$user_id = mysql_escape_string($this->ovUserSecurity->LoggedInUserID());
				$query = "CALL GetCommentVote(" . ovDBConnector::SiteID() . ", $user_id, $comment_id)";
				$result = ovDBConnector::Query($query);
				
				if (!$result)
				{
					// error
					return false;
				}
				
				if ($result->num_rows > 0)
				{
					// vote exists
					$row = $result->fetch_assoc();
					
					ovDBConnector::FreeResult();
					
					return $row['direction'];
				}
				else
				{
					// no vote
					ovDBConnector::FreeResult();
					return false;
				}
			}
			else
			{
				// not logged in, no idea what direction
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Checks to see if the comment is marked a favorite by the logged in user
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return bool True if favorite | false if not a favorite
		 * @since 3.0
		 */
		public function IsFavorite($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL IsCommentFavorite(" . ovDBConnector::SiteID() . ", $comment_id, " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
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
			} else {
				return false;
			}
		}
		
		/**
		 * Marks a comment as a favorite for the logged in user
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return false returns false if no user logged in
		 * @since 3.0
		 */
		public function AddFavorite($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL AddCommentFavorite(" . ovDBConnector::SiteID() . ", $comment_id, " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Unmarks a comment as a favorite for the logged in user
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return false returns false if no user logged in
		 * @since 3.0
		 */
		public function DeleteFavorite($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL DeleteCommentFavorite(" . ovDBConnector::SiteID() . ", $comment_id, " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Deletes a comment
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @return nothing|false returns false if no user logged in or no permissions
		 * @since 3.1
		 */
		public function DeleteComment($comment_id)
		{
			$comment = $this->GetCommentDetails($comment_id);
			
			$comment_id = mysql_escape_string($comment_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn() && $this->ovUserSecurity->LoggedInUserID() == $comment['user_id']) 
			{
				$query = "CALL DeleteComment(" . ovDBConnector::SiteID() . ", $comment_id, 1)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Edits a comment
		 * @access public
		 * @param int $comment_id PK of the comment
		 * @param string $body New body of comment
		 * @return nothing|false returns false if no user logged in or no permissions
		 * @since 3.1
		 */
		public function EditComment($comment_id, $body)
		{
			$comment = $this->GetCommentDetails($comment_id);
			
			$comment_id = mysql_escape_string($comment_id);
			$body = mysql_escape_string($body);
			
			if ($this->ovUserSecurity->IsUserLoggedIn() && $this->ovUserSecurity->LoggedInUserID() == $comment['user_id']) 
			{
				$query = "CALL EditComment($comment_id, '$body')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Gets the score of the comment based on the active votes
		 * @param $comment_id int Submission PK
		 * @return int Comment score
		 * @access public
		 * @since 3.2
		 */
		public function GetCommentScore($comment_id)
		{
			$comment_id = mysql_escape_string($comment_id);
			$query = "CALL GetCommentScore($comment_id)";
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
