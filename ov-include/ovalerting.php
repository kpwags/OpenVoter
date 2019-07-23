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
	 * OpenVoter Alerting Class
	 * Class dealing with alerting users of new events and saving the events into the database
	 *
	 * @package OpenVoter
	 * @subpackage Alerting
	 * @since 3.0
	 */
	class ovAlerting
	{
		function __construct()
		{
			require_once 'ovusersecurity.php';
			$this->ovUserSecurity = new ovUserSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			require_once 'ovemailer.php';
			$this->ovEmailer = new ovEmailer();
			
			require_once 'ovsettings.php';
			$this->ovSettings = new ovSettings();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets The Number of Alerts for the Logged In User
		 * @access public
		 * @return int Alert Count
		 * @since 3.0
		 */
		public function GetAlertCount()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL GetAlertCount(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				$result = ovDBConnector::Query($query);
				
				if ($result) {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						ovDBConnector::FreeResult();
						return $row['num_alerts'];
					} else {
						ovDBConnector::FreeResult();
						return 0;
					}
				} else {
					return 0;
				}
			}
			else
			{
				return 0;
			}
		}
		
		/**
		 * Gets The Number of Alerts categorized for the Logged In User
		 * @access public
		 * @return array Categorized alert count array
		 * @since 3.2
		 */
		public function GetAlertCategoryCounts()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL GetAlertCategoryCounts(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				$result = ovDBConnector::Query($query);
				
				if ($result) {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						ovDBConnector::FreeResult();
						
						$alerts['share_alert_count'] = $row['share_alert_count'];
						$alerts['comment_alert_count'] = $row['comment_alert_count'];
						$alerts['follower_alert_count'] = $row['follower_alert_count'];
						$alerts['favorite_alert_count'] = $row['favorite_alert_count'];
						
						return $alerts;
					} else {
						ovDBConnector::FreeResult();
						$alerts['share_alert_count'] = 0;
						$alerts['comment_alert_count'] = 0;
						$alerts['follower_alert_count'] = 0;
						$alerts['favorite_alert_count'] = 0;
						
						return $alerts;
					}
				} else {
					// error
					$alerts['share_alert_count'] = 0;
					$alerts['comment_alert_count'] = 0;
					$alerts['follower_alert_count'] = 0;
					$alerts['favorite_alert_count'] = 0;
					
					return $alerts;
				}
			}
			else
			{
				return 0;
			}
		}
		
		/**
		 * Gets The New Comment Alerts for the Logged In User
		 * @access public
		 * @return array|false Array with alerts or False if no alerts or error
		 * @since 3.0
		 */
		public function GetCommentAlerts()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL GetCommentAlerts(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// error
					return false;
				}

				if ($result->num_rows > 0) 
				{
					$alerts = array();
					while($row = $result->fetch_assoc())
					{
						$alert['id'] = $row['id'];
						$alert['username'] = stripslashes($row['username']);
						$alert['avatar'] = $row['avatar'];
						$alert['title'] = stripslashes($row['title']);
						$alert['comment_id'] = $row['comment_id'];
						$alert['submission'] = "/" . strtolower($row['type']) . "/" . $row['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']) . "#comment-" . $row['comment_id'];
						
						array_push($alerts, $alert);
					}
					ovDBConnector::FreeResult();
					return $alerts;
				}
				else
				{
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
		 * Gets The New Share Alerts for the Logged In User
		 * @access public
		 * @return array|false Array with alerts or False if no alerts or error
		 * @since 3.0
		 */
		public function GetShareAlerts()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL GetShareAlerts(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// error
					return false;
				}

				if ($result->num_rows > 0) 
				{
					$alerts = array();
					while($row = $result->fetch_assoc())
					{
						$alert['id'] = $row['id'];
						$alert['username'] = stripslashes($row['username']);
						$alert['avatar'] = $row['avatar'];
						$alert['message'] = $this->ovUtilities->FormatBody($row['message']);
						$alert['title'] = stripslashes($row['title']);
						$alert['submission'] = "/" . strtolower($row['type']) . "/" . $row['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']);
						
						array_push($alerts, $alert);
					}
					ovDBConnector::FreeResult();
					return $alerts;
				}
				else
				{
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
		 * Gets The New Follower Alerts for the Logged In User
		 * @access public
		 * @return array|false Array with alerts or False if no alerts or error
		 * @since 3.0
		 */
		public function GetFollowerAlerts()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL GetFollowerAlerts(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// error
					return false;
				}

				if ($result->num_rows > 0) 
				{
					$alerts = array();
					while($row = $result->fetch_assoc())
					{
						$alert['id'] = $row['id'];
						$alert['username'] = stripslashes($row['username']);
						$alert['avatar'] = $row['avatar'];
						
						array_push($alerts, $alert);
					}
					ovDBConnector::FreeResult();
					return $alerts;
				}
				else
				{
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
		 * Gets The New Favorite Alerts for the Logged In User
		 * @access public
		 * @return array|false Array with alerts or False if no alerts or error
		 * @since 3.0
		 */
		public function GetFavoriteAlerts()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL GetFavoriteAlerts(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
				$result = ovDBConnector::Query($query);
				
				if (!$result) {
					// error
					return false;
				}

				if ($result->num_rows > 0) 
				{
					$alerts = array();
					while($row = $result->fetch_assoc())
					{
						$alert['id'] = $row['id'];
						$alert['username'] = stripslashes($row['username']);
						$alert['avatar'] = $row['avatar'];
						$alert['title'] = stripslashes($row['title']);
						$alert['submission'] = "/" . strtolower($row['type']) . "/" . $row['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']);
						
						array_push($alerts, $alert);
					}
					ovDBConnector::FreeResult();
					return $alerts;
				}
				else
				{
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
		 * Marks All Alerts Read
		 * @access public
		 * @param string $type Type of alert to mark read (all (default), comments, shares, followers, favorites)
		 * @since 3.0
		 */
		public function MarkAllAlertsRead($type = "all")
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				switch($type)
				{
					case "comments":
						$query = "CALL MarkAllCommentAlertsRead(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
						break;
					case "shares":
						$query = "CALL MarkAllShareAlertsRead(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
						break;
					case "followers":
						$query = "CALL MarkAllFollowerAlertsRead(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
						break;
					case "favorites":
						$query = "CALL MarkAllFavoriteAlertsRead(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
						break;
					case "all":
					default:
						$query = "CALL MarkAllAlertsRead(" . ovDBConnector::SiteID() . ", " . mysql_escape_string($this->ovUserSecurity->LoggedInUserID()) . ")";
						break;
				}
				
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Marks a single alert read
		 * @access public
		 * @param string $type Type of alert to mark read (comments, shares, followers, favorites)
		 * @param int $alert_id PK of alert
		 * @since 3.0
		 */
		public function MarkAlertRead($type, $alert_id)
		{
			$alert_id = mysql_escape_string($alert_id);
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				switch($type)
				{
					case "comments":
						$query = "CALL MarkCommentAlertRead(" . ovDBConnector::SiteID() . ", $alert_id)";
						break;
					case "shares":
						$query = "CALL MarkShareAlertRead(" . ovDBConnector::SiteID() . ", $alert_id)";
						break;
					case "followers":
						$query = "CALL MarkFollowerAlertRead(" . ovDBConnector::SiteID() . ", $alert_id)";
						break;
					case "favorites":
						$query = "CALL MarkFavoriteAlertRead(" . ovDBConnector::SiteID() . ", $alert_id)";
						break;
				}
				
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Processes new alerts for a new comment and sends the email or on-site alert out
		 * @access public
		 * @param int $comment_user_id The PK of the user who posted the comment
		 * @param int $submission_id THE PK of the submission the comment was posted to
		 * @since 3.0
		 */
		public function ProcessNewCommentAlerts($comment_user_id, $submission_id, $comment_id)
		{
			$comment_user_id = mysql_escape_string($comment_user_id);
			$subscribers = $this->GetSubscribers($submission_id);
			
			require_once 'ovuser.php';
			$ovUser = new ovUser();
			
			if ($subscribers)
			{
				foreach($subscribers as $subscriber)
				{
					if ($subscriber != $comment_user_id  && !$ovUser->IsBlocking($subscriber, $comment_user_id)) {
						$alert_settings = $this->GetUserAlertSettings($subscriber);
					
						if ($alert_settings['comments'] == "SITE" || $alert_settings['comments'] == "BOTH") {
							// on site alert
							$this->AddCommentAlert($subscriber, $comment_user_id, $submission_id, $comment_id);
						}
					
						if ($alert_settings['comments'] == "EMAIL" || $alert_settings['comments'] == "BOTH") {
							// send email
							require_once 'ovouser.php';
							require_once 'ovsubmission.php';
							$ovSubmission = new ovSubmission();
							require_once 'ovosubmission.php';

							$comment_user_detail = new ovoUser($comment_user_id);
							$user_detail = new ovoUser($subscriber);
							$submission_detail = new ovoSubmission($ovSubmission->GetSubmissionDetails($submission_id));

							$domain = $this->ovUtilities->GetDomain($this->ovSettings->RootURL());
							$this->ovEmailer->SetFromEmail("alerts@" . $domain);
							$this->ovEmailer->SetSource($this->ovSettings->Title() . " Alerts");

							$subject = $comment_user_detail->Username() . " has posted a new comment on " . $submission_detail->Title() . ".";

							$message = "<html><body>";
							$message .= "<p>Hi " . $user_detail->Username() . "</p>";
							$message .= "<p><a href=\"" . $this->ovSettings->RootURL() . "/users/" . strtolower($comment_user_detail->Username) . "\">" . $comment_user_detail->Username . "</a>";
							$message .= " has posted a new comment on <a href=\"" . $this->ovSettings->RootURL() . $submission_detail->PageURL() . "#comment-$comment_id\">" . $submission_detail->Title() . "</a></p>";
							$message .= "<p>Regards,<br/>" . $this->ovSettings->Title() . "</p>";
							$message .= "</body></html>";

							$this->ovEmailer->SendEmail($user_detail->Email(), $message, $subject);
						}
					}
				}
			}
		}
		
		/**
		 * Processes new alerts for a new comment and sends the email or on-site alert out
		 * @access public
		 * @param int $submission_id THE PK of the submission being shared
		 * @param int $share_user_id The PK of the user who is sharing
		 * @param var $list_id The PK of the list to share to or "all" for all followers
		 * @param string $message Personal message to go along with the share
		 * @since 3.0
		 */
		public function ProcessNewShareAlerts($submission_id, $share_user_id, $list_id, $personal_message)
		{
			require_once 'ovouser.php';
			$ovoUser = new ovoUser($share_user_id);
			
			if ($ovoUser) {
				if ($list_id == "all") {
					$followers = $ovoUser->GetFollowers();
				} else {
					$followers = $ovoUser->GetFollowersInList($list_id);
				}
				
				
				if ($followers) {
					foreach($followers as $follower) {
						$user_id = $follower['id'];
						
						$alert_settings = $this->GetUserAlertSettings($user_id);
						if ($alert_settings['shares'] == "SITE" || $alert_settings['shares'] == "BOTH") {
							// on site alert
							$this->AddShareAlert($user_id, $share_user_id, $submission_id, $personal_message);
						}

						if ($alert_settings['shares'] == "EMAIL" || $alert_settings['shares'] == "BOTH") {
							// send email
							
							require_once 'ovosubmission.php';
							
							require_once 'ovsubmission.php';
							$ovSubmission = new ovSubmission();

							$user_detail = new ovoUser($user_id);
							$submission_detail = new ovoSubmission($ovSubmission->GetSubmissionDetails($submission_id));

							$domain = $this->ovUtilities->GetDomain($this->ovSettings->RootURL());
							$this->ovEmailer->SetFromEmail("alerts@" . $domain);
							$this->ovEmailer->SetSource($this->ovSettings->Title() . " Alerts");

							$subject = $ovoUser->Username() . " has shared a submission with you.";

							$message = "<html><body>";
							$message .= "<p>Hi " . $user_detail->Username() . "</p>";
							$message .= "<p><a href=\"" . $this->ovSettings->RootURL() . "/users/" . strtolower($ovoUser->Username) . "\">" . $ovoUser->Username . "</a>";
							$message .= " has shared a submission with you</p>";
							$message .= "<p><a href=\"" . $this->ovSettings->RootURL() . $submission_detail->PageURL() . "\">" . $submission_detail->Title() . "</a></p>";
							$message .= "<p>$personal_message</p>";
							$message .= "<p>Regards,<br/>" . $this->ovSettings->Title() . "</p>";
							$message .= "</body></html>";

							$this->ovEmailer->SendEmail($user_detail->Email(), $message, $subject);
						}
					}
				}
			}
		}
		
		/**
		 * Processes new alerts for a new follower and sends the email or on-site alert out
		 * @access public
		 * @param int $user_id The PK of the user who was just followed
		 * @param int $follower_user_id THE PK of the user who is following
		 * @since 3.0
		 */
		public function ProcessNewFollowerAlerts($user_id, $follower_user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$follower_user_id = mysql_escape_string($follower_user_id);
			
			$alert_settings = $this->GetUserAlertSettings($user_id);
			
			if ($alert_settings['followers'] == "SITE" || $alert_settings['followers'] == "BOTH") {
				// on site alert
				$this->AddFollowerAlert($user_id, $follower_user_id);
			}
			
			if ($alert_settings['followers'] == "EMAIL" || $alert_settings['followers'] == "BOTH") {
				// send email
				require_once 'ovouser.php';
				$follower_detail = new ovoUser($follower_user_id);
				$user_detail = new ovoUser($user_id);
				
				if ($follower_detail && $user_detail) {
					$domain = $this->ovUtilities->GetDomain($this->ovSettings->RootURL());
					$this->ovEmailer->SetFromEmail("alerts@" . $domain);
					$this->ovEmailer->SetSource($this->ovSettings->Title() . " Alerts");
			
					$subject = $follower_detail->Username() . " is now following you on " . $this->ovSettings->Title();
					
					$message = "<html><body>";
					$message .= "<p>Hi " . $user_detail->Username() . "</p>";
					$message .= "<p><a href=\"" . $this->ovSettings->RootURL() . "/users/" . strtolower($follower_detail->Username) . "\">" . $follower_detail->Username . "</a> is now following you on ";
					$message .= " <a href=\"" . $this->ovSettings->RootURL() . "\">" . $this->ovSettings->Title() . "</a></p>";
					$message .= "<p>Regards,<br/>" . $this->ovSettings->Title() . "</p>";
					$message .= "</body></html>";
			
					$this->ovEmailer->SendEmail($user_detail->Email(), $message, $subject);
				}
			}
		}
		
		/**
		 * Processes new alerts for new favorites and sends the email or on-site alert out
		 * @access public
		 * @param int $user_id The PK of the user whose submission was just marked as a favorite
		 * @param int $submission_id The PK of the submission that was marked as a favorite
		 * @param int $favorite_user_id THE PK of the user who marked the submission a favorite
		 * @since 3.0
		 */
		public function ProcessNewFavoriteAlerts($user_id, $submission_id, $favorite_user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$submission_id = mysql_escape_string($submission_id);
			$favorite_user_id = mysql_escape_string($favorite_user_id);
			
			$alert_settings = $this->GetUserAlertSettings($user_id);
			
			if ($alert_settings['favorites'] == "SITE" || $alert_settings['favorites'] == "BOTH") {
				// on site alert
				$this->AddFavoriteAlert($user_id, $favorite_user_id, $submission_id);
			}
			
			if ($alert_settings['favorites'] == "EMAIL" || $alert_settings['favorites'] == "BOTH") {
				// send email
				require 'ovouser.php';
				require 'ovsubmission.php';
				require 'ovosubmission.php';
				
				$favorite_user_detail = new ovoUser($favorite_user_id);
				$user_detail = new ovoUser($user_id);
				$submission_detail = new ovoSubmission($ovSubmission->GetSubmissionDetails($submission_id));
				
				if ($favorite_user_detail && $user_detail && $submission_detail) {
					$domain = $this->ovUtilities->GetDomain($this->ovSettings->RootURL());
					$this->ovEmailer->SetFromEmail("alerts@" . $domain);
					$this->ovEmailer->SetSource($this->ovSettings->Title() . " Alerts");
			
					$subject = $favorite_user_detail->Username() . " has marked one of your submissions as a favorite.";
					
					$message = "<html><body>";
					$message .= "<p>Hi " . $user_detail->Username() . "</p>";
					$message .= "<p><a href=\"" . $this->ovSettings->RootURL() . "/users/" . strtolower($favorite_user_detail->Username) . "\">" . $favorite_user_detail->Username . "</a>";
					$message .= " has marked your submission";
					$message .= " <a href=\"" . $this->ovSettings->RootURL() . $submission_detail->PageURL() . "\">" . $submission_detail->Title() . "</a>";
					$message .= " a favorite</p>";
					$message .= "<p>Regards,<br/>" . $this->ovSettings->Title() . "</p>";
					$message .= "</body></html>";
			
					$this->ovEmailer->SendEmail($user_detail->Email(), $message, $subject);
				}
			}
		}
		
		/**
		 * Gets the subscribers of a comment thread
		 * @access protected
		 * @param int $submission_id The PK of the submission
		 * @return array|false Array of USER IDs or FALSE if no users or ERROR
		 * @since 3.0
		 */
		protected function GetSubscribers($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			$query = "CALL GetSubscribers(" . ovDBConnector::SiteID() . ", $submission_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return false;
			}
			
			if ($result->num_rows > 0) {
				$subscribers = array();
				while($row = $result->fetch_assoc()) {
					array_push($subscribers, $row['user_id']);
				}
				ovDBConnector::FreeResult();
				return $subscribers;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the alert settings for a given user
		 * @access public
		 * @param int $user_id The PK of the user
		 * @return array Array of the alert settings, if error or no user, all alerts are set to NONE
		 * @since 3.0
		 */
		public function GetUserAlertSettings($user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$query = "CALL GetUserAlertSettings(" . ovDBConnector::SiteID() . ", $user_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return array('comments'=>'NONE', 'shares'=>'NONE', 'messages'=>'NONE', 'followers'=>'NONE', 'favorites'=>'NONE');
			}
			
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult();
				return array('comments'=>$row['alert_comments'], 'shares'=>$row['alert_shares'], 'messages'=>$row['alert_messages'], 'followers'=>$row['alert_followers'], 'favorites'=>$row['alert_favorites']);
			} else {
				ovDBConnector::FreeResult();
				return array('comments'=>'NONE', 'shares'=>'NONE', 'messages'=>'NONE', 'followers'=>'NONE', 'favorites'=>'NONE');
			}
		}

		/**
		 * Adds an On-Site alert for a new comment
		 * @access protected
		 * @param int $user_id The PK of the user being alerted
		 * @param int $comment_user_id The PK of the user who posted the comment
		 * @param int $submission_id The PK of the submission the comment was posted to
		 * @since 3.0
		 */
		protected function AddCommentAlert($user_id, $comment_user_id, $submission_id, $comment_id)
		{
			$user_id = mysql_escape_string($user_id);
			$comment_user_id = mysql_escape_string($comment_user_id);
			$submission_id = mysql_escape_string($submission_id);
			$comment_id = mysql_escape_string($comment_id);
			
			$query = "CALL AddCommentAlert(" . ovDBConnector::SiteID() . ", $user_id, $comment_user_id, $submission_id, $comment_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Adds an On-Site alert for a new share
		 * @access protected
		 * @param int $user_id The PK of the user being alerted
		 * @param int $share_user_id The PK of the user who shared the submission
		 * @param int $submission_id The PK of the submission being shared
		 * @param string $message Personal Message to go along with share
		 * @since 3.0
		 */
		protected function AddShareAlert($user_id, $share_user_id, $submission_id, $message)
		{
			$user_id = mysql_escape_string($user_id);
			$share_user_id = mysql_escape_string($share_user_id);
			$submission_id = mysql_escape_string($submission_id);
			$message = mysql_escape_string($message);

			$query = "CALL AddShareAlert(" . ovDBConnector::SiteID() . ", $user_id, $share_user_id, $submission_id, '$message')";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Adds an On-Site alert for a new follower
		 * @access protected
		 * @param int $user_id The PK of the user being alerted
		 * @param int $comment_user_id The PK of the user who started following
		 * @since 3.0
		 */
		protected function AddFollowerAlert($user_id, $follower_user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$follower_user_id = mysql_escape_string($follower_user_id);

			$query = "CALL AddFollowerAlert(" . ovDBConnector::SiteID() . ", $user_id, $follower_user_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Adds an On-Site alert for a new favorite
		 * @access protected
		 * @param int $user_id The PK of the user being alerted
		 * @param int $comment_user_id The PK of the user who marked the submission a favorite
		 * @param int $submission_id The PK of the submission that was marked as a favorite
		 * @since 3.0
		 */
		protected function AddFavoriteAlert($user_id, $favorite_user_id, $submission_id)
		{
			$user_id = mysql_escape_string($user_id);
			$favorite_user_id = mysql_escape_string($favorite_user_id);
			$submission_id = mysql_escape_string($submission_id);

			if ($user_id != $favorite_user_id) {
				$query = "CALL AddFavoriteAlert(" . ovDBConnector::SiteID() . ", $user_id, $favorite_user_id, $submission_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Emailer Class Object
		 * @access protected
		 * @var ovEmailer
		 * @since 3.0
		 */
		protected $ovEmailer;
		
		/**
		 * Settings Class Object
		 * @access protected
		 * @var ovSettings
		 * @since 3.0
		 */
		protected $ovSettings;
		
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