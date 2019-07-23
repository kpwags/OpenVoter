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
	 * OpenVoter Submission Class
	 * Class handling Submission Calls
	 *
	 * @package OpenVoter
	 * @subpackage Submission
	 * @since 3.0
	 */
	class ovSubmission
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
		 * Adds a submission to the site
		 * @param $title string The title of the submission
		 * @param $summary string The summary of the submission
		 * @param $url string The URL of the submission
		 * @param $type string The type of submission (STORY, PHOTO, VIDEO, PODCAST, SELF)
		 * @param $override bool I have no idea why this is here
		 * @return false Returns false on error
		 * @access public
		 * @since 3.0
		 */
		public function AddSubmission($title, $summary, $url, $type, $override = false)
		{
			$url = mysql_escape_string($url);
			
			if (strtolower($type) != "self") {
				$query = "CALL GetSubmissionDataFromURL(" . ovDBConnector::SiteID() . ", '$url')";
				$result = ovDBConnector::Query($query);

				if (!$result)
				{
					// ERROR
					return false;
				}

				if ($result->num_rows > 0)
				{
					$row = $result->fetch_assoc();

					$id = $row['id'];
					$title = $this->ovUtilities->ConvertToUrl(stripslashes($row['title']));
					ovDBConnector::FreeResult();
					header("Location: /" . strtolower($row['type']) . "/$id/$title/?already_submitted=yes");
					exit();
				} else {
					ovDBConnector::FreeResult();
				}
			}
			
			$title = mysql_escape_string($title);
			$summary = mysql_escape_string($summary);
			$type = mysql_escape_string($type);
			
			// make sure there is a logged in user
			if ($this->ovUserSecurity->IsUserLoggedIn())
			{
				$user_id = $this->ovUserSecurity->LoggedInUserID();
				
				$query = "CALL AddSubmission(" . ovDBConnector::SiteID() . ", $user_id, '$type', '$title', '$summary', '$url')";
				$result = ovDBConnector::Query($query);

				if (!$result)
				{
					// ERROR
					return false;
				}

				if ($result->num_rows > 0)
				{
					$row = $result->fetch_assoc();
					$submission_id = $row['id'];
					
					ovDBConnector::FreeResult();
					
					require_once 'ovuser.php';
					$ovUser = new ovUser();
					
					if ($ovUser->SubscribeOnSubmit($user_id)) {
						$this->Subscribe($user_id, $submission_id);
					}
					
					return $submission_id;
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
		 * Applies the tags to the submission
		 * @param $submission_id int The submission's ID
		 * @param $tag_array array Array of tags
		 * @access public
		 * @since 3.0
		 */
		public function AddSubmissionTags($submission_id, $tag_array)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			if ($tag_array && count($tag_array) > 0)
			{
				foreach($tag_array as $tag)
				{
					$tag = mysql_escape_string($tag);
					$query = "CALL AddSubmissionTag(" . ovDBConnector::SiteID() . ", $submission_id, $tag)";
					ovDBConnector::ExecuteNonQuery($query);
					ovDBConnector::FreeResult();
				}
			}
		}
		
		/**
		 * Applies the categories to the submission
		 * @param $submission_id int The submission's ID
		 * @param $tag_array array Array of categories
		 * @access public
		 * @since 3.0
		 */
		public function AddSubmissionCategories($submission_id, $category_array)
		{
			$submission_id = mysql_escape_string($submission_id);
						
			if ($category_array && count($category_array) > 0)
			{
				foreach($category_array as $category)
				{
					$category = mysql_escape_string($category);
					$query = "CALL AddSubmissionCategory(" . ovDBConnector::SiteID() . ", $submission_id, $category)";
					ovDBConnector::ExecuteNonQuery($query);
					ovDBConnector::FreeResult();
				}
			}
		}
		
		/**
		 * Votes a submission
		 * @param $submission_id int The submission's ID
		 * @param $new_direction int Direction of vote (-1 for DOWN / 1 for UP)
		 * @access public
		 * @since 3.0
		 */
		public function AddVote($submission_id, $new_direction)
		{
			$submission_id = mysql_escape_string($submission_id);
			$new_direction = mysql_escape_string($new_direction);
				
			if ($this->ovUserSecurity->IsUserLoggedIn())
			{
				$user_id = $this->ovUserSecurity->LoggedInUserID();
				
				$query = "CALL SubmissionVote(" . ovDBConnector::SiteID() . ", $user_id, $submission_id, $new_direction)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
				
				if ($new_direction == 1)
				{
					//only check popular if voted up
					$submission_details = $this->GetSubmissionDetails($submission_id);
					
					if ($submission_details) {
						if (!$submission_details['popular']) {
							// only check for popular if not yet popular
							$this->CheckForPopular($submission_id);
						}
					}
				}
			}
		}
		
		/**
		 * Checks to see if the logged in user has voted on a submission
		 * @param $submission_id int The submission's ID
		 * @return int|false Direction of vote if user has voted / False if user has not or not logged in
		 * @access public
		 * @since 3.0
		 */
		function CheckForVote($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn())
			{
				$user_id = $this->ovUserSecurity->LoggedInUserID();
				$query = "CALL GetSubmissionVote(" . ovDBConnector::SiteID() . ", $user_id, $submission_id)";
				$result = ovDBConnector::Query($query);
				
				if (!$result)
				{
					// error
					return "ERROR";
				}
				
				if ($result->num_rows > 0)
				{
					// vote exists, return direction
					$row = $result->fetch_assoc();
					$direction = $row['direction'];

					ovDBConnector::FreeResult();
					
					return $direction;
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
				return false;
			}
		}

		/**
		 * Gets the number of submissions not posted to a group
		 * @param $type string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $is_popular bool Popular flag (defaults to false)
		 * @return int Submission count
		 * @access public
		 * @since 3.3
		 */
		public function GetAllSubmissionsCount($type = "", $is_popular = false) 
		{
			$type = mysql_escape_string($type);
			
			if ($type == "all") {
				$type = "";
			}
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user = "NULL";
			}

			if ($is_popular) {
				$popular = 1;
			} else {
				$popular = 0;
			}

			$query = "CALL GetAllSubmissionsCount(" . ovDBConnector::SiteID() . ", '$type', $popular, $logged_in_user)";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				ovDBConnector::FreeResult();
				
				return $row['num_subs'];
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}	
		}

		/**
		 * Gets all submissions not posted to a group
		 * @param string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $is_popular bool Popular flag (defaults to false)
		 * @param $page_number int The current page number
		 * @return array|false Array of submission data or false if error or no submissions
		 * @access public
		 * @since 3.3
		 */
		public function GetAllSubmissions($subtype, $is_popular, $page_number)
		{
			switch (strtolower($subtype)) {
				case "stories":
				case "story":
					$subtype = "STORY";
					break;
				case "photos":
				case "photo":
					$subtype = "PHOTO";
					break;
				case "videos":
				case "video":
					$subtype = "VIDEO";
					break;
				case "podcasts":
				case "podcast":
					$subtype = "PODCAST";
					break;
				case "self":
					$subtype = "SELF";
					break;
				case "all":
				default:
					$subtype = "";
					break;
			}
			
			$num_subs = $this->GetAllSubmissionsCount($subtype, $is_popular);
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$subtype = mysql_escape_string($subtype);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$is_popular = mysql_escape_string($is_popular);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user = "NULL";
			}
			
			if ($is_popular) {
				$popular = 1;
			} else {
				$popular = 0;
			}

			$query = "CALL GetAllSubmissions(" . ovDBConnector::SiteID() . ", '$subtype', $popular, $offset, $limit, $logged_in_user)";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
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
				
				ovDBConnector::FreeResult();
				
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult();
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the number of submissions in a given category
		 * @param $category_url_name string The URL name of the category
		 * @param $type string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $is_popular bool Popular flag (defaults to false)
		 * @return int Submission count
		 * @access public
		 * @since 3.0
		 */
		public function GetCountForCategory($category_url_name, $type = "", $is_popular = false)
		{
			$category_url_name = mysql_escape_string($category_url_name);
			$type = mysql_escape_string($type);
			
			if ($type == "all") {
				$type = "";
			}
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user = "NULL";
			}
			
			if ($is_popular) {
				$query = "CALL GetSubmissionPopularCountForCategory(" . ovDBConnector::SiteID() . ", '$category_url_name', '$type', $logged_in_user)";
			} else {
				$query = "CALL GetSubmissionCountForCategory(" . ovDBConnector::SiteID() . ", '$category_url_name', '$type', $logged_in_user)";
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
				
				ovDBConnector::FreeResult();
				
				return $row['num_subs'];
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets submissions for given category
		 * @param $category_url_name string The URL name of the category
		 * @param string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $is_popular bool Popular flag (defaults to false)
		 * @param $page_number int The current page number
		 * @return array|false Array of submission data or false if error or no submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetForCategory($category_url_name, $submission_type, $is_popular = false, $page_number = 1)
		{
			$subtype = "";
			switch (strtolower($submission_type)) {
				case "stories":
				case "story":
					$subtype = "story";
					break;
				case "photos":
				case "photo":
					$subtype = "PHOTO";
					break;
				case "videos":
				case "video":
					$subtype = "VIDEO";
					break;
				case "podcasts":
				case "podcast":
					$subtype = "podcast";
					break;
				case "self":
					$subtype = "self";
					break;
				case "all":
				default:
					$subtype = "";
					break;
			}
			
			$num_subs = $this->GetCountForCategory($category_url_name, $submission_type, $is_popular);
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$category_url_name = mysql_escape_string($category_url_name);
			$submission_type = mysql_escape_string($submission_type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$is_popular = mysql_escape_string($is_popular);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user = "NULL";
			}
			
			if ($is_popular) {
				$query = "CALL GetSubmissionsPopularForCategory(" . ovDBConnector::SiteID() . ", '$category_url_name', '$subtype', $offset, $limit, $logged_in_user)";
			} else {
				$query = "CALL GetSubmissionsForCategory(" . ovDBConnector::SiteID() . ", '$category_url_name', '$subtype', $offset, $limit, $logged_in_user)";
			}
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
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
				
				ovDBConnector::FreeResult();
				
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult();
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the number of submissions in a given tag
		 * @param $tag_url_name string The URL name of the tag
		 * @param $type string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $is_popular bool Popular flag (defaults to false)
		 * @return int Submission count
		 * @access public
		 * @since 3.0
		 */
		public function GetCountForTag($tag_url_name, $type = "", $is_popular = false)
		{
			$tag_url_name = mysql_escape_string($tag_url_name);
			$type = mysql_escape_string($type);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user = "NULL";
			}
			
			if ($is_popular) {
				$query = "CALL GetSubmissionPopularCountForTag(" . ovDBConnector::SiteID() . ", '$tag_url_name', '$type', $logged_in_user)";
			} else {
				$query = "CALL GetSubmissionCountForTag(" . ovDBConnector::SiteID() . ", '$tag_url_name', '$type', $logged_in_user)";
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

				ovDBConnector::FreeResult();

				return $row['num_subs'];
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets submissions for given tag
		 * @param $tag_url_name string The URL name of the tag
		 * @param $submission_type string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $is_popular bool Popular flag (defaults to false)
		 * @param $page_number int The current page number
		 * @return array|false Array of submission data or false if error or no submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetForTag($tag_url_name, $submission_type, $is_popular = false, $page_number = 1)
		{
			$subtype = "";
			switch (strtolower($submission_type)) {
				case "stories":
				case "story":
					$subtype = "story";
					break;
				case "photos":
				case "photo":
					$subtype = "PHOTO";
					break;
				case "videos":
				case "video":
					$subtype = "VIDEO";
					break;
				case "podcasts":
				case "podcast":
					$subtype = "podcast";
					break;
				case "self":
					$subtype = "self";
					break;
				case "all":
				default:
					$subtype = "";
					break;
			}
			
			$num_subs = $this->GetCountForTag($tag_url_name, $subtype, $is_popular);
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user = "NULL";
			}
			
			$tag_url_name = mysql_escape_string($tag_url_name);
			$submission_type = mysql_escape_string($submission_type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			$is_popular = mysql_escape_string($is_popular);
			
			if ($is_popular) {
				$query = "CALL GetSubmissionsPopularForTag(" . ovDBConnector::SiteID() . ", '$tag_url_name', '$subtype', $offset, $limit, $logged_in_user)";
			} else {
				$query = "CALL GetSubmissionsForTag(" . ovDBConnector::SiteID() . ", '$tag_url_name', '$subtype', $offset, $limit, $logged_in_user)";
			}
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
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
				
				ovDBConnector::FreeResult();

				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult();
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Gets the details of the specified submission
		 * @param $submission_id int The PK of the submission
		 * @return array|false Array of submission data or false if error or no submission
		 * @access public
		 * @since 3.0
		 */
		public function GetSubmissionDetails($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			$query = "CALL GetSubmissionDetails(" . ovDBConnector::SiteID() . ", $submission_id)";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();

				$submission['id'] = $row['id'];
				$submission['type'] = strtolower(stripslashes($row['type']));
				$submission['title'] = stripslashes($row['title']);
				$submission['summary'] = $row['summary'];
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
				
				ovDBConnector::FreeResult();
				
				return $submission;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/**
		 * Gets the top 10 submissions for a category -- used for the sidebar
		 * @param $category string Category name
		 * @param $type string Type of submission
		 * @param $is_popular bool Popular flag
		 * @return array|false Returns submission data or false on error or no submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetTopSubmissionsForCategory($category, $type, $is_popular)
		{
			$category = mysql_escape_string($category);
			$type = mysql_escape_string($type);

			if ($is_popular) {
				$query = "CALL GetTopPopularSubmissionsForCategory(" . ovDBConnector::SiteID() . ", '$category', '$type')";
			} else {
				$query = "CALL GetTopUpcomingSubmissionsForCategory(" . ovDBConnector::SiteID() . ", '$category', '$type')";
			}

			
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$top_submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['page_url'] = "/" . $submission['type'] . "/" . $submission['id'] . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					
					array_push($top_submissions, $submission);
				}
				
				ovDBConnector::FreeResult();
				return $top_submissions;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the top 10 submissions for a tag -- used for the sidebar
		 * @param $tag string Tag name
		 * @param $type string Type of submission
		 * @param $is_popular bool Popular flag
		 * @return array|false Returns submission data or false on error or no submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetTopSubmissionsForTag($tag, $type, $is_popular)
		{
			$tag = mysql_escape_string($tag);
			$type = mysql_escape_string($type);
			
			if ($is_popular) {
				$query = "CALL GetTopPopularSubmissionsForTag(" . ovDBConnector::SiteID() . ", '$tag', '$type')";
			} else {
				$query = "CALL GetTopUpcomingSubmissionsForTag(" . ovDBConnector::SiteID() . ", '$tag', '$type')";
			}

			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$top_submissions = array();
				while ($row = $result->fetch_assoc())
				{
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['score'] = $row['score'];
					$submission['page_url'] = "/" . $submission['type'] . "/" . $submission['id'] . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					
					array_push($top_submissions, $submission);
				}
				
				ovDBConnector::FreeResult();
				
				return $top_submissions;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Checks to see if a user has marked a submission as a favorite
		 * @param $submission_id int The PK of the submission
		 * @return bool Favorite flag
		 * @access public
		 * @since 3.0
		 */
		public function IsFavorite($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$query = "CALL IsSubmissionFavorite(" . ovDBConnector::SiteID() . ", $submission_id, " . $this->ovUserSecurity->LoggedInUserID() . ")";
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
		 * Adds a location to the submission
		 * @param $submission_id int The PK of the submission
		 * @param $location string The location for the submission
		 * @access public
		 * @since 3.0
		 */
		public function AddLocation($submission_id, $location)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$submission = $this->GetSubmissionDetails($submission_id);
			
				if ($submission)
				{
					if ($this->ovUserSecurity->LoggedInUserID() == $submission['user_id'])
					{
						$submission_id = mysql_escape_string($submission_id);
						$location = mysql_escape_string($location);
						
						if (trim($submission_id) != "" && trim($location) != "")
						{
							$query = "CALL AddLocationToSubmission(" . ovDBConnector::SiteID() . ", $submission_id, '$location')";
							ovDBConnector::ExecuteNonQuery($query);
							ovDBConnector::FreeResult();
						}
					}
				}
			}
		}
		
		/**
		 * Edits the submission
		 * @param $submission_id int The PK of the submission
		 * @param $title string The new title for the submission
		 * @param $summary string The new summary for the submission
		 * @access public
		 * @since 3.0
		 */
		public function EditSubmission($submission_id, $title, $summary)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$submission = $this->GetSubmissionDetails($submission_id);
			
				if ($submission)
				{
					if ($this->ovUserSecurity->LoggedInUserID() == $submission['user_id'])
					{
						$submission_id = mysql_escape_string($submission_id);
						$title = mysql_escape_string($title);
						$summary = mysql_escape_string($summary);
						
						if (trim($submission_id) != "" && trim($title) != "")
						{
							$query = "CALL EditSubmission(" . ovDBConnector::SiteID() . ", $submission_id, '$title', '$summary')";
							ovDBConnector::ExecuteNonQuery($query);
							ovDBConnector::FreeResult();

							return array('status' => 'OK', 'title' => stripslashes($title), 'summary' => $this->ovUtilities->FormatBody($summary, false), 'message' => 'Changes saved.');
						} else {
							return array('status' => 'ERROR', 'message' => 'You must enter a title.');
						}
					} else {
						// not user's submission to edit
						return array('status' => 'ERROR', 'message' => 'You can only edit your own submissions.');
					}
				} else {
					// can't find submission
					return array('status' => 'ERROR', 'message' => 'Unable to find submission.');
				}
			} else {
				// user not logged in
				return array('status' => 'ERROR', 'message' => 'You must be logged in to edit your submission.');
			}
		}

		/**
		 * Deletes the submission (activated by user)
		 * @param $submission_id int The PK of the submission
		 * @access public
		 * @since 3.3
		 */
		public function UserDeleteSubmission($submission_id)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$submission = $this->GetSubmissionDetails($submission_id);
			
				if ($submission)
				{
					if ($this->ovUserSecurity->LoggedInUserID() == $submission['user_id'])
					{
						$submission_id = mysql_escape_string($submission_id);
						
						if (trim($submission_id) != "")
						{
							$query = "CALL DeleteSubmission(" . ovDBConnector::SiteID() . ", $submission_id)";
							ovDBConnector::ExecuteNonQuery($query);
							ovDBConnector::FreeResult();

							return array('status' => 'OK', 'message' => 'Submission Deleted.');
						} else {
							// can't find submission
							return array('status' => 'ERROR', 'message' => 'Invalid Submission.');
						}
					} else {
						// not user's submission to edit
						return array('status' => 'ERROR', 'message' => 'You can only delete your own submissions.');
					}
				} else {
					// can't find submission
					return array('status' => 'ERROR', 'message' => 'Unable to find submission.');
				}
			} else {
				// user not logged in
				return array('status' => 'ERROR', 'message' => 'You must be logged in to delete your submission.');
			}
		}
		
		/**
		 * Adds a submission to the logged in user's favorites
		 * @param $submission_id int The PK of the submission
		 * @return bool Returns false on error
		 * @access public
		 * @since 3.0
		 */
		public function AddFavorite($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL AddSubmissionFavorite(" . ovDBConnector::SiteID() . ", $submission_id, " . $this->ovUserSecurity->LoggedInUserID() . ")";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();

				$submission_details = $this->GetSubmissionDetails($submission_id);
				
				if ($submission_details) {
					require_once 'ovalerting.php';
					$ovAlerting = new ovAlerting();
					$ovAlerting->ProcessNewFavoriteAlerts($submission_details['user_id'], $submission_id, $this->ovUserSecurity->LoggedInUserID());
				}
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Removes a submission to the logged in user's favorites
		 * @param $submission_id int The PK of the submission
		 * @return bool Returns false on error
		 * @access public
		 * @since 3.0
		 */
		public function DeleteFavorite($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				$query = "CALL DeleteSubmissionFavorite(" . ovDBConnector::SiteID() . ", $submission_id, " . $this->ovUserSecurity->LoggedInUserID() . ")";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Checks to see if a user is subscribed to a submission
		 * @param $submission_id int The PK of the submission
		 * @param $user_id int OPTIONAL pass in a user id if you want to check someone other than the logged in user
		 * @return bool Subscription flag
		 * @access public
		 * @since 3.0
		 */
		public function IsUserSubscribed($submission_id, $user_id = false)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) 
			{
				if (!$user_id) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				}
				
				$submission_id = mysql_escape_string($submission_id);
				$user_id = mysql_escape_string($user_id);
				
				$query = "CALL IsUserSubscribed(" . ovDBConnector::SiteID() . ", $submission_id, $user_id)";
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
		 * Adds a submission to a user's subscription list
		 * @param $user_id int The PK of the user
		 * @param $submission_id int The PK of the submission
		 * @access public
		 * @since 3.0
		 */
		public function Subscribe($user_id, $submission_id)
		{
			$user_id = mysql_escape_string($user_id);
			$submission_id = mysql_escape_string($submission_id);
			
			$query = "CALL SubscribeToThread(" . ovDBConnector::SiteID() . ", $submission_id, $user_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Removes a submission to a user's subscription list
		 * @param $user_id int The PK of the user
		 * @param $submission_id int The PK of the submission
		 * @access public
		 * @since 3.0
		 */
		public function Unsubscribe($user_id, $submission_id)
		{
			$user_id = mysql_escape_string($user_id);
			$submission_id = mysql_escape_string($submission_id);
			
			$query = "CALL UnsubscribeFromThread(" . ovDBConnector::SiteID() . ", $submission_id, $user_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Gets the number of submissions in a given search
		 * @param $keywords string The Search string
		 * @param $type string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in 'all' to bring back all types
		 * @param $popular string Popular flag (yes, no, all)
		 * @param $time_limit string SQL time frame (eg 1 WEEK, 1 DAY)
		 * @return int Submission count
		 * @access public
		 * @since 3.0
		 */
		public function SearchCount($keywords, $type = "all", $popular = "all", $time_limit = "")
		{
			error_reporting(0);
			if ($popular == "yes") {
				$popular_clause = "AND popular = 1";
			} elseif ($popular == "no") {
				$popular_clause = "AND popular = 0";
			} else {
				$popular_clause = "";
			}
			
			if ($type == "all") {
				$type_clause = "";
			} else {
				if ($type == "video") {
					$type_clause = "AND type = 'VIDEO'";
				} elseif ($type == "photo") {
					$type_clause = "AND type = 'PHOTO'";
				} elseif ($type == "podcast") {
					$type_clause = "AND type = 'PODCAST'";
				} elseif ($type == "self") {
					$type_clause = "AND type = 'SELF'";
				} else {
					$type_clause = "AND type = 'STORY'";
				}
			}
			
			if ($time_limit != "") {
				$time_clause = "AND s.date_created >= DATE_SUB(NOW(),INTERVAL $time_limit)";
			} else {
				$time_clause = "";
			}
			
			$query_string = $this->GetSearchString($keywords);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$blocked_user_clause = "AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE user_id = " . $this->ovUserSecurity->LoggedInUserID() . " AND active = 1)"; 
			} else {
				$blocked_user_clause = "";
			}
			
			//$query = "CALL GetSearchCount(" . ovDBConnector::SiteID() . ", '$type_clause', '$is_popular', '$query_string', $logged_in_user, '$time_limit')";
			
			$query = "SELECT COUNT(s.id) as num_subs FROM " . DB_PREFIX . "submission s 
				WHERE 
				$query_string $type_clause $popular_clause $time_clause $blocked_user_clause 
				AND s.active = 1 AND s.site_id = " . ovDBConnector::SiteID();
			
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
				
				ovDBConnector::FreeResult();
				
				return $row['num_subs'];
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the submissions from a search
		 * @param $keywords string The Search string
		 * @param $type string The type of submission (STORY, PHOTO, VIDEO, PODCAST, SELF, all)
		 * @param $popular string Popular flag (yes, no, all)
		 * @param $ordering string How should the results be ordered by (date or score) (NOT IN USE)
		 * @param $page_number int Current Page
		 * @param $time_limit SQL time frame (eg 1 WEEK, 1 DAY)
		 * @return array|false Array of submission data or false on error or no submissions
		 * @access public
		 * @since 3.0
		 */
		public function Search($keywords, $type = "all", $popular = "all", $ordering = "date", $page_number, $time_limit = "")
		{
			error_reporting(0);
			$num_subs = $this->SearchCount($keywords, $type, $popular, $time_limit);
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			if ($ordering == "score") {
				$sort_column = "s.score";
			} else {
				if ($popular == "yes") {
					$sort_column = "s.popular_date";
				} else {
					$sort_column = "s.date_created";
				}
			}

			$query_string = $this->GetSearchString($keywords);

			if ($query_string == "") {
				return array('submissions' => false, 'last-page' => 1);
			}

			
			
			if ($type == "all") {
				$type_clause = "";
			} else {
				if ($type == "video") {
					$type_clause .= "AND type = 'VIDEO'";
				} elseif ($type == "photo") {
					$type_clause .= "AND type = 'PHOTO'";
				} elseif ($type == "podcast") {
					$type_clause .= "AND type = 'PODCAST'";
				} elseif ($type == "self") {
					$type_clause .= "AND type = 'SELF'";
				} else {
					$type_clause .= "AND type = 'STORY'";
				}
			}

			if ($popular == "yes") {
				$popular_clause .= "AND popular = 1";
			} elseif ($popular == "no") {
				$popular_clause .= "AND popular = 0";
			} else {
				$popular_clause = "";
			}


			
			if ($time_limit != "") {
				$time_clause = "AND s.date_created >= DATE_SUB(NOW(),INTERVAL $time_limit)";
			} else {
				$time_clause = "";
			}
			
			
			
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$blocked_user_clause = "AND s.submitted_by_user_id NOT IN (SELECT user_is_blocking_id FROM " . DB_PREFIX . "blocked_user WHERE user_id = " . $this->ovUserSecurity->LoggedInUserID() . " AND active = 1)"; 
			} else {
				$blocked_user_clause = "";
			}
			
			//$query = "CALL SearchSubmissions(" . ovDBConnector::SiteID() . ", '$type_clause', '$is_popular', '$query_string', '$order_clause', $logged_in_user, $offset, $limit, '$time_limit')";
			$query = "SELECT 
				s.id, s.type, s.title, s.summary, s.url, s.score, s.thumbnail, s.popular, s.popular_date, s.date_created, 
				s.submitted_by_user_id AS user_id, s.can_edit, s.location, u.username, u.avatar 
			FROM " . DB_PREFIX . "submission s 
			INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
			WHERE 
			$query_string 
			$type_clause 
			$popular_clause 
			$time_clause 
			$blocked_user_clause
			AND s.active = 1 
			AND s.site_id = " . ovDBConnector::SiteID() . 
			" ORDER BY $sort_column DESC LIMIT $offset, $limit";
			
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
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
				
				ovDBConnector::FreeResult();
				
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult();
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Builds the search query string for the search functions
		 * @param $keywords string Keywords array
		 * @return string The search query string
		 * @access protected
		 * @since 3.0
		 */
		protected function GetSearchString($keywords)
		{
			/**** BUILD THE SEARCH STRING FOR KEYWORDS ****/
			$keywords_array = explode("+", $keywords);
			$search_table = DB_PREFIX . "search";
			$search_query_string = "";

			if (count($keywords_array) > 0) {
				$search_query_string .= "(";
				for($i = 0; $i < count($keywords_array); $i++)
				{
					$term = mysql_escape_string($keywords_array[$i]);
					if (strlen($term) > 3 || count($keywords_array) == 1) {
						$search_query_string .= "s.id IN (SELECT submission_id FROM $search_table WHERE MATCH(title, summary) AGAINST ('$term')) OR s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag WHERE tag_id IN (SELECT id FROM " . DB_PREFIX . "tag WHERE name LIKE '%$term%' AND active = 1))";

						if ($i < (count($keywords_array) - 1 )) {
							$search_query_string = $search_query_string . " OR ";
						}
					}
				}

				$search_query_string .= ")";

				if ($search_query_string == "()") {
					$search_query_string = "";
				}
			}
			
			return $search_query_string;
		}
		
		/**
		 * Gets the number of submissions from a user's friends
		 * @param $user_id int The PK of the user (defaults to false indicating use logged in user)
		 * @return int Submission count
		 * @access public
		 * @since 3.0
		 */
		public function GetFriendSubmissionCount($user_id = false)
		{
			if (!$user_id && !$this->ovUserSecurity->IsUserLoggedIn()) {
				return false;
			}
			
			if (!$user_id && $this->ovUserSecurity->IsUserLoggedIn()) {
				$user_id = $this->ovUserSecurity->LoggedInUserID();
			}
			
			$user_id = mysql_escape_string($user_id);
			
			$query = "CALL GetFriendSubmissionCount(" . ovDBConnector::SiteID() . ", $user_id)";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();
			
				ovDBConnector::FreeResult();
			
				return $row['num_subs'];
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the submissions from a user's friends
		 * @param $user_id int The PK of the user (defaults to false indicating use logged in user)
		 * @param $page_number int The Current Page number
		 * @return array|false Array of submission data or false on error or no submissions
		 * @access public
		 * @since 3.0
		 */
		public function GetFriendSubmissions($user_id = false, $page_number = 1)
		{
			$num_subs = $this->GetFriendSubmissionCount($user_id, $page_number);
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			if (!$user_id && !$this->ovUserSecurity->IsUserLoggedIn()) {
				return false;
			}
			
			if (!$user_id && $this->ovUserSecurity->IsUserLoggedIn()) {
				$user_id = $this->ovUserSecurity->LoggedInUserID();
			}
			
			$user_id = mysql_escape_string($user_id);
			$limit = mysql_escape_string($limit);
			$offset = mysql_escape_string($offset);
			
			$query = "CALL GetFriendSubmissions(" . ovDBConnector::SiteID() . ", $user_id, $offset, $limit)";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
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
				
				ovDBConnector::FreeResult();
				
				return array('submissions' => $submissions, 'last-page' => $limits[2]);
			}
			else
			{
				ovDBConnector::FreeResult();
				return array('submissions' => false, 'last-page' => 1);
			}
		}
		
		/**
		 * Checks to see if the submission crossed the popular threshold, and if it did, make it popular
		 * @param $submission_id int The PK of the submission
		 * @param $score int The score of the submission
		 * @access protected
		 * @since 3.0
		 */
		protected function CheckForPopular($submission_id)
		{
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			
			$score = $this->GetSubmissionScore($submission_id);
			
			if ($ovSettings->Algorithm() == "dynamic") {
				// dynamic algorithm
				if ($score >= $this->GetAverageScore() && $score > 1) {
					$this->MakePopular($submission_id);
				}
			} else {
				// static algorithm
				if ($score >= $ovSettings->Threshold()) {
					$this->MakePopular($submission_id);
				}
			}
		}
		
		/**
		 * Makes a submission popular
		 * @param $submission_id The PK of the submission
		 * @access protected
		 * @since 3.0
		 */
		protected function MakePopular($submission_id)
		{
			$submission_id = mysql_escape_string($submission_id);
			$query = "CALL UpdateSubmissionMakePopular(" . ovDBConnector::SiteID() . ", $submission_id)";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		/**
		 * Gets the average score of the submissions over the recent days
		 * @return int The average score
		 * @access protected
		 * @since 3.0
		 */
		protected function GetAverageScore()
		{
			$query = "CALL GetAverageSubmissionScore(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				return 0;
			}

			if ($result->num_rows > 0)
			{			
				$row = $result->fetch_assoc();	
				$avg_score = $row['average_score'];
				
				ovDBConnector::FreeResult();
				
				return ceil($avg_score);
			}
			else
			{
				ovDBConnector::FreeResult();
				return 0;
			}
		}
		
		/**
		 * Uploads and sets an Image as a Submission Thumbnail
		 * @param $submission_id int Submission PK
		 * @param $image string Image to crop
		 * @param $filename string Filename to save the image as
		 * @param $image_width int Width of new image
		 * @param $image_height int Height of new image
		 * @param $scale double Scale to crop image as
		 * @return string Image filename
		 * @access public
		 * @since 3.0
		 */
		public function SetSubmissionThumbnail($submission_id, $image, $filename, $image_width = 200, $image_height = 200, $scale = 1)
		{
			if ($submission_id == "" || $image == "") {
				return array('status' => 'ERROR', 'message' => 'Invalid Image or Submission');
			}

			$submission_id = mysql_escape_string($submission_id);
			$db_filename = "/ov-upload/thumbnails/" . $filename;
			$filename = "./../ov-upload/thumbnails/" . $filename;

			require_once('phpthumb/ThumbLib.inc.php');
			$thumb = PhpThumbFactory::create($image);  
			$thumb->resize(200, 200)->save($filename);
			
			$query = "CALL SetSubmissionThumbnail($submission_id, '$db_filename')";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();

			return array('status' => 'OK', 'message' => 'Image set', 'src' => $db_filename);
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
		
		
		/**
		 * Sets the Thumbnail in the database without an upload
		 * @param $submission_id int Submission PK
		 * @param $img_src string Image Name
		 * @access public
		 * @since 3.2
		 */
		public function SetSubmissionDBThumbnail($submission_id, $img_src)
		{
			$submission_id = mysql_escape_string($submission_id);
			$img_src = mysql_escape_string($img_src);
			
			$query = "CALL SetSubmissionThumbnail($submission_id, '$img_src')";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
		}
		
		
		protected $ovDBConnector;
		protected $ovUserSecurity;
		protected $ovUtilities;
	}
?>
