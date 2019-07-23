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
	 * OpenVoter Submission Object Class
	 * Class holding the data from a submission
	 *
	 * @package OpenVoter
	 * @subpackage oSubmission
	 * @since 3.0
	 */
	class ovoSubmission
	{
		/**
		 * Instantiates the object
		 * @param array $submission Array of data from the SQL database
		 * @access public
		 * @since 3.0
		 */
		function __construct($submission)
		{	
			require_once 'ovdbconnector.php';
			
			require_once 'ovusersecurity.php';
			$this->ovUserSecurity = new ovUserSecurity();
			
			require_once 'ovsubmission.php';
			$this->ovSubmission = new ovSubmission();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			$this->_id = $submission['id'];
			$this->_type = $submission['type'];
			$this->_title = $submission['title'];
			$this->_summary = $this->ovUtilities->FormatBody($submission['summary'], true);
			$this->_url = $submission['url'];
			if (strtolower($this->_type) != "self") {
				$this->_domain = $this->ovUtilities->GetDomain($this->_url);
			} else {
				$this->_domain = "";
			}
	
			$this->_score = $this->ovSubmission->GetSubmissionScore($this->_id);
			$this->_thumbnail = $submission['thumbnail'];
			$this->_is_popular = $submission['popular'];
			$this->_popular_date = $submission['popular_date'];
			
			if ($this->_popular_date != "") {
				$this->_popular_date = $this->ovUtilities->CalculateTimeAgo($submission['popular_date']);
			}
			
			$this->_can_edit = $submission['can_edit'];
			$this->_location = $submission['location'];
			$this->_date = $this->ovUtilities->CalculateTimeAgo($submission['date']);
			$this->_user_id = $submission['user_id'];
			$this->_username = $submission['username'];
			$this->_avatar = $submission['avatar'];
			$this->_page_url = "/" . strtolower($this->_type) . "/" . $this->_id . "/" . $this->ovUtilities->ConvertToUrl($this->_title);
			
			require_once 'ovcontent.php';
			$ovContent = new ovContent();
			
			$this->_is_domain_restricted = $ovContent->IsDomainRestricted($this->URL());
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * @return int The PK of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function ID() { return $this->_id; }
		
		/**
		 * @return string The Type of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Type() { return $this->_type; }
		
		/**
		 * @return string The Title of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Title() { return $this->_title; }
		
		/**
		 * @return string The summary of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Summary() { return $this->_summary; }
		
		/**
		 * @return string The URL of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function URL() { return $this->_url; }
		
		/**
		 * @return string The domain of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Domain() { return $this->_domain; }
		
		/**
		 * @return int The score of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Score() { return $this->_score; }
		
		/**
		 * @return string The thumbnail for the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Thumbnail() { return $this->_thumbnail; }
		
		/**
		 * @return bool Popular flag
		 * @access public
		 * @since 3.0
		 */
		public function IsPopular() { return $this->_is_popular; }
		
		/**
		 * @return string The date the Submission went popular
		 * @access public
		 * @since 3.0
		 */
		public function PopularDate() { return $this->_popular_date; }
		
		/**
		 * @return int The date the submission was posted
		 * @access public
		 * @since 3.0
		 */
		public function SubmissionDate() { return $this->_date; }
		
		/**
		 * @return bool Can edit submission flag
		 * @access public
		 * @since 3.0
		 */
		public function CanEdit() { return $this->_can_edit; }
		
		/**
		 * @return string The location of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Location() { return $this->_location; }
		
		/**
		 * @return int The PK of the user who posted the Submission
		 * @access public
		 * @since 3.0
		 */
		public function UserID() { return $this->_user_id; }
		
		/**
		 * @return string The username of the user who posted the submission
		 * @access public
		 * @since 3.0
		 */
		public function Username() { return $this->_username; }
		
		/**
		 * @return string The avatar of the user who posted the Submission
		 * @access public
		 * @since 3.0
		 */
		public function Avatar() { return $this->_avatar; }
		
		/**
		 * @return string The internal page URL of the Submission
		 * @access public
		 * @since 3.0
		 */
		public function PageURL() { return $this->_page_url; }
		
		/**
		 * @return bool Domain restriction flag
		 * @access public
		 * @since 3.1
		 */
		public function IsDomainRestricted() { return $this->_is_domain_restricted; }
		
		/**
		 * @return array The comments for the submission
		 * @access public
		 * @since 3.0
		 */
		public function GetComments()
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$logged_in_user_id = $this->ovUserSecurity->LoggedInUserID();
			} else {
				$logged_in_user_id = "NULL";
			}
			
			$query = "CALL GetCommentsForSubmission(" . ovDBConnector::SiteID() . ", " . $this->_id . ", $logged_in_user_id)";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// error
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$comments = array();
				while ($row = $result->fetch_assoc())
				{
					$comment['id'] = $row['id'];
					$comment['body'] = $this->ovUtilities->FormatBody($row['body']);
					$comment['score'] = stripslashes($row['score']);
					$comment['date'] = $row['date_created'];
					$comment['username'] = stripslashes($row['username']);
					$comment['avatar'] = $row['avatar'];
					
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
		 * Votes on a submission
		 * @param int $direction Direction of the vote 1 = UP, -1 = DOWN
		 * @return bool Success flag
		 * @access public
		 * @since 3.0
		 */
		public function Vote($direction)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$this->ovSubmission->AddVote($this->_id, $direction);
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * Outputs the HTML for the categories for the submission sidebar
		 * @access public
		 * @since 3.0
		 */
		public function ListCategories()
		{
			$query = "CALL GetSubmissionCategories(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				echo "<a href=\"/c/popular\" title=\"Popular\">Popular</a>";
			}
			
			if ($result->num_rows > 0)
			{
				$category_string = "";
				
				while ($row = $result->fetch_assoc())
				{
					$category_url_name = stripslashes($row['url_name']);
					$category_name = stripslashes($row['name']);
					
					$category_string .= "<a href=\"/c/$category_url_name\" title=\"$category_name\">$category_name</a>, "; 
				}
				
				// remove last comma and trailing space
				$category_string = substr($category_string, 0, strlen($category_string) - 2);
				
				ovDBConnector::FreeResult();
				
				echo $category_string;
			}
			else
			{	ovDBConnector::FreeResult();
				echo "<a href=\"/c/popular\" title=\"Popular\">Popular</a>";
			}
		}

		/**
		 * Gets the categories for the submission
		 * @return array|false Array of categories OR false if error
		 * @access public
		 * @since 3.3
		 */
		public function GetCategories()
		{
			$query = "CALL GetSubmissionCategories(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$categories = array();
				while ($row = $result->fetch_assoc())
				{
					$category['url_name'] = stripslashes($row['url_name']);
					$category['name'] = stripslashes($row['name']);
					
					array_push($categories, $category);
				}
				
				ovDBConnector::FreeResult();
				
				return $categories;
			}
			else
			{	ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Outputs the HTML for the tags for the submission sidebar
		 * @access public
		 * @since 3.0
		 */
		public function ListTags()
		{
			$query = "CALL GetSubmissionTags(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				echo "No Tags";
			}
			
			if ($result->num_rows > 0)
			{
				$tag_string = "";
				
				while ($row = $result->fetch_assoc())
				{
					$tag_url_name = stripslashes($row['url_name']);
					$tag_name = stripslashes($row['name']);
					
					$tag_string .= "<a href=\"/t/$tag_url_name\" title=\"$tag_name\">$tag_name</a>, "; 
				}
				
				// remove last comma and trailing space
				$tag_string = substr($tag_string, 0, strlen($tag_string) - 2);
				
				echo $tag_string;
				ovDBConnector::FreeResult();
			}
			else
			{
				echo "No Tags";
				ovDBConnector::FreeResult();
			}
		}

		/**
		 * Gets the tags for the submission
		 * @return array|false Array of tags OR false if error
		 * @access public
		 * @since 3.3
		 */
		public function GetTags()
		{
			$query = "CALL GetSubmissionTags(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$tags = array();
				while ($row = $result->fetch_assoc())
				{
					$tag['url_name'] = stripslashes($row['url_name']);
					$tag['name'] = stripslashes($row['name']);
					
					array_push($tags, $tag);
				}
				
				ovDBConnector::FreeResult();
				
				return $tags;
			}
			else
			{	ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Outputs the HTML for the tags for the submission sidebar
		 * @access public
		 * @since 3.0
		 */
		public function ListTagsMobilePage()
		{
			$query = "CALL GetSubmissionTags(" . ovDBConnector::SiteID() . ", " . $this->_id . ")";
			$result = ovDBConnector::Query($query);
			
			if (!$result) {
				// ERROR
				echo "No Tags";
			}
			
			if ($result->num_rows > 0)
			{
				$tag_string = "";
				
				while ($row = $result->fetch_assoc())
				{
					$tag_url_name = stripslashes($row['url_name']);
					$tag_name = stripslashes($row['name']);
					
					$tag_string .= "<li onclick=\"navigateTo('/m/t/$tag_url_name')\">";
					$tag_string .= "<div class=\"tag-list-name\">$tag_name</div>";
					$tag_string .= "<a class=\"arrow\"></a>";
					$tag_string .= "<div class=\"clearfix\"></div>";
					$tag_string .= "</li>";
				}
				
				// remove last comma and trailing space
				$tag_string = substr($tag_string, 0, strlen($tag_string) - 2);
				
				echo $tag_string;
				ovDBConnector::FreeResult();
			}
			else
			{
				echo "<li>No Tags</li>";
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the Votes for a submission
		 * @return array|false Array of votes for the submission OR false if errored
		 * @access public
		 * @since 3.0
		 */
		public function GetVotes()
		{
			$query = "CALL GetSubmissionVotes(" . $this->_id . ")";
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
					$vote['username'] = stripslashes($row['username']);
					$vote['avatar'] = stripslashes($row['avatar']);
					
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
		 * PK of Submission
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_id;
		
		/**
		 * Type of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_type;
		
		/**
		 * Title of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_title;
		
		/**
		 * Summary of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_summary;
		
		/**
		 * URL of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_url;
		
		/**
		 * Domain of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_domain;
		
		/**
		 * Score of Submission
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_score;
		
		/**
		 * Thumbnail for the Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_thumbnail;
		
		/**
		 * Popular flag
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_is_popular;
		
		/**
		 * Date submission went popular
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_popular_date;
		
		/**
		 * Date of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_date;
		
		/**
		 * Edit flag
		 * @access protected
		 * @var bool
		 * @since 3.0
		 */
		protected $_can_edit;
		
		/**
		 * Location of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_location;
		
		/**
		 * PK of user who posted Submission
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_user_id;
		
		/**
		 * Username of user who posted Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_username;
		
		/**
		 * Avatar of user who posted the Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_avatar;
		
		/**
		 * Internal Page URL of Submission
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_page_url;
		
		/**
		 * Restricted domain flag
		 * @access protected
		 * @var bool
		 * @since 3.1
		 */
		protected $_is_domain_restricted;
		
		/**
		 * User Security Class Object
		 * @access protected
		 * @var ovUserSecurity
		 * @since 3.0
		 */
		protected $ovUserSecurity;
		
		/**
		 * Submission Class Object
		 * @access protected
		 * @var ovSubmission
		 * @since 3.0
		 */
		protected $ovSubmission;
		
		/**
		 * Utilities Class Object
		 * @access protected
		 * @var ovUtilities
		 * @since 3.0
		 */
		protected $ovUtilities;
	}
?>
