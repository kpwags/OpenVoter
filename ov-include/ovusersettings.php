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
	 * OpenVoter User Settings Class
	 * Class handling user settings
	 *
	 * @package OpenVoter
	 * @subpackage UserSettings
	 * @since 3.0
	 */
	class ovUserSettings
	{
		function __construct($user_id = false)
		{
			require_once 'ovdbconnector.php' ;
			
			$this->GetUserSettings($user_id);
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * @return string The user's start page (not in use)
		 * @access public
		 * @since 3.0
		 */
		public function StartPage() { return $this->_start_page; }
		
		/**
		 * @return string The user's start page title (not in use)
		 * @access public
		 * @since 3.0
		 */
		public function StartPageTitle() { return $this->_start_page_title; }
		
		/**
		 * @return string User's new comment alert preference (NONE, SITE, EMAIL, BOTH)
		 * @access public
		 * @since 3.0
		 */
		public function AlertComments() { return $this->_alert_comments; }
		
		/**
		 * @return string User's new shares alert preference (NONE, SITE, EMAIL, BOTH)
		 * @access public
		 * @since 3.0
		 */
		public function AlertShares() { return $this->_alert_shares; }
		
		/**
		 * @return string User's new message alert preference (NONE, SITE, EMAIL, BOTH) (not in use)
		 * @access public
		 * @since 3.0
		 */
		public function AlertMessages() { return $this->_alert_messaes; }
		
		/**
		 * @return string User's new follower alert preference (NONE, SITE, EMAIL, BOTH)
		 * @access public
		 * @since 3.0
		 */
		public function AlertFollowers() { return $this->_alert_followers; }
		
		/**
		 * @return string User's new favorite alert preference (NONE, SITE, EMAIL, BOTH)
		 * @access public
		 * @since 3.0
		 */
		public function AlertFavorites() { return $this->_alert_favorites; }
		
		/**
		 * @return string Preference as to where links should be opened (_blank = new tab / _self = same window)
		 * @access public
		 * @since 3.0
		 */
		public function OpenLinksIn() { return $this->_open_links_in; }
		
		/**
		 * @return bool Preference to subscribe to submission on posting it
		 * @access public
		 * @since 3.0
		 */
		public function SubscribeOnSubmit() { return $this->_subscribe_submit; }
		
		/**
		 * @return bool Preference to subscribe to submission on posting comment on it
		 * @access public
		 * @since 3.0
		 */
		public function SubscribeOnComment() { return $this->_subscribe_comment; }
		
		/**
		 * @return bool Preference on whether to prepopulate reply field
		 * @access public
		 * @since 3.2.2
		 */
		public function PrepopulateReply() { return $this->_prepopulate_reply; }
		
		/**
		 * @return int Comment score threshold to show/hide comment
		 * @access public
		 * @since 3.1
		 */
		public function CommentThreshold() { return $this->_comment_threshold; }

		/**
		 * @return bool Preference on whether to publicly display likes/dislikes
		 * @access public
		 * @since 3.3
		 */
		public function PubliclyDisplayLikes() { return $this->_publicly_display_likes; }

		/**
		 * Gets the logged in user's settings
		 * @access protected
		 * @since 3.0
		 */
		protected function GetUserSettings($user_id = false)
		{
			require_once 'ovusersecurity.php';
			$ovUserSecurity = new ovUserSecurity();
			
			if ($user_id == false && $ovUserSecurity->IsUserLoggedIn()) {
				$user_id = $ovUserSecurity->LoggedInUserID();
			}

			if ($user_id != false) {
				$query = "CALL GetUserSettings(" . ovDBConnector::SiteID() . ", $user_id)";
				$result = ovDBConnector::Query($query);
			
				if(!$result)
				{
					// ERROR
					$this->GetDefaultUserSettings();
				}
			
				if ($result->num_rows > 0)
				{
					$row = $result->fetch_assoc();
					
					$this->_start_page = $row['start_page'];
					$this->_start_page_title = $row['start_page_title'];
					$this->_alert_comments = $row['alert_comments'];
					$this->_alert_shares = $row['alert_shares'];
					$this->_alert_messaes = $row['alert_messages'];
					$this->_alert_followers = $row['alert_followers'];
					$this->_alert_favorites = $row['alert_favorites'];
					$this->_open_links_in = $row['open_links_in'];
					
					if ($row['subscribe_on_submit'] == 1) {
						$this->_subscribe_submit = true;
					} else {
						$this->_subscribe_submit = false;
					}
					
					if ($row['subscribe_on_comment'] == 1) {
						$this->_subscribe_comment = true;
					} else {
						$this->_subscribe_comment = false;
					}
					
					if ($row['prepopulate_reply'] == 1) {
						$this->_prepopulate_reply = true;
					} else {
						$this->_prepopulate_reply = false;
					}

					if ($row['publicly_display_likes'] == 1) {
						$this->_publicly_display_likes = true;
					} else {
						$this->_publicly_display_likes = false;
					}
					
					$this->_comment_threshold = $row['comment_threshold'];

					ovDBConnector::FreeResult();
				}
				else
				{
					$this->GetDefaultUserSettings();
					ovDBConnector::FreeResult();
				}
			} else {
				$this->GetDefaultUserSettings();
			}
		}
		
		/**
		 * Gets the default settings if user not logged in or if error
		 * @access protected
		 * @since 3.0
		 */
		protected function GetDefaultUserSettings()
		{
			$this->_start_page = "/";
			$this->_start_page_title = "Home";
			$this->_alert_comments = "SITE";
			$this->_alert_shares = "SITE";
			$this->_alert_messaes = "SITE";
			$this->_alert_followers = "SITE";
			$this->_alert_favorites = "SITE";
			$this->_open_links_in = "_blank";
			$this->_subscribe_submit = true;
			$this->_subscribe_comment = true;
			$this->_comment_threshold = -2;
			$this->_prepopulate_reply = false;
			$this->_publicly_display_likes = true;
		}
		
		/**
		 * Start page (not in use)
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_start_page;
		
		/**
		 * Start page title (not in use)
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_start_page_title;
		
		/**
		 * Preference for new comment alerts
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_alert_comments;
		
		/**
		 * Preference for new share alerts
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_alert_shares;
		
		/**
		 * Preference for new message alerts (not in use)
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_alert_messages;
		
		/**
		 * Preference for new follower alerts
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_alert_followers;
		
		/**
		 * Preference for new favorite alerts
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_alert_favorites;
		
		/**
		 * Preference for opening links
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_open_links_in;
		
		/**
		 * Preference on subscribing to submissions on submitting
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_subscribe_submit;
		
		/**
		 * Preference on subscribing to submissions on posting a comment to it
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_subscribe_comment;
		
		/**
		 * Preference on whether to prepopulate the reply box
		 * @access protected
		 * @var bool
		 * @since 3.2.2
		 */
		protected $_prepopulate_reply;
		
		/**
		 * Preference for when a comment becomes hidden
		 * @access protected
		 * @var int
		 * @since 3.1
		 */
		protected $_comment_threshold;

		/**
		 * Preference on whether to publicly display likes/dislikes
		 * @access protected
		 * @var bool
		 * @since 3.3
		 */
		protected $_publicly_display_likes;
	}
?>