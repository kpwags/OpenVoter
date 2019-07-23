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
	 * OpenVoter RSS Class
	 * Class handling RSS Feed calls
	 *
	 * @package OpenVoter
	 * @subpackage RSS
	 * @since 3.0
	 */
	class ovRSS
	{
		function __construct()
		{
			require_once 'ovdbconnector.php';
			
			require_once 'ovusersecurity.php';
			$this->ovUserSecurity = new ovUserSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			require_once 'ovcontent.php';
			$this->ovContent = new ovContent();
			
			require_once 'ovsettings.php';
			$this->ovSettings = new ovSettings();
		}
		
		function __destruct() 
		{
			
		}
		
		/** 
		 * Gets the feed info
		 * @param $type string Type of feed (POSSIBLE VALUES: popular, upcoming, category, tag, comment, user, all)
		 * @param $subtype string Submission type of feed (POSSIBLE VALUES: story, photo, video, podcast, self, all)
		 * @param $id string Identifier for the feed (Category name for category, tag name for tag, username for user)
		 * @param $popular string Popular flag (POSSIBLE VALUES: yes, no all)
		 * @return array Array containing the title, link, and description
		 * @access public
		 * @since 3.0
		 */
		public function GetFeedInfo($type, $subtype, $id, $popular)
		{
			if ($type == "category")
			{
				$rss_link = $this->ovSettings->RootURL();
				
				if ($id == "") {
					$category_name = "Popular";
					$id = "popular";
				} else {
					$category_name = $this->ovContent->GetCategoryNameFromSlug($id);
				}
				
				switch ($popular)
				{
					case "no":
						$rss_description = "Upcoming";
						$rss_link .= "/c/" . $id  . "/upcoming";
						break;
					case "yes":
					case "all":
					default:
						$rss_description = "Popular";
						$rss_link .= "/c/" . $id . "/popular";
						break;
				}
				
				switch ($subtype)
				{
					case "story":
						$rss_description .= " Stories in " . $category_name;
						$rss_link .= "/stories";
						break;
					case "photo":
						$rss_description .= " Photos in " . $category_name;
						$rss_link .= "/photos";
					break;
					case "video":
						$rss_description .= " Videos in " . $category_name;
						$rss_link .= "/videos";
						break;
					case "podcast":
						$rss_description .= " Podcasts in " . $category_name;
						$rss_link .= "/podcasts";
						break;
					case "self":
						$rss_description .= " Self Posts in " . $category_name;
						$rss_link .= "/self";
						break;
					case "all":
					default:
						$rss_description .= " Submissions in " . $category_name;
						break;
				}
				
				$rss_title = $rss_description . " | " . $this->ovSettings->Title();
				
				return array('title'=>$rss_title, 'link'=>$rss_link, 'desc'=>$rss_description);
			}
			elseif ($type == "tag") 
			{
				$rss_link = $this->ovSettings->RootURL();
				
				if ($id == "") {
					$tag_name = "Popular";
					$id = "popular";
				} else {
					$tag_name = $this->ovContent->GetTagNameFromSlug($id);
				}
				
				switch ($popular)
				{
					case "no":
						$rss_description = "Upcoming";
						$rss_link .= "/t/" . $id . "/upcoming";
						break;
					case "yes":
					case "all":
					default:
						$rss_description = "Popular";
						$rss_link .= "/t/" . $id . "/popular";
						break;
				}
				
				switch ($subtype)
				{
					case "story":
						$rss_description .= " Stories tagged " . $tag_name;
						$rss_link .= "/stories";
						break;
					case "photo":
						$rss_description .= " Photos tagged " . $tag_name;
						$rss_link .= "/photos";
					break;
					case "video":
						$rss_description .= " Videos tagged " . $tag_name;
						$rss_link .= "/videos";
						break;
					case "podcast":
						$rss_description .= " Podcasts tagged " . $category_name;
						$rss_link .= "/podcasts";
						break;
					case "self":
						$rss_description .= " Self Posts tagged " . $category_name;
						$rss_link .= "/self";
						break;
					case "all":
					default:
						$rss_description .= " Submissions tagged " . $tag_name;
						break;
				}
				
				$rss_title = $rss_description . " | " . $this->ovSettings->Title();
				
				return array('title'=>$rss_title, 'link'=>$rss_link, 'desc'=>$rss_description);
			}
			elseif ($type == "user")
			{
				$rss_title = "Submissions from " . $id . " | " . $this->ovSettings->Title();
				$rss_link = $this->ovSettings->RootURL() . "/" . strtolower($id);
				$rss_description = "Submissions from " . $id;
				
				return array('title'=>$rss_title, 'link'=>$rss_link, 'desc'=>$rss_description);
			}
			elseif ($type == "comment")
			{
				$rss_title = $ovSettings->Title();
				$rss_link = $ovSettings->RootURL();
				$rss_description = "Submissions from " . $ovSettings->Title();
				
				return array('title'=>$rss_title, 'link'=>$rss_link, 'desc'=>$rss_description);
			}
			else
			{
				// all
				switch ($subtype)
				{
					case "story":
						$rss_description = "Stories ";
						break;
					case "photo":
						$rss_description = "Photos ";
					break;
					case "video":
						$rss_description = "Videos ";
						break;
					case "podcast":
						$rss_description = "Podcasts ";
						break;
					case "self":
						$rss_description = "Self Posts ";
						break;
					case "all":
					default:
						$rss_description = "Submissions ";
						break;
				}

				switch ($popular)
				{
					case "no":
						$rss_description = "Upcoming " . $rss_description;
						break;
					case "yes":
						$rss_description = "Popular " . $rss_description;
						break;
				}

				$rss_title = $this->ovSettings->Title();
				$rss_link = $this->ovSettings->RootURL();
				$rss_description = $rss_description . "from " . $this->ovSettings->Title();
				
				return array('title'=>$rss_title, 'link'=>$rss_link, 'desc'=>$rss_description);
			}
		}
		
		/** 
		 * Gets all submissions
		 * @param $popular string Popular flag (POSSIBLE VALUES: yes, no, all)
		 * @param $sub_type string Submission type of feed (POSSIBLE VALUES: story, photo, video, podcast, self, all)
		 * @return array Array of submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetAllSubmissions($popular, $sub_type)
		{
			if ($popular == "yes") {
				$is_popular = "yes";
			} elseif ($popular == "no") {
				$is_popular = "no";
			} else {
				$is_popular = "all";
			}
			
			if (strtoupper($sub_type) == "STORY") {
				$submission_type = "STORY";
			} elseif (strtoupper($sub_type) == "PHOTO") {
				$submission_type = "PHOTO";
			} elseif (strtoupper($sub_type) == "VIDEO") {
				$submission_type = "VIDEO";
			} elseif (strtoupper($sub_type) == "PODCAST") {
				$submission_type = "PODCAST";
			} elseif (strtoupper($sub_type) == "SELF") {
				$submission_type = "SELF";
			} else {
				$submission_type = "all";
			}
			
			$query = "CALL RSSGetAllSubmissions(" . ovDBConnector::SiteID() . ", '$is_popular', '$submission_type')";
			$result = ovDBConnector::Query($query);

			//echo "<query>" . htmlspecialchars($query) . "</query>";

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['title'] = stripslashes($row['title']);
					$submission['description'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['link'] = $this->ovSettings->RootURL() . "/" . strtolower(stripslashes($row['type'])) . "/" . $row['id'] . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					
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
		 * Gets submissions for category
		 * @param $category_url_name string The URL name of the category
		 * @param $popular string Popular flag (POSSIBLE VALUES: yes, no, all)
		 * @param $sub_type string Submission type of feed (POSSIBLE VALUES: story, photo, video, podcast, self, all)
		 * @return array Array of submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetSubmissionsForCategory($category_url_name, $popular, $sub_type)
		{
			if ($popular == "yes") {
				$is_popular = "yes";
			} elseif ($popular == "no") {
				$is_popular = "no";
			} else {
				$is_popular = "all";
			}
			
			if (strtoupper($sub_type) == "STORY") {
				$submission_type = "STORY";
			} elseif (strtoupper($sub_type) == "PHOTO") {
				$submission_type = "PHOTO";
			} elseif (strtoupper($sub_type) == "VIDEO") {
				$submission_type = "VIDEO";
			} elseif (strtoupper($sub_type) == "PODCAST") {
				$submission_type = "PODCAST";
			} elseif (strtoupper($sub_type) == "SELF") {
				$submission_type = "SELF";
			} else {
				$submission_type = "all";
			}
			
			$category_url_name = mysql_escape_string($category_url_name);
			
			$query = "CALL RSSGetSubmissionsForCategory(" . ovDBConnector::SiteID() . ", '$category_url_name', '$submission_type', '$is_popular')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['title'] = stripslashes($row['title']);
					$submission['description'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['link'] = $this->ovSettings->RootURL() . "/" . strtolower(stripslashes($row['type'])) . "/" . $row['id'] . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					
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
		 * Gets submissions for tag
		 * @param $tag_url_name string The URL name of the tag
		 * @param $popular string Popular flag (POSSIBLE VALUES: yes, no, all)
		 * @param $sub_type string Submission type of feed (POSSIBLE VALUES: story, photo, video, podcast, self, all)
		 * @return array Array of submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetSubmissionsForTag($tag_url_name, $popular, $sub_type)
		{
			if ($popular == "yes") {
				$is_popular = "yes";
			} elseif ($popular == "no") {
				$is_popular = "no";
			} else {
				$is_popular = "all";
			}
			
			if (strtoupper($sub_type) == "STORY") {
				$submission_type = "STORY";
			} elseif (strtoupper($sub_type) == "PHOTO") {
				$submission_type = "PHOTO";
			} elseif (strtoupper($sub_type) == "VIDEO") {
				$submission_type = "VIDEO";
			} elseif (strtoupper($sub_type) == "PODCAST") {
				$submission_type = "PODCAST";
			} elseif (strtoupper($sub_type) == "SELF") {
				$submission_type = "SELF";
			} else {
				$submission_type = "all";
			}
			
			$tag_url_name = mysql_escape_string($tag_url_name);
			
			$query = "CALL RSSGetSubmissionsForTag(" . ovDBConnector::SiteID() . ", '$tag_url_name', '$submission_type', '$is_popular')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['title'] = stripslashes($row['title']);
					$submission['description'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['link'] = $this->ovSettings->RootURL() . "/" . strtolower(stripslashes($row['type'])) . "/" . $row['id'] . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					
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
		 * Gets all submissions from a user
		 * @param $username Username of user
		 * @return array Array of submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetSubmissionsForUser($username)
		{
			$username = mysql_escape_string($username);
			
			$query = "CALL RSSGetSubmissionsForUser(" . ovDBConnector::SiteID() . ", '$username')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['title'] = stripslashes($row['title']);
					$submission['description'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['link'] = $this->ovSettings->RootURL() . "/" . strtolower(stripslashes($row['type'])) . "/" . $row['id'] . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					
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
		 * Content Class Object
		 * @access protected
		 * @var ovContent
		 * @since 3.0
		 */
		protected $ovContent;
		
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