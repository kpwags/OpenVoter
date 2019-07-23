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
	class ovAdminSettings
	{
		function __construct()
		{
			//require_once 'ovdbconnector.php';
			require_once 'ovadminsecurity.php';
			$this->ovAdminSecurity = new ovAdminSecurity();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets The Site's Base Settings
		 * @access public
		 * @return array Settings Array
		 * @since 3.0
		 */
		public function GetBaseSettings()
		{
			$query = "CALL GetBaseSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();
				
				$settings_array['root_url'] = $row['root_url'];
				$settings_array['title'] = stripslashes($row['title']);
				$settings_array['blog'] = $row['blog'];
				
				if ($row['enable_api'] == 1) {
					$settings_array['enable_api'] = true;
				} else {
					$settings_array['enable_api'] = false;
				}
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
	
		/**
		 * Saves The Site's Base Settings
		 * @access public
		 * @param string $root_url The site's root URL
		 * @param string $title The site's title
		 * @param string $blog URL to the blog
		 * @param bool $enable_api Flag to enable/disable API
		 * @since 3.0
		 */
		public function SaveBaseSettings($root_url, $title, $blog, $enable_api)
		{
			$root_url = mysql_escape_string($root_url);
			$title = mysql_escape_string($title);
			$blog = mysql_escape_string($blog);
			
			if ($enable_api) {
				$enable_api = 1;
			} else {
				$enable_api = 0;
			}
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveBaseSettings(" . ovDBConnector::SiteID() . ", '$root_url', '$title', '$blog', $enable_api)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Karma Settings
		 * @access public
		 * @return array Settings Array
		 * @since 3.0
		 */
		public function GetKarmaSettings()
		{
			$query = "CALL GetKarmaSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();
				
				if ($row['use_karma_system'] == 1) {
					$settings_array['use_karma_system'] =  true;
				} else {
					$settings_array['use_karma_system'] =  false;
				}
				
				$settings_array['karma_name'] = stripslashes($row['karma_name']);
				$settings_array['points_submission'] = $row['points_submission'];
				$settings_array['points_comment'] = $row['points_comment'];
				$settings_array['points_vote'] = $row['points_vote'];
				$settings_array['points_popular'] = $row['points_popular'];
				$settings_array['points_comment_up_vote'] = $row['points_comment_vote_up'];
				$settings_array['points_comment_down_vote'] = $row['points_comment_vote_down'];
				$settings_array['karma_penalties'] = $row['karma_penalties'];
				$settings_array['karma_penalty_1_threshold'] = $row['karma_penalty_1_threshold'];
				$settings_array['karma_penalty_1_comments'] = $row['karma_penalty_1_comments'];
				$settings_array['karma_penalty_1_submissions'] = $row['karma_penalty_1_submissions'];
				$settings_array['karma_penalty_2_threshold'] = $row['karma_penalty_2_threshold'];
				$settings_array['karma_penalty_2_comments'] = $row['karma_penalty_2_comments'];
				$settings_array['karma_penalty_2_submissions'] = $row['karma_penalty_2_submissions'];
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Saves The Site's Karma Settings
		 * @access public
		 * @param bool $use_karma_system Flag as whether to use a karma point system
		 * @param string $karma_name Name of the karma point
		 * @param double $points_popular Points gained per submission
		 * @param double $points_comment Points gained per comment
		 * @param double $points_comment_up Points gained per comment up vote
		 * @param double $points_comment_down Points gained per comment down vote
		 * @param double $points_vote Points gained per vote
		 * @param double $points_popular Points gained per submission made popular
		 * @since 3.0
		 */
		public function SaveKarmaSettings($use_karma_system, $karma_name, $points_submission, $points_comment, $points_comment_up, $points_comment_down, $points_vote, $points_popular, $karma_penalties, $karma_penalty_1_threshold, $karma_penalty_1_comments, $karma_penalty_1_submissions, $karma_penalty_2_threshold, $karma_penalty_2_comments, $karma_penalty_2_submissions)
		{
			$karma_name = mysql_escape_string($karma_name);
			$points_submission = mysql_escape_string($points_submission);
			$points_comment = mysql_escape_string($points_comment);
			$points_vote = mysql_escape_string($points_vote);
			$points_popular = mysql_escape_string($points_popular);
			$points_comment_up = mysql_escape_string($points_comment_up);
			$points_comment_down = mysql_escape_string($points_comment_down);
			$karma_penalty_1_threshold = mysql_escape_string($karma_penalty_1_threshold);
			$karma_penalty_1_comments = mysql_escape_string($karma_penalty_1_comments);
			$karma_penalty_1_submissions = mysql_escape_string($karma_penalty_1_submissions);
			$karma_penalty_2_threshold = mysql_escape_string($karma_penalty_2_threshold);
			$karma_penalty_2_comments = mysql_escape_string($karma_penalty_2_comments);
			$karma_penalty_2_submissions = mysql_escape_string($karma_penalty_2_submissions);
			
			if ($use_karma_system) {
				$use_karma_system = 1;
			} else {
				$use_karma_system = 0;
			}
			
			if ($karma_penalties) {
				$karma_penalties = 1;
			} else {
				$karma_penalties = 0;
			}
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveKarmaSettings(" . ovDBConnector::SiteID() . ", $use_karma_system, '$karma_name', $points_submission, $points_comment, $points_vote, $points_popular, $points_comment_up, $points_comment_down, $karma_penalties, $karma_penalty_1_threshold, $karma_penalty_1_submissions, $karma_penalty_1_comments, $karma_penalty_2_threshold, $karma_penalty_2_submissions, $karma_penalty_2_comments)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Algorithm Settings
		 * @access public
		 * @return array Settings Array
		 * @since 3.0
		 */
		public function GetAlgorithmSettings()
		{
			$query = "CALL GetAlgorithmSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();

				$settings_array['algorithm'] = $row['algorithm'];
				$settings_array['threshold'] = $row['threshold'];
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Saves The Site's Algorithm Settings
		 * @access public
		 * @param string $algorithm Popular algorithm (dynamic or static)
		 * @param string $threshold Popular threshold for the static algorithm
		 * @since 3.0
		 */
		public function SaveAlgorithmSettings($algorithm, $threshold)
		{
			$algorithm = mysql_escape_string($algorithm);
			$threshold = mysql_escape_string($threshold);
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveAlgorithmSettings(" . ovDBConnector::SiteID() . ", '$algorithm', '$threshold')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Submission Settings
		 * @access public
		 * @return array Settings Array
		 * @since 3.0
		 */
		public function GetSubmissionSettings()
		{
			$query = "CALL GetSubmissionSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();
				
				$settings_array['pagination'] = $row['pagination'];
				
				if ($row['show_votes'] == 1) {
					$settings_array['show_votes'] =  true;
				} else {
					$settings_array['show_votes'] =  false;
				}
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/**
		 * Saves The Site's Submission Settings
		 * @access public
		 * @param int $pagination Number of submissions to show per page
		 * @param bool $show_votes Flag to determine whether to show votes
		 * @since 3.0
		 */
		public function SaveSubmissionSettings($pagination, $show_votes)
		{
			$pagination = mysql_escape_string($pagination);
			
			if ($show_votes) {
				$show_votes = 1;
			} else {
				$show_votes = 0;
			}
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveSubmissionSettings(" . ovDBConnector::SiteID() . ", '$pagination', $show_votes)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Comment Settings
		 * @access public
		 * @return array Settings Array
		 * @since 3.0
		 */
		public function GetCommentSettings()
		{
			$query = "CALL GetCommentSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();
				
				$settings_array['comment_modify_time'] = $row['comment_modify_time'];
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Saves The Site's Comment Settings
		 * @access public
		 * @param string $threshold Threshold to Hide Comments
		 * @param string $modify_time Time to Allow Comments to be Modified
		 * @since 3.0
		 */
		public function SaveCommentSettings($modify_time)
		{
			$modify_time = mysql_escape_string($modify_time);
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveCommentSettings(" . ovDBConnector::SiteID() . ", '$modify_time')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Captcha Settings
		 * @access public
		 * @return array Settings Array
		 * @since 3.0
		 */
		public function GetCaptchaSettings()
		{
			$query = "CALL GetCaptchaSettings(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();

				if ($row['enable_recaptcha'] == 1) {
					$settings_array['enable_recaptcha'] =  true;
				} else {
					$settings_array['enable_recaptcha'] =  false;
				}

				$settings_array['recaptcha_private_key'] = stripslashes($row['recaptcha_private_key']);
				$settings_array['recaptcha_public_key'] = stripslashes($row['recaptcha_public_key']);
				$settings_array['recaptcha_theme'] = stripslashes($row['recaptcha_theme']);
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Saves The Site's Captcha Settings
		 * @access public
		 * @param bool $enable_recaptcha Flag to enable/disable recaptcha
		 * @param string $recaptcha_private_key Private key for recaptcha
		 * @param string $recaptcha_public_key Public key for recaptcha
		 * @param string $recaptcha_theme Theme recaptcha should use
		 * @since 3.0
		 */
		public function SaveCaptchaSettings($enable_recaptcha, $recaptcha_private_key, $recaptcha_public_key, $recaptcha_theme)
		{
			if ($enable_recaptcha) {
				$enable_recaptcha = 1;
			} else {
				$enable_recaptcha = 0;
			}
			
			$recaptcha_private_key = mysql_escape_string($recaptcha_private_key);
			$recaptcha_public_key = mysql_escape_string($recaptcha_public_key);
			$recaptcha_theme = mysql_escape_string($recaptcha_theme);
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveCaptchaSettings(" . ovDBConnector::SiteID() . ", $enable_recaptcha, '$recaptcha_private_key', '$recaptcha_public_key', '$recaptcha_theme')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Policies
		 * @access public
		 * @return array Policy Array
		 * @since 3.0
		 */
		public function GetPolicies()
		{
			$query = "CALL GetPolicies(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{			
				$settings_array = array();
				$row = $result->fetch_assoc();
				
				$settings_array['about_site'] = stripslashes($row['about_site']);
				$settings_array['privacy_policy'] = stripslashes($row['privacy_policy']);
				$settings_array['terms_of_use'] = stripslashes($row['terms_of_use']);
				$settings_array['site_help'] = stripslashes($row['site_help']);
								
				ovDBConnector::FreeResult();
				
				return $settings_array;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Saves The Site's Policies
		 * @access public
		 * @param string $about_site The about site HTML
		 * @param string $privacy_policy The privacy policy HTML
		 * @param string $terms_of_use The terms of use HTML
		 * @param string $site_help The site help HTML
		 * @since 3.0
		 */
		public function SavePolicies($about_site, $privacy_policy, $terms_of_use, $site_help)
		{
			$about_site = mysql_escape_string($about_site);
			$privacy_policy = mysql_escape_string($privacy_policy);
			$terms_of_use = mysql_escape_string($terms_of_use);
			$site_help = mysql_escape_string($site_help);
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SavePolciies(" . ovDBConnector::SiteID() . ", '$about_site', '$privacy_policy', '$terms_of_use', '$site_help')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets The Site's Ad Code
		 * @access public
		 * @return array Ad Array
		 * @since 3.0
		 */
		public function GetAds()
		{
			$query = "CALL GetAds(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$ad['top_ad'] = stripslashes($row['top_ad']);
				$ad['side_ad'] = stripslashes($row['side_ad']);
				
				ovDBConnector::FreeResult();
				
				$ad['google_analytics_code'] = $this->GetGoogleAnalyticsCode();
				
				return $ad;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		public function GetGoogleAnalyticsCode()
		{
			$query = "CALL GetAnalyticsCode(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);
			if (!$result) {
				// ERROR
				$google_analytics_code = "";
			} else { 
				if ($result->num_rows <= 0) {
					$google_analytics_code = "";
				} else {
					$row = $result->fetch_assoc();
					$google_analytics_code = stripslashes($row['google_analytics_code']);
				}
			}
			
			ovDBConnector::FreeResult();
			return $google_analytics_code;
		}
		
		/**
		 * Saves The Site's Ad Code
		 * @access public
		 * @param string $top_ad The ad code for top banner
		 * @param string $side_ad The ad code for the sidebar ad
		 * @since 3.0
		 */
		public function SaveAds($top_ad, $side_ad, $google_analytics)
		{
			$top_ad = mysql_escape_string($top_ad);
			$side_ad = mysql_escape_string($side_ad);
			$google_analytics = mysql_escape_string($google_analytics);
			
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL SaveAds(" . ovDBConnector::SiteID() . ", '$top_ad', '$side_ad', '$google_analytics')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		public function GetCurrentThemeInfo()
		{
			$query = "CALL GetCurrentThemeXML(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$xml_file = $row['theme'];

				ovDBConnector::FreeResult();

				return $this->GetBaseThemeInfo($xml_file);
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		protected function GetCurrentThemeXMLName()
		{
			$query = "CALL GetCurrentThemeXML(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return "";
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				$xml_file = $row['theme'];

				ovDBConnector::FreeResult();
				
				return $xml_file;
			}
			else
			{
				ovDBConnector::FreeResult();
				return "";
			}
		}
		
		protected function GetBaseThemeInfo($xml)
		{
			$xml_file = "./../ov-content/themes/" . $xml;
			$name = "Unnamed Theme";
			$screen = "/img/unknown_theme.jpg";
			$root_theme_dir = "base";
			$author = "";
			$website = "";
			
			try {
				$objDOM = new DOMDocument(); 
				$objDOM->load($xml_file);
			
				$name_node = $objDOM->getElementsByTagName("name"); 
				$screen_node = $objDOM->getElementsByTagName("screenshot");
				$author_node = $objDOM->getElementsByTagName("author");
				$website_node = $objDOM->getElementsByTagName("website");
				$root_theme_dir = $objDOM->getElementsByTagName("rootFolder");
				
				if ($name_node->length > 0) {
					$name = $name_node->item(0)->nodeValue; 
				}
				
				if ($screen_node->length > 0) {
					$screen = "/ov-content/themes/" . $screen_node->item(0)->nodeValue; 
				}
				
				if ($author_node->length > 0) {
					$author = $author_node->item(0)->nodeValue; 
				}
				
				if ($website_node->length > 0) {
					$website = $website_node->item(0)->nodeValue; 
				}				
			} catch (Exception $e) {
				return false;
			}
			
			return array('name'=>$name, 'author'=>$author, 'website'=>$website, 'screen'=>$screen, 'xml'=>$xml, 'root'=>$root_theme_dir); 
		}
		
		public function GetAvailableThemes()
		{
			$current_xml_file = $this->GetCurrentThemeXMLName();
			$themes_dir = "./../ov-content/themes";
			
			$theme_files = array ();

			$h_dir = opendir($themes_dir);

			while ($s_file = readdir($h_dir)) {

				$file_type = strtolower(substr(strrchr($s_file, '.'), 1));

				if ($file_type == "xml" && $s_file != $current_xml_file) {
					array_push($theme_files, $s_file);
				}
			}

			closedir($h_dir);
			
			if (count($theme_files > 0)) {
				$available_themes = array();
				foreach ($theme_files as $theme) {
					if ($theme) {
						$theme_info = $this->GetBaseThemeInfo($theme);
						if ($theme_info) {
							array_push($available_themes, $theme_info);
						}
					}
				}
				
				return $available_themes;
			} else {
				return false;
			}
		}
		
		public function ApplyTheme($theme_file, $root_dir)
		{
			$theme_file = mysql_escape_string($theme_file);
			$root_dir = mysql_escape_string($root_dir);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {
				$query = "CALL UpdateSiteTheme(" . ovDBConnector::SiteID() . ", '$theme_file', '$root_dir')";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		protected $ovAdminSecurity;
	}
?>