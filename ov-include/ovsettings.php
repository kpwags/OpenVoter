<?php
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
	 * OpenVoter Settings Class
	 * Class handling Site Settings
	 *
	 * @package OpenVoter
	 * @subpackage Settings
	 * @since 3.0
	 */
	class ovSettings
	{
		/**
		 * Constructor
		 * @access public
		 */
		function __construct()
		{			
			require_once 'ovdbconnector.php';
			
			/* Get Site Settings */
			$query = "CALL GetSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if(!$result)
			{
				// ERROR
				die('NO SITE CONFIG');
			}
			
			if ($result->num_rows > 0)
			{
				// bring in the settings row
				$row = $result->fetch_assoc();
				
				// assign the settings
				$this->_root_url = stripslashes($row['root_url']);
				$this->_mobile_root_url = stripslashes($row['mobile_root_url']);
				$this->_admin_root_url = $this->_root_url . "/ov-admin";
				$this->_theme_dir = stripslashes($row['theme_dir']);
				$this->_title = stripslashes($row['title']);

				if ($row['email_new_report'] == 1) {
					$this->_email_new_report = true;
				} else {
					$this->_email_new_report = false;
				}
				
				$this->_auto_report_keywords = stripslashes($row['auto_report_keywords']);
				$this->_blog = stripslashes($row['blog']);
				
				if ($row['use_karma_system'] == 1) {
					$this->_use_karma_system = true;
				} else {
					$this->_use_karma_system = false;
				}
				
				$this->_karma_name = stripslashes($row['karma_name']);
				$this->_points_submission = $row['points_submission'];
				$this->_points_comment = $row['points_comment'];
				$this->_points_vote = $row['points_vote'];
				$this->_points_popular = $row['points_popular'];
				$this->_default_avatar = $row['default_avatar'];
				$this->_default_photo_thumbnail = $row['default_photo_thumbnail'];
				$this->_default_video_thumbnail = $row['default_video_thumbnail'];
				$this->_algorithm = $row['algorithm'];
				$this->_threshold = $row['threshold'];
				$this->_comment_modify_time = $row['comment_modify_time'];
				$this->_pagination = $row['pagination'];

				if ($row['show_votes'] == 1) {
					$this->_show_votes = true;
				} else {
					$this->_show_votes = false;
				}
				
				if ($row['enable_recaptcha'] == 1) {
					$this->_enable_recaptcha = true;
				} else {
					$this->_enable_recaptcha = false;
				}

				$this->_recaptcha_private_key = stripslashes($row['recaptcha_private_key']);
				$this->_recaptcha_public_key = stripslashes($row['recaptcha_public_key']);
				$this->_recaptcha_theme = stripslashes($row['recaptcha_theme']);
				$this->_about_site = $row['about_site'];
				$this->_privacy_policy = $row['privacy_policy'];
				$this->_terms_of_use = $row['terms_of_use'];
				$this->_site_help = $row['site_help'];
				$this->_google_analytics = $row['google_analytics_code'];
				
				if ($row['enable_api'] == 1) {
					$this->_enable_api = true;
				} else {
					$this->_enable_api = false;
				}
				
				$this->_version = stripslashes($row['version']);
				ovDBConnector::FreeResult();
			}
			else
			{
				ovDBConnector::FreeResult();
				die('NO SITE CONFIG');
			}
			
			$this->GetReportEmails();
			$this->GetFeedbackEmails();
			$this->GetAds();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * @return string The Root URL of the site
		 * @access public
		 * @since 3.0
		 */
		public function RootURL() { return $this->_root_url; }
		
		/**
		 * @return string The mobile Root URL of the site (not in use)
		 * @access public
		 * @since 3.0
		 */
		public function MobileRootURL() { return $this->_mobile_root_url; }
		
		/**
		 * @return string The admin root URL of the site
		 * @access public
		 * @since 3.0
		 */
		public function AdminRootURL() { return $this->_admin_root_url; }
		
		/**
		 * @return string The directory for the theme
		 * @access public
		 * @since 3.2
		 */
		public function ThemeDirectory() { return $this->_theme_dir; }
		
		/**
		 * @return string The title of the site
		 * @access public
		 * @since 3.0
		 */
		public function Title() { return $this->_title; }
		
		/**
		 * @return bool Flag to email on new reports (deprecated)
		 * @access public
		 * @since 3.0
		 */
		public function EmailOnNewReport() { return $this->_email_new_report; }
		
		/**
		 * @return string keywords to auto report a submission (not in use)
		 * @access public
		 * @since 3.0
		 */
		public function AutoReportKeywords() { return $this->_auto_report_keywords; }
		
		/**
		 * @return string URL of site's blog
		 * @access public
		 * @since 3.0
		 */
		public function Blog() { return $this->_blog; }
		
		/**
		 * @return bool Flag to use karma system
		 * @access public
		 * @since 3.0
		 */
		public function UseKarmaSystem() { return $this->_use_karma_system; }
		
		/**
		 * @return string Name of karma points
		 * @access public
		 * @since 3.0
		 */
		public function KarmaName() { return $this->_karma_name; }
		
		/**
		 * @return double Karma points given for each submission
		 * @access public
		 * @since 3.0
		 */
		public function PointsPerSubmission() { return $this->_points_submission; }
		
		/**
		 * @return double Karma points given for each comment
		 * @access public
		 * @since 3.0
		 */
		public function PointsPerComment() { return $this->_points_comment; }
		
		/**
		 * @return double Karma points given for each vote
		 * @access public
		 * @since 3.0
		 */
		public function PointsPerVote() { return $this->_points_vote; }
		
		/**
		 * @return double Karma points given for each submission made popular
		 * @access public
		 * @since 3.0
		 */
		public function PointsPerPopular() { return $this->_points_popular; }
		
		/**
		 * @return string URL for the default user avatar
		 * @access public
		 * @since 3.0
		 */
		public function DefaultAvatar() { return $this->_default_avatar; }
		
		/**
		 * @return string URL for the default photo thumbnail
		 * @access public
		 * @since 3.0
		 */
		public function DefaultPhotoThumbnail() { return $this->_default_photo_thumbnail; }
		
		/**
		 * @return string URL for the default video thumbnail
		 * @access public
		 * @since 3.0
		 */
		public function DefaultVideoThumbnail() { return $this->_default_video_thumbnail; }
		
		/**
		 * @return string Popular algorithm in use (STATIC or DYNAMIC)
		 * @access public
		 * @since 3.0
		 */
		public function Algorithm() { return $this->_algorithm; }
		
		/**
		 * @return double Popular threshold for the static algorithm
		 * @access public
		 * @since 3.0
		 */
		public function Threshold() { return $this->_threshold; }
		
		/**
		 * @return int Number of minutes a comment can be modified for after posting (0 = Can not edit / -1 = Can edit forever)
		 * @access public
		 * @since 3.1
		 */
		public function CommentModifyTime() { return $this->_comment_modify_time; }
		
		/**
		 * @return int Number of submissions per page
		 * @access public
		 * @since 3.0
		 */
		public function Pagination() { return $this->_pagination; }
		
		/**
		 * @return bool Flag to show votes on submission pages
		 * @access public
		 * @since 3.0
		 */
		public function ShowVotes() { return $this->_show_votes; }
		
		/**
		 * @return bool Flag to enable ReCaptcha
		 * @access public
		 * @since 3.0
		 */
		public function EnableRecaptcha() { return $this->_enable_recaptcha; }
		
		/**
		 * @return string ReCaptcha private key
		 * @access public
		 * @since 3.0
		 */
		public function RecaptchaPrivateKey() { return $this->_recaptcha_private_key; }
		
		/**
		 * @return string ReCaptcha public key
		 * @access public
		 * @since 3.0
		 */
		public function RecaptchaPublicKey() { return $this->_recaptcha_public_key; }
		
		/**
		 * @return string ReCaptcha theme
		 * @access public
		 * @since 3.0
		 */
		public function RecaptchaTheme() { return $this->_recaptcha_theme; }
		
		/**
		 * @return string Site about HTML
		 * @access public
		 * @since 3.0
		 */
		public function About() { return $this->_about_site; }
		
		/**
		 * @return string Site privacy policy HTML
		 * @access public
		 * @since 3.0
		 */
		public function PrivacyPolicy() { return $this->_privacy_policy; }
		
		/**
		 * @return string Site terms of use HTML
		 * @access public
		 * @since 3.0
		 */
		public function TermsOfUse() { return $this->_terms_of_use; }
		
		/**
		 * @return string Site help HTML
		 * @access public
		 * @since 3.0
		 */
		public function Help() { return $this->_site_help; }
		
		/**
		 * @return ovUserSettings User settings object
		 * @access public
		 * @since 3.0
		 */
		public function UserSettings() { return $this->_user_settings; }
		
		/**
		 * @return bool Flag for if API is enabled
		 * @access public
		 * @since 3.2
		 */
		public function EnableAPI() { return $this->_enable_api; }
		
		/**
		 * @return string Site version
		 * @access public
		 * @since 3.0
		 */
		public function Version() { return $this->_version; }
		
		/**
		 * @return array Array of admin emails subscribing to emails on new feedback
		 * @access public
		 * @since 3.0
		 */
		public function FeedbackEmails() { return $this->_feedback_emails; }
		
		/**
		 * @return array Array of admin emails subscribing to emails on new reports
		 * @access public
		 * @since 3.0
		 */
		public function ReportEmails() { return $this->_report_emails; }
		
		/**
		 * @return string Top Ad code
		 * @access public
		 * @since 3.0
		 */
		public function TopAd() { return $this->_top_ad; }
		
		/**
		 * @return string Side ad code
		 * @access public
		 * @since 3.0
		 */
		public function SidebarAd() { return $this->_side_ad; }
		
		/**
		 * @return string Google Analytics code
		 * @access public
		 * @since 3.0
		 */
		public function GoogleAnalytics() { return $this->_google_analytics; }
		
		/**
		 * Sets the email addresses of admins who subscribe to notifications of new reports
		 * @access public
		 * @since 3.0
		 */
		protected function GetReportEmails()
		{
			$query = "CALL GetReportEmails(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				$this->_report_emails = false;
			}

			if ($result->num_rows > 0)
			{
				$this->_report_emails = array();
				while($row = $result->fetch_assoc())
				{
					array_push($this->_report_emails, stripslashes($row['email']));
				}
			}
			else
			{
				$this->_report_emails = false;
			}
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Sets the email addresses of admins who subscribe to notifications of new feedback
		 * @access public
		 * @since 3.0
		 */
		protected function GetFeedbackEmails()
		{
			$query = "CALL GetFeedbackEmails(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				$this->_feedback_emails = false;
			}

			if ($result->num_rows > 0)
			{
				$this->_feedback_emails = array();
				while($row = $result->fetch_assoc())
				{
					array_push($this->_feedback_emails, stripslashes($row['email']));
				}
			}
			else
			{
				$this->_report_emails = false;
			}
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Sets the ads for the site
		 * @access public
		 * @since 3.0
		 */
		protected function GetAds()
		{
			$this->_top_ad = "";
			$this->_side_ad = "";
			
			$query = "CALL GetAds(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if($result)
			{
				if ($result->num_rows > 0)
				{
					$row = $result->fetch_assoc();
					$this->_top_ad = stripslashes($row['top_ad']);
					$this->_side_ad = stripslashes($row['side_ad']);
				}
				
				ovDBConnector::FreeResult();
			}
		}

		/**
		 * Root URL of site
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_root_url;
		
		/**
		 * Mobile Root URL of site
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_mobile_root_url;
		
		/**
		 * Admin Root URL of site
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_admin_root_url;
		
		/**
		 * Theme Directory
		 * @access protected
		 * @var string
		 * @since 3.2
		 */
		protected $_theme_dir;
		
		/**
		 * Title of site
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_title;
		
		/**
		 * Flag to email on new reports (not in use)
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_email_new_report;
		
		/**
		 * Auto report keywords
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_auto_report_keywords;
		
		/**
		 * URL of site's blog
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_blog;
		
		/**
		 * Flag to use karma system
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_use_karma_system;
		
		/**
		 * Karma point name
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_karma_name;
		
		/**
		 * Karma points per submission
		 * @access protected
		 * @var double
		 * @since 3.0
		 */
		protected $_points_submission;
		
		/**
		 * Karma points per comment
		 * @access protected
		 * @var double
		 * @since 3.0
		 */
		protected $_points_comment;
		
		/**
		 * Karma points per vote
		 * @access protected
		 * @var double
		 * @since 3.0
		 */
		protected $_points_vote;
		
		/**
		 * Karma points per submission made popular
		 * @access protected
		 * @var double
		 * @since 3.0
		 */
		protected $_points_popular;
		
		/**
		 * URL of default user avatar
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_default_avatar;
		
		/**
		 * URL of default photo thumbnail
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_default_photo_thumbnail;
		
		/**
		 * URL of default video thumbnail
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_default_video_thumbnail;
		
		/**
		 * Popular Algorithm (STATIC or DYNAMIC)
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_algorithm;
		
		/**
		 * Score threshold for static popular algorithm
		 * @access protected
		 * @var double
		 * @since 3.0
		 */
		protected $_threshold;
		
		/**
		 * Minutes comments can be modified (0 = No Editing / -1 = Forever)
		 * @access protected
		 * @var string
		 * @since 3.1
		 */
		protected $_comment_modify_time;
		
		/**
		 * Submissions per page
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_pagination;
		
		/**
		 * Flag to show votes
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_show_votes;
		
		/**
		 * Flag to enable ReCaptcha
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_enable_recaptcha;
		
		/**
		 * ReCaptcha private key
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_recaptcha_private_key;
		
		/**
		 * ReCaptcha public key
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_recaptcha_public_key;
		
		/**
		 * ReCaptcha theme
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_recaptcha_theme;
		
		/**
		 * Site about HTML
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_about_site;
		
		/**
		 * Site privacy policy HTML
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_privacy_policy;
		
		/**
		 * Site terms of use HTML
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_terms_of_use;
		
		/**
		 * Site help HTML
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_site_help;
		
		/**
		 * User Settings Object
		 * @access protected
		 * @var ovUserSettings
		 * @since 3.0
		 */
		protected $_user_settings;
		
		/**
		 * Site version of OpenVoter
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_version;
		
		/**
		 * Admin email addresses subscribed to new feedback
		 * @access protected
		 * @var array
		 * @since 3.0
		 */
		protected $_feedback_emails;
		
		/**
		 * Admin email addresses subscribed to new reports
		 * @access protected
		 * @var array
		 * @since 3.0
		 */
		protected $_report_emails;
		
		/**
		 * Top ad code
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_top_ad;
		
		/**
		 * Side ad code
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_side_ad;
		
		/**
		 * Google Analytics code
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_google_analytics;
		
		/**
		 * API Enabled Flag
		 * @access protected
		 * @var bool
		 * @since 3.2
		 */
		protected $_enable_api;
		
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