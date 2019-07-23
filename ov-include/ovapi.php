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
	 * OpenVoter API Class
	 * Class dealing with the API calls to the site
	 *
	 * @package OpenVoter
	 * @subpackage API
	 * @since 3.0
	 */
	class ovAPI
	{
		function __construct()
		{
			require_once 'ovdbconnector.php';
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			
			require_once 'ovsubmission.php';
			$this->ovSubmission = new ovSubmission();
			
			require_once 'ovcomment.php';
			$this->ovComment = new ovComment();
			
			$this->_root_url = $ovSettings->RootURL();
			
			// checks to see if there is a trailing '/' and if there is, removing it
			if (substr($this->_root_url, -1) == "/") {
				$this->_root_url = substr($this->_root_url, 0, strlen($this->_root_url) - 1);
			}
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets Submissions in the specified category
		 * @access public
		 * @param $category string The name of the category
		 * @param $is_popular string Should the database return popular submissions, upcoming submissions, or all (POSSIBLE VALUES: yes, no, all) - Defaults to all
		 * @param $type string The type of submissions to return (POSSIBLE VALUES: story, photo, video, podcast, self, all) - Defaults to all 
		 * @param $offset int The offset for the SQL query - Defaults to 0
		 * @param $limit int The number of submissions to return - Defaults to 10
		 * @return array Submission Data
		 * @since 3.0
		 */
		public function GetSubmissionsByCategory($category, $is_popular = "all", $type = "", $offset = 0, $limit = 10)
		{
			$category = mysql_escape_string($category);
			$type = mysql_escape_string($type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			if ($is_popular == "no") {
				$popular = "s.popular = 0 AND";
				$order_col = "s.date_created";
			} elseif ($is_popular == "yes") {
				$popular = "s.popular = 1 AND";
				$order_col = "s.popular_date";
			} else {
				$popular = "";
				$order_col = "s.date_created";
			}
			
			if ($type != 'all') {
				$type_clause = "s.type = '" . strtoupper($type) . "' AND"; 
			} else {
				$type_clause = "";
			}
			
			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_category 
							WHERE category_id IN 
								(SELECT id FROM " . DB_PREFIX . "category 
									WHERE url_name = '$category' OR name = '$category' AND site_id = " . SITE_ID . " AND active = 1) AND active = 1) AND 
							$popular $type_clause s.active = 1 AND s.site_id = " . SITE_ID . " 
						ORDER BY $order_col DESC LIMIT $offset, $limit";

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
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);
					
					if ($row['thumbnail'] == "") {
						$submission['thumbnail'] = "";
					} else {
						$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];
					}

					if ($row['popular'] == 1) {
						$submission['popular'] = "true";
					} else {
						$submission['popular'] = "false";
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];
					$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					$submission['location'] = $row['location'];
					$submission['num_comments'] = $row['num_comments'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $this->_root_url . $row['avatar'];
					
					// get the categories
					$categories = "";
					$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
					$cat_result = ovDBConnector::Query($cat_query);
					
					if ($cat_result) {
						while($cat_row = $cat_result->fetch_assoc()) {
							$categories .= $cat_row['name'] . ",";
						}
						
						$categories = substr($categories, 0, -1);
					}
					$submission['categories'] = $categories;
					
					// get the tags
					$tags = "";
					$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
					$tag_result = ovDBConnector::Query($tag_query);
					
					if ($tag_result) {
						while($tag_row = $tag_result->fetch_assoc()) {
							$tags .= $tag_row['name'] . ",";
						}
						
						$tags = substr($tags, 0, -1);
					}
					$submission['tags'] = $tags;

					array_push($submissions, $submission);
				}

				return $submissions;
			}
			else
			{
				return false;
			}
		}
			
		/**
		 * Gets Submissions tagged with the specified tag
		 * @access public
		 * @param $tag string The name of the tag
		 * @param $is_popular string Should the database return popular submissions, upcoming submissions, or all (POSSIBLE VALUES: yes, no, all) - Defaults to all
		 * @param $type string The type of submissions to return (POSSIBLE VALUES: story, photo, video, podcast, self, all) - Defaults to all 
		 * @param $offset int The offset for the SQL query - Defaults to 0
		 * @param $limit int The number of submissions to return - Defaults to 10
		 * @return array Submission Data
		 * @since 3.0
		 */
		public function GetSubmissionsByTag($tag, $is_popular = "all", $type = "all", $offset = 0, $limit = 10)
		{
			$tag = mysql_escape_string($tag);
			$type = mysql_escape_string($type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			if ($is_popular == "no") {
				$popular = "s.popular = 0 AND";
				$order_col = "s.date_created";
			} elseif ($is_popular == "yes") {
				$popular = "s.popular = 1 AND";
				$order_col = "s.popular_date";
			} else {
				$popular = "(s.popular = 1 OR s.popular = 0) AND";
				$order_col = "s.date_created";
			}
			
			if ($type != 'all') {
				$type_clause = "s.type = '" . strtoupper($type) . "' AND"; 
			} else {
				$type_clause = "s.type != '' AND";
			}
			
			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							s.id IN (SELECT submission_id FROM " . DB_PREFIX . "submission_tag 
							WHERE tag_id IN 
								(SELECT id FROM " . DB_PREFIX . "tag 
									WHERE url_name = '$tag' OR name = '$tag' AND site_id = " . SITE_ID . " AND active = 1) AND active = 1) AND
							$popular $type_clause s.active = 1 AND s.site_id = " . SITE_ID . " 
						ORDER BY $order_col DESC LIMIT $offset, $limit";
		
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
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);
					$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];

					if ($row['popular'] == 1) {
						$submission['popular'] = "true";
					} else {
						$submission['popular'] = "false";
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];
					$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					$submission['location'] = $row['location'];
					$submission['num_comments'] = $row['num_comments'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $this->_root_url . $row['avatar'];
					
					// get the categories
					$categories = "";
					$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
					$cat_result = ovDBConnector::Query($cat_query);
					
					if ($cat_result) {
						while($cat_row = $cat_result->fetch_assoc()) {
							$categories .= $cat_row['name'] . ",";
						}
						
						$categories = substr($categories, 0, -1);
					}
					$submission['categories'] = $categories;
					
					// get the tags
					$tags = "";
					$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
					$tag_result = ovDBConnector::Query($tag_query);
					
					if ($tag_result) {
						while($tag_row = $tag_result->fetch_assoc()) {
							$tags .= $tag_row['name'] . ",";
						}
						
						$tags = substr($tags, 0, -1);
					}
					$submission['tags'] = $tags;
					
					array_push($submissions, $submission);
				}

				return $submissions;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Gets the data from a submission given the URL of the submission
		 * @access public
		 * @param string $url The URL of the submission
		 * @param bool $return_comments Flag as to whether to return comments on submission
		 * @return array Submission Data
		 * @since 3.0
		 */
		public function GetSubmissionByUrl($url, $return_comments = false)
		{
			$url = mysql_escape_string(urldecode($url));
			
			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							s.url = '$url' AND s.active = 1";
	
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
				$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
				$submission['url'] = stripslashes($row['url']);
				$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);
				$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];

				if ($row['popular'] == 1) {
					$submission['popular'] = "true";
				} else {
					$submission['popular'] = "false";
				}

				$submission['popular_date'] = $row['popular_date'];
				$submission['date'] = $row['date_created'];
				$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
				$submission['location'] = $row['location'];
				$submission['num_comments'] = $row['num_comments'];
				$submission['username'] = stripslashes($row['username']);
				$submission['avatar'] = $this->_root_url . $row['avatar'];
				
				// get the categories
				$categories = "";
				$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
				$cat_result = ovDBConnector::Query($cat_query);
				
				if ($cat_result) {
					while($cat_row = $cat_result->fetch_assoc()) {
						$categories .= $cat_row['name'] . ",";
					}
					
					$categories = substr($categories, 0, -1);
				}
				$submission['categories'] = $categories;
				
				// get the tags
				$tags = "";
				$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
				$tag_result = ovDBConnector::Query($tag_query);
				
				if ($tag_result) {
					while($tag_row = $tag_result->fetch_assoc()) {
						$tags .= $tag_row['name'] . ",";
					}
					
					$tags = substr($tags, 0, -1);
				}
				$submission['tags'] = $tags;
				
				$submission['comments'] = "";
				if ($return_comments) {
					$comment_query = "SELECT c.id, c.date_created, c.body, u.username, u.avatar 
						FROM " . DB_PREFIX . "comment c 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = c.user_id 
						WHERE c.submission_id = " . $submission['id'] . " AND c.active = 1 ORDER BY c.date_created ASC";
					$comment_result = ovDBConnector::Query($comment_query);
					
					if ($comment_result) {
						$comments = array();
						while($crow = $comment_result->fetch_assoc()) {
							$comment['id'] = $crow['id'];
							$comment['date'] = $crow['date_created'];
							$comment['body'] = $this->ovUtilities->FormatBody($crow['body'], false);
							$comment['username'] = stripslashes($crow['username']);
							$comment['avatar'] = $this->_root_url . $crow['avatar'];
							$comment['score'] = $this->ovComment->GetCommentScore($comment['id']);
							
							array_push($comments, $comment);
						}
						$submission['comments'] = $comments;
					}
				}

				return $submission;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Gets the data from a submission given the PK/ID of the submission
		 * @access public
		 * @param int $id The URL of the submission
		 * @param bool $return_comments Flag as to whether to return comments on submission
		 * @return array Submission Data
		 * @since 3.2
		 */
		public function GetSubmissionByID($id, $return_comments = false)
		{
			$id = mysql_escape_string($id);
			
			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							s.id = $id AND s.active = 1";
	
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
				$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
				$submission['url'] = stripslashes($row['url']);
				$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);
				$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];

				if ($row['popular'] == 1) {
					$submission['popular'] = "true";
				} else {
					$submission['popular'] = "false";
				}

				$submission['popular_date'] = $row['popular_date'];
				$submission['date'] = $row['date_created'];
				$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
				$submission['location'] = $row['location'];
				$submission['num_comments'] = $row['num_comments'];
				$submission['username'] = stripslashes($row['username']);
				$submission['avatar'] = $this->_root_url . $row['avatar'];
				
				// get the categories
				$categories = "";
				$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
				$cat_result = ovDBConnector::Query($cat_query);
				
				if ($cat_result) {
					while($cat_row = $cat_result->fetch_assoc()) {
						$categories .= $cat_row['name'] . ",";
					}
					
					$categories = substr($categories, 0, -1);
				}
				$submission['categories'] = $categories;
				
				// get the tags
				$tags = "";
				$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
				$tag_result = ovDBConnector::Query($tag_query);
				
				if ($tag_result) {
					while($tag_row = $tag_result->fetch_assoc()) {
						$tags .= $tag_row['name'] . ",";
					}
					
					$tags = substr($tags, 0, -1);
				}
				$submission['tags'] = $tags;
				
				$submission['comments'] = "";
				if ($return_comments) {
					$comment_query = "SELECT c.id, c.date_created, c.body, u.username, u.avatar 
						FROM " . DB_PREFIX . "comment c 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = c.user_id 
						WHERE c.submission_id = " . $submission['id'] . " AND c.active = 1 ORDER BY c.date_created ASC";
					$comment_result = ovDBConnector::Query($comment_query);
					
					if ($comment_result) {
						$comments = array();
						while($crow = $comment_result->fetch_assoc()) {
							$comment['id'] = $crow['id'];
							$comment['date'] = $crow['date_created'];
							$comment['body'] = $this->ovUtilities->FormatBody($crow['body'], false);
							$comment['username'] = stripslashes($crow['username']);
							$comment['avatar'] = $this->_root_url . $crow['avatar'];
							$comment['score'] = $this->ovComment->GetCommentScore($comment['id']);
							
							array_push($comments, $comment);
						}
						$submission['comments'] = $comments;
					}
				}

				return $submission;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Gets Submissions from a specified domain
		 * @access public
		 * @param $domain string The domain name (e.g. google.com, nbc.com)
		 * @param $is_popular string Should the database return popular submissions, upcoming submissions, or all (POSSIBLE VALUES: yes, no, all) - Defaults to all
		 * @param $type string The type of submissions to return (POSSIBLE VALUES: story, photo, video, podcast, self, all) - Defaults to all 
		 * @param $offset int The offset for the SQL query - Defaults to 0
		 * @param $limit int The number of submissions to return - Defaults to 10
		 * @return array Submission Data
		 * @since 3.0
		 */
		public function GetSubmissionsByDomain($domain, $is_popular = "all", $type = "all", $offset = 0, $limit = 10)
		{
			$domain = mysql_escape_string($domain);
			$type = mysql_escape_string($type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			if ($is_popular == "no") {
				$popular = "s.popular = 0 AND";
				$order_col = "s.date_created";
			} elseif ($is_popular == "yes") {
				$popular = "s.popular = 1 AND";
				$order_col = "s.popular_date";
			} else {
				$popular = "";
				$order_col = "s.date_created";
			}
			
			if ($type != 'all') {
				$type_clause = "s.type = '" . strtoupper($type) . "' AND"; 
			} else {
				$type_clause = "";
			}
			
			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							s.url LIKE '%$domain%' AND 
							$popular $type_clause s.active = 1 AND s.site_id = " . SITE_ID . " 
						ORDER BY $order_col DESC LIMIT $offset, $limit";
						
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
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);
					$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];

					if ($row['popular'] == 1) {
						$submission['popular'] = "true";
					} else {
						$submission['popular'] = "false";
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];
					$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					$submission['location'] = $row['location'];
					$submission['num_comments'] = $row['num_comments'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $this->_root_url . $row['avatar'];
					
					// get the categories
					$categories = "";
					$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
					$cat_result = ovDBConnector::Query($cat_query);
					
					if ($cat_result) {
						while($cat_row = $cat_result->fetch_assoc()) {
							$categories .= $cat_row['name'] . ",";
						}
						
						$categories = substr($categories, 0, -1);
					}
					$submission['categories'] = $categories;
					
					// get the tags
					$tags = "";
					$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
					$tag_result = ovDBConnector::Query($tag_query);
					
					if ($tag_result) {
						while($tag_row = $tag_result->fetch_assoc()) {
							$tags .= $tag_row['name'] . ",";
						}
						
						$tags = substr($tags, 0, -1);
					}
					$submission['tags'] = $tags;

					array_push($submissions, $submission);
				}

				return $submissions;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Gets All Submissions from the site
		 * @access public
		 * @param $is_popular string Should the database return popular submissions, upcoming submissions, or all (POSSIBLE VALUES: yes, no, all) - Defaults to all
		 * @param $type string The type of submissions to return (POSSIBLE VALUES: story, photo, video, podcast, self, all) - Defaults to all 
		 * @param $offset int The offset for the SQL query - Defaults to 0
		 * @param $limit int The number of submissions to return - Defaults to 10
		 * @return array Submission Data
		 * @since 3.0
		 */
		public function GetAllSubmissions($is_popular = "all", $type = "all", $offset = 0, $limit = 10)
		{
			$type = mysql_escape_string($type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);

			if ($is_popular == "no") {
				$popular = "s.popular = 0 AND";
				$order_col = "s.date_created";
			} elseif ($is_popular == "yes") {
				$popular = "s.popular = 1 AND";
				$order_col = "s.popular_date";
			} else {
				$popular = "";
				$order_col = "s.date_created";
			}

			if ($type != 'all') {
				$type_clause = "s.type = '" . strtoupper($type) . "' AND "; 
			} else {
				$type_clause = "";
			}

			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							$popular $type_clause s.active = 1 AND s.site_id = " . SITE_ID . " 
						ORDER BY $order_col DESC LIMIT $offset, $limit";

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
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);

					if ($row['thumbnail'] == "") {
						$submission['thumbnail'] = "";
					} else {
						$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];
					}

					if ($row['popular'] == 1) {
						$submission['popular'] = "true";
					} else {
						$submission['popular'] = "false";
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];
					$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					$submission['location'] = $row['location'];
					$submission['num_comments'] = $row['num_comments'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $this->_root_url . $row['avatar'];
					
					// get the categories
					$categories = "";
					$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
					$cat_result = ovDBConnector::Query($cat_query);
					
					if ($cat_result) {
						while($cat_row = $cat_result->fetch_assoc()) {
							$categories .= $cat_row['name'] . ",";
						}
						
						$categories = substr($categories, 0, -1);
					}
					$submission['categories'] = $categories;
					
					// get the tags
					$tags = "";
					$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
					$tag_result = ovDBConnector::Query($tag_query);
					
					if ($tag_result) {
						while($tag_row = $tag_result->fetch_assoc()) {
							$tags .= $tag_row['name'] . ",";
						}
						
						$tags = substr($tags, 0, -1);
					}
					$submission['tags'] = $tags;

					array_push($submissions, $submission);
				}

				return $submissions;
			}
			else
			{
				return false;
			}
		}
		
		/**
		 * Gets All Submissions submitted by a specific user
		 * @access public
		 * @param $username The Username of the user
		 * @param $is_popular string Should the database return popular submissions, upcoming submissions, or all (POSSIBLE VALUES: yes, no, all) - Defaults to all
		 * @param $type string The type of submissions to return (POSSIBLE VALUES: story, photo, video, podcast, self, all) - Defaults to all 
		 * @param $offset int The offset for the SQL query - Defaults to 0
		 * @param $limit int The number of submissions to return - Defaults to 10
		 * @return array Submission Data
		 * @since 3.0
		 */
		public function GetSubmissionsByUser($username, $is_popular = "all", $type = "all", $offset = 0, $limit = 10)
		{
			$username = mysql_escape_string($username);
			$type = mysql_escape_string($type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			if ($is_popular == "no") {
				$popular = "s.popular = 0 AND";
				$order_col = "s.date_created";
			} elseif ($is_popular == "yes") {
				$popular = "s.popular = 1 AND";
				$order_col = "s.popular_date";
			} else {
				$popular = "";
				$order_col = "s.date_created";
			}
			
			if ($type != 'all') {
				$type_clause = "s.type = '" . strtoupper($type) . "' AND"; 
			} else {
				$type_clause = "";
			}
			
			$query = "SELECT s.id, s.type, s.title, s.summary, s.url, s.thumbnail, s.popular, s.popular_date, s.date_created, s.location, u.username, u.avatar, (SELECT COUNT(id) FROM comment WHERE submission_id = s.id AND active = 1) AS num_comments 
						FROM " . DB_PREFIX . "submission s 
						INNER JOIN " . DB_PREFIX . "user u ON u.id = s.submitted_by_user_id 
						WHERE 
							s.submitted_by_user_id IN (SELECT id FROM " . DB_PREFIX . "user WHERE username = '$username' AND active = 1) AND 
							$popular $type_clause s.active = 1 AND s.site_id = " . SITE_ID . " 
						ORDER BY $order_col DESC LIMIT $offset, $limit";
						
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
					$submission['id'] = $row['id'];
					$submission['type'] = strtolower(stripslashes($row['type']));
					$submission['title'] = stripslashes($row['title']);
					$submission['summary'] = $this->ovUtilities->FormatBody($row['summary'], false);
					$submission['url'] = stripslashes($row['url']);
					$submission['score'] = $this->ovSubmission->GetSubmissionScore($submission['id']);
					$submission['thumbnail'] = $this->_root_url . $row['thumbnail'];

					if ($row['popular'] == 1) {
						$submission['popular'] = "true";
					} else {
						$submission['popular'] = "false";
					}

					$submission['popular_date'] = $row['popular_date'];
					$submission['date'] = $row['date_created'];
					$submission['page_url'] = $this->_root_url . "/" . strtolower($submission['type']) . "/" . $submission['id']  . "/" . $this->ovUtilities->ConvertToUrl($submission['title']);
					$submission['location'] = $row['location'];
					$submission['num_comments'] = $row['num_comments'];
					$submission['username'] = stripslashes($row['username']);
					$submission['avatar'] = $this->_root_url . $row['avatar'];
					
					// get the categories
					$categories = "";
					$cat_query = "SELECT name FROM " . DB_PREFIX . "category WHERE id IN (SELECT category_id FROM " . DB_PREFIX . "submission_category WHERE submission_id = " . $submission['id'] . ")";
					$cat_result = ovDBConnector::Query($cat_query);
					
					if ($cat_result) {
						while($cat_row = $cat_result->fetch_assoc()) {
							$categories .= $cat_row['name'] . ",";
						}
						
						$categories = substr($categories, 0, -1);
					}
					$submission['categories'] = $categories;
					
					// get the tags
					$tags = "";
					$tag_query = "SELECT name FROM " . DB_PREFIX . "tag WHERE id IN (SELECT tag_id FROM " . DB_PREFIX . "submission_tag WHERE submission_id = " . $submission['id'] . ")";
					$tag_result = ovDBConnector::Query($tag_query);
					
					if ($tag_result) {
						while($tag_row = $tag_result->fetch_assoc()) {
							$tags .= $tag_row['name'] . ",";
						}
						
						$tags = substr($tags, 0, -1);
					}
					$submission['tags'] = $tags;

					array_push($submissions, $submission);
				}

				return $submissions;
			}
			else
			{
				return false;
			}
		}

		public function GetUserDetails($username)
		{
			$username = mysql_escape_string($username);
			
			$query = "SELECT id, username, details, location, website, date_created, karma_points, avatar FROM " . DB_PREFIX . "user WHERE username = '$username' AND active = 1";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				
				$user['id'] = $row['id'];
				$user['username'] = stripslashes($row['username']);
				$user['details'] = $this->ovUtilities->FormatBody($row['details'], false);
				$user['location'] = stripslashes($row['location']);
				$user['website'] = stripslashes($row['website']);
				$user['join_date'] = stripslashes($row['date_created']);
				$user['points'] = stripslashes(floor($row['karma_points']));
				$user['avatar'] = $this->_root_url . $row['avatar'];
				
				$count_query = "SELECT COUNT(id) AS num_submissions FROM " . DB_PREFIX . "submission WHERE submitted_by_user_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num = $crow['num_submissions'];
					$user['num_submissions'] = $num;
				} else {
					$user['num_submissions'] = 0;
				}
				
				$count_query = "SELECT COUNT(id) AS num_comments FROM " . DB_PREFIX . "comment WHERE user_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num = $crow['num_comments'];
					$user['num_comments'] = $num;
				} else {
					$user['num_comments'] = 0;
				}
				
				$count_query = "SELECT COUNT(submission_id) AS num_votes FROM " . DB_PREFIX . "submission_vote WHERE user_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num = $crow['num_votes'];
					$user['num_votes'] = $num;
				} else {
					$user['num_votes'] = 0;
				}
				
				$num_sub_favorites = 0;
				$num_comment_favorites = 0;
				$count_query = "SELECT COUNT(submission_id) AS num_submission_favorites FROM " . DB_PREFIX . "submission_favorite WHERE user_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num_sub_favorites = $crow['num_submission_favorites'];
				}
				
				$count_query = "SELECT COUNT(comment_id) AS num_comment_favorites FROM " . DB_PREFIX . "comment_favorite WHERE user_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num_comment_favorites = $crow['num_comment_favorites'];
				}
				
				$user['num_favorites'] = $num_sub_favorites + $num_comment_favorites;
				
				$count_query = "SELECT COUNT(user_id) AS num_following FROM " . DB_PREFIX . "friend WHERE user_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num = $crow['num_following'];
					$user['num_following'] = $num;
				} else {
					$user['num_following'] = 0;
				}
				
				$count_query = "SELECT COUNT(user_id) AS num_followers FROM " . DB_PREFIX . "friend WHERE user_is_following_id = " . $user['id']  . " AND active = 1";
				$count_result = ovDBConnector::Query($count_query);
				
				if ($count_result) {
					$crow = $count_result->fetch_assoc();
					$num = $crow['num_followers'];
					$user['num_followers'] = $num;
				} else {
					$user['num_followers'] = 0;
				}
				
				return $user;
			} else {
				return false;
			}
		}
	
		/**
		 * The Root URL of the Site
		 * @access protected
		 * @var string
		 * @since 3.0
		 */ 
		protected $_root_url;
		
		/**
		 * Utilities Class Object
		 * @access protected
		 * @var ovUtilities
		 * @since 3.0
		 */
		protected $ovUtilities;
		
		/**
		 * Submission Class Object
		 * @access protected
		 * @var ovSubmission
		 * @since 3.2
		 */
		protected $ovSubmission;
		
		/**
		 * Comment Class Object
		 * @access protected
		 * @var ovComment
		 * @since 3.2
		 */
		protected $ovComment;
	}
?>