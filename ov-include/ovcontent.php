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
	 * OpenVoter Content Class
	 * Class dealing with general content for site
	 *
	 * @package OpenVoter
	 * @subpackage Content
	 * @since 3.0
	 */
	class ovContent
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
		 * Gets The categories either parent or child categories depending on arguments)
		 * @access public
		 * @param string $parent_category URL name of parent category
		 * @return array|false returns array of categories or false on error or no categories
		 * @since 3.0
		 */
		public function GetCategories($parent_category = false)
		{
			if ($parent_category) {
				$parent_category = mysql_escape_string($parent_category);
				$query = "CALL GetSubCategories(" . ovDBConnector::SiteID() . ", '$parent_category')";
			} else {
				$query = "CALL GetCategories(" . ovDBConnector::SiteID() . ")";
			}
					
			$result = ovDBConnector::Query($query);
			
			if(!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$categories = array();
				while ($row = $result->fetch_assoc())
				{
					$category['id'] = $row['id'];
					$category['name'] = stripslashes($row['name']);
					$category['url_name'] = stripslashes($row['url_name']);
					
					array_push($categories, $category);
				}
				
				ovDBConnector::FreeResult();
				return $categories;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets The URL name of the parent category
		 * @access public
		 * @param string $category_name URL name of category looking up its parent
		 * @return string|false returns the URL name or false on error or no category found
		 * @since 3.0
		 */
		public function GetParentCategory($category_name)
		{
			$category_name = mysql_escape_string($category_name);
			$query = "CALL GetParentCategory(" . ovDBConnector::SiteID() . ", '$category_name')";
			$result = ovDBConnector::Query($query);

			if(!$result)
			{
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$row = $result->fetch_assoc();

				ovDBConnector::FreeResult();
				
				return stripslashes($row['url_name']);
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets The full name of the parent category
		 * @access public
		 * @param string $url_name URL name of category looking up its name
		 * @return string|false returns the name or false on error or no category found
		 * @since 3.0
		 */
		public function GetCategoryNameFromSlug($url_name)
		{
			$url_name = mysql_escape_string($url_name);
			$query = "CALL GetCategoryNameFromUrlName(" . ovDBConnector::SiteID() . ", '$url_name')";
			$result = ovDBConnector::Query($query);
			
			if(!$result) {
				return false;
			}
			
			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult();
				return $row['name'];
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Validates a URL to ensure the URL is valid, that it is not banned, and that it is not already submitted
		 * @access public
		 * @param string $url URL of submission
		 * @param string $type Type of submission (story, photo, video)
		 * @return false returns false on error
		 * @since 3.0
		 */
		public function validateURL($url, $type)
		{
			$is_banned = false ;
			$is_valid = true ;
			
			
			
			$url = mysql_escape_string($url);
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
				
				$sub_url = "/" . strtolower($row['type']) . "/$id/$title";
				
				return array('code'=>"EXISTS", 'message'=>$sub_url);
				exit();
			} else {
				ovDBConnector::FreeResult();
			}

			if( !$this->isValidUrl($url) )
			{
				$is_valid = false;
			}

			$domain = $this->ovUtilities->GetDomain($url) ;

			$is_banned = $this->IsDomainBanned($domain);

			if ($is_banned)
			{
				return array('code'=>"BANNED", 'message'=>"");
			}
			
			if (!$is_valid)
			{
				return array('code'=>"INVALID", 'message'=>"");
			}
			
			return array('code'=>"OK", 'message'=>"");
		}

		/**
		 * Validates a URL to ensure the URL is valid and exists
		 * @access protected
		 * @param string $url URL of the submission
		 * @return bool True if valid, false if not
		 * @since 3.0
		 */
		protected function isValidUrl($url)
		{
			$url = @parse_url($url);

			if (!$url)
			{
				return false;
			}

			$url = array_map('trim', $url);
			$url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
			$path = (isset($url['path'])) ? $url['path'] : '';

			if ($path == '')
			{
				$path = '/';
			}

			$path .= (isset($url['query'])) ? "?$url[query]" : '';

			if (isset($url['host']) AND $url['host'] != gethostbyname($url['host']))
			{
				if (PHP_VERSION >= 5)
				{
					$headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
				}
				else
				{
					$fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

					if (!$fp)
					{
						return false;
					}
					fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
					$headers = fread($fp, 4096);
					fclose($fp);
				}
				$headers = (is_array($headers)) ? implode("\n", $headers) : $headers;
				return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
			}
			return false;
		}
		
		/**
		 * Attempts to get the title and the description from the meta tags of given URL
		 * @access public
		 * @param string $url URL of the submission
		 * @return array Array containing the Title and Description from the remote URL
		 * @since 3.0
		 */
		public function GetTitleAndDescription($url)
		{
			require 'ovmetatags.php';
			$ovMetaTags = new ovMetaTags();
			$ovMetaTags->getmetadata($url);
			
			$submission_title = $ovMetaTags->GetTitle();
			$submission_description = $ovMetaTags->GetDescription();
			
			return array('title' => strip_tags($submission_title), 'description' => strip_tags($submission_description));
		}
		
		/**
		 * Checks to see if given domain is banned from site
		 * @access protected
		 * @param string $domain_name Domain name to check against banned list
		 * @return bool True if banned, false if not
		 * @since 3.0
		 */
		protected function IsDomainBanned($domain_name)
		{
			$domain_name = mysql_escape_string($domain_name);
			$query = "CALL CheckBannedDomain(" . ovDBConnector::SiteID() . ", '$domain_name')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
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
		 * Checks if a domain is restricted
		 * @access public
		 * @param string $url The URL to get the domain to check
		 * @return bool True if restricted, false if not
		 * @since 3.0
		 */
		public function IsDomainRestricted($url)
		{
			$domain = $this->ovUtilities->GetDomain($url);
			
			$arr = explode(".", $domain);
			
			if (count($arr) > 2) {
				$domain = "";
				for($i = 1; $i < count($arr); $i++) {
					$domain .= $arr[$i] . ".";
				}
				$domain = substr($domain, 0, -1);
			}
			
			$query = "CALL IsDomainRestricted(" . ovDBConnector::SiteID() . ", '$domain')";
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
		 * #########################################################################
		 * # imageGrabber.php v1.0                                                 #
		 * # -----------                                                           #
		 * # Copyright (C) 2005 Aristidis Karidis, aris.karidis@bcs.org            #
		 * # ----------------------------------------------------------            #
		 * # This function grabs the images from one or more URLs and saves them   #
		 * # to a specified local directory.                                       #
		 * #                                                                       #
		 * #########################################################################
		 * #                                                                       #
		 * # This program is free software; you can redistribute it and/or         #
		 * # modify it under the terms of the GNU General Public License           #
		 * # as published by the Free Software Foundation; either version 2        #
		 * # of the License, or (at your option) any later version.                #
		 * # This program is distributed in the hope that it will be useful,       #
		 * # but WITHOUT ANY WARRANTY; without even the implied warranty of        #
		 * # MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         #
		 * # GNU General Public License for more details.                          #
		 * # ------------------------------------                                  #
		 * # http://www.gnu.org/copyleft/gpl.html                                  #
		 * #########################################################################
		 *   
		 *
		 * This function mines the image URLs form one or more webpages, returning an array of links.
		 * @access public
		 * @param string $url
		 * @param int $unique
		 * @return array
		 */
		public function imageGrabber($url)
		{
			$paths = array();
			
		    $startTag = '<img';
		    $srcTag = 'src=';
		    $endTag = '>';
		    $counter = 0;  		   

	        $contents = file_get_contents($url);
	       
	        $domain = $url;
	        $domain = substr($domain, 7);
	        $pos = stripos($domain, '/');
	       
	        if ($pos)
	        {
	            $domain = substr($domain, 0, stripos($domain, '/'));
	        }
	       
	        while ($contents)
	        {
	            set_time_limit(0);                                    # In case we have several large pages
	           
	            $quotes = array('"', "'", "\n");
	            $contents = str_replace($quotes, '', $contents);    # Strip " and ' as well as \n from input string
	            $contents = stristr($contents, $startTag);            # Drop everything before the start tag '<img'
	            $contents = stristr($contents, $srcTag);            # Drop everything before the 'src'
	           
	            $endTagPosition = stripos($contents, $endTag);        # Position of the end tag '>'
	            $src = substr($contents, 4, $endTagPosition - 4);    # Get everything from src to end tag --> 'src="path" something>'
	           
	            $spacePosition = stripos($src, ' ');                # Position of space (if it exists)               
	           
	            if ($spacePosition !== false)
	            {
	                $src = substr($src, 0, $spacePosition);            # Drop everything after space, keeping 'src="path"'
	            }
	           
	            $questionMarkPosition = stripos($src, '?');
	           
	            if ($questionMarkPosition !== false)
	            {
	                $src = substr($src, 0, $questionMarkPosition);    # Remove any part after a '?'
	            }
	           
	            $contents = stristr($contents, $endTag);            # Drop everything before the end tag '>'
	           
	            if ($src)
	            {
	                if (stripos($src, '/') === 0)
	                {
	                    $src = 'http://'.$domain.$src;                # Relative link, so add domain before '/'
	                }
	                else
	                {
	                    if (stripos($src, 'http://') !== 0 && stripos($src, 'https://') !== 0 && stripos($src, 'ftp://') !== 0)
	                    {
	                        $src = 'http://'.$domain.'/'.$src;        # Relative link, so add domain and '/'
	                    }
	                }
	               
	               // checks to make sure the image is either jpg, jpeg, png, or gif, if its not, does nothing.
	               if( strripos($src, ".jpg") || strripos($src, ".png") || strripos($src, ".gif") || strripos($src, ".jpeg") )
	               {
	               		$paths[] = $src;
	               }
	            }
	        }
		   
		    return $paths;
		}
		
		/**
		 * Takes a string of tags and returns an array broken down into name and URL name
		 * @access public
		 * @param string $tags Comma separated list of tags
		 * @return array returns an array of tags
		 * @since 3.0
		 */
		public function BreakDownTags($tags)
		{
			$tag_array = explode(',', $tags);
			$return_array = array();
			foreach ($tag_array as $tag)
			{
				$tag_item['name'] = $tag;
				$tag_item['url_name'] = $this->ovUtilities->ConvertToUrl($tag);
				
				array_push($return_array, $tag_item);
			}
			
			return $return_array;
		}
		
		/**
		 * Adds tags to the database
		 * @access public
		 * @param array $tag_array Array of tags (should contain 2 indexes, 'name' and 'url_name')
		 * @return array returns an array of PKs from the added tags
		 * @since 3.0
		 */
		public function AddTags($tag_array)
		{
			$return_array = array();
			foreach($tag_array as $tag)
			{
				$tag_id = $this->AddTagToDatabase($tag['name'], $tag['url_name']);
				
				if ($tag_id)
				{
					array_push($return_array, $tag_id);
				}
			}
			
			return $return_array;
		}
		
		/**
		 * Adds tags to the database
		 * @access protected
		 * @param string $tag_name Name of the Tag
		 * @param string $tag_url_name URL name of the Tag
		 * @return int|false returns PK of added tag or false on error
		 * @since 3.0
		 */
		protected function AddTagToDatabase($tag_name, $tag_url_name)
		{
			$tag_name = mysql_escape_string($tag_name);
			$tag_url_name = mysql_escape_string($tag_url_name);
			$query = "CALL AddTag(" . ovDBConnector::SiteID() . ", '$tag_name', '$tag_url_name')";
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
				return $row['id'];
			}
			else
			{
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the full name of a tag given its URL name
		 * @access public
		 * @param string $url_name URL name of tag
		 * @return string|false returns name of tag or false on error/not found
		 * @since 3.0
		 */
		public function GetTagNameFromSlug($url_name)
		{
			$url_name = mysql_escape_string($url_name);
			$query = "CALL GetTagNameFromUrlName(" . ovDBConnector::SiteID() . ", '$url_name')";
			$result = ovDBConnector::Query($query);

			if(!$result) {
				return false;
			}

			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				ovDBConnector::FreeResult();
				return $row['name'];
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Reports a submission
		 * @access public
		 * @param int $submission_id PK of the submission being reported
		 * @param string $reason reason given by user for report
		 * @param string $details more details given by user for why submission was reported
		 * @return string Returns a string indicating result of report (OK = All Good, REPEAT = User already reported, ERROR = self explanatory)
		 * @since 3.0
		 */
		public function ReportSubmission($submission_id, $reason, $details)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$user_id = $this->ovUserSecurity->LoggedInUserID();
				$submission_id = mysql_escape_string($submission_id);
				$reason = mysql_escape_string($reason);
				$details = mysql_escape_string($details);

				$query = "CALL AddSubmissionReport(" . ovDBConnector::SiteID() . ", $submission_id, '$reason', '$details', '$user_id')";
				$result = ovDBConnector::Query($query);
				
				if ($result) {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						
						if ($row['report_result'] == "OK") {
							ovDBConnector::FreeResult();
							$this->NotifyAdminOfReport("Submission", $submission_id, $reason, $details);
							return "OK";
						} else {
							// already reported
							ovDBConnector::FreeResult();
							return "REPEAT";
						}
					} else {
						// no rows returned...something happened
						ovDBConnector::FreeResult();
						return "ERROR";
					}
				} else {
					// error in query
					return "ERROR";
				}
				
			} else {
				return "ERROR";
			}
		}
		
		/**
		 * Reports a comment
		 * @access public
		 * @param int $comment_id PK of the comment being reported
		 * @param string $reason reason given by user for report
		 * @param string $details more details given by user for why comment was reported
		 * @return string Returns a string indicating result of report (OK = All Good, REPEAT = User already reported, ERROR = self explanatory)
		 * @since 3.0
		 */
		public function ReportComment($comment_id, $reason, $details)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$user_id = $this->ovUserSecurity->LoggedInUserID();
				$comment_id = mysql_escape_string($comment_id);
				$reason = mysql_escape_string($reason);
				$details = mysql_escape_string($details);

				$query = "CALL AddCommentReport(" . ovDBConnector::SiteID() . ", $comment_id, '$reason', '$details', '$user_id')";
				$result = ovDBConnector::Query($query);
				
				if ($result) {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						
						if ($row['report_result'] == "OK") {
							ovDBConnector::FreeResult();
							$this->NotifyAdminOfReport("Comment", $comment_id, $reason, $details);
							return "OK";
						} else {
							// already reported
							ovDBConnector::FreeResult();
							return "REPEAT";
						}
					} else {
						// no rows returned...something happened
						ovDBConnector::FreeResult();
						return "ERROR";
					}
				} else {
					// error in query
					return "ERROR";
				}
				
			} else {
				return "ERROR";
			}
		}
		
		/**
		 * Reports a user
		 * @access public
		 * @param int $reported_user_id PK of the user being reported
		 * @param string $reason reason given by user for report
		 * @param string $details more details given by user for why user was reported
		 * @return string Returns a string indicating result of report (OK = All Good, REPEAT = User already reported, ERROR = self explanatory)
		 * @since 3.0
		 */
		public function ReportUser($reported_user_id, $reason, $details)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$user_id = $this->ovUserSecurity->LoggedInUserID();
				$reported_user_id = mysql_escape_string($reported_user_id);
				$reason = mysql_escape_string($reason);
				$details = mysql_escape_string($details);

				$query = "CALL AddUserReport(" . ovDBConnector::SiteID() . ", $reported_user_id, '$reason', '$details', '$user_id')";
				$result = ovDBConnector::Query($query);
				
				if ($result) {
					if ($result->num_rows > 0) {
						$row = $result->fetch_assoc();
						
						if ($row['report_result'] == "OK") {
							ovDBConnector::FreeResult();
							$this->NotifyAdminOfReport("User", $reported_user_id, $reason, $details);
							return "OK";
						} else {
							// already reported
							ovDBConnector::FreeResult();
							return "REPEAT";
						}
					} else {
						// no rows returned...something happened
						ovDBConnector::FreeResult();
						return "ERROR";
					}
				} else {
					// error in query
					return "ERROR";
				}
				
			} else {
				return "ERROR";
			}
		}
		
		/**
		 * Gets the Site's Frequently Asked Questions
		 * @access public
		 * @return array|false Returns an array of QAs or false on error or none
		 * @since 3.0
		 */
		public function GetFAQs()
		{
			$query = "CALL GetFAQs(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0)
			{
				$faqs = array();
				while ($row = $result->fetch_assoc())
				{
					$faq['id'] = $row['id'];
					$faq['question'] = $row['question'];
					$faq['answer'] = $this->ovUtilities->FormatBody($row['answer']);
					
					array_push($faqs, $faq);
				}

				ovDBConnector::FreeResult();

				return $faqs;
			}
			else 
			{
				ovDBConnector::FreeResult();

				return false;
			}
		}
		
		/**
		 * Leaves feedback for the site's admins
		 * @access public
		 * @param string $name Name of the user leaving feedback
		 * @param string $email Email address of the user leaving feedback
		 * @param string $reason Reason for feedback (bug, message, etc)
		 * @param string $message The message from the user
		 * @since 3.0
		 */
		public function LeaveFeedback($name, $email, $reason, $message)
		{
			$name = mysql_escape_string($name);
			$email = mysql_escape_string($email);
			$reason = mysql_escape_string($reason);
			$message = mysql_escape_string($message);
			
			$query = "CALL LeaveFeedback(" . ovDBConnector::SiteID() . ", '$name', '$email', '$reason', '$message')";
			ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();
			
			$this->NotifyAdminOfFeedback($name, $email, $message);
		}
		
		/**
		 * Notifies the site admin when a new feedback message is left
		 * @access protected
		 * @since 3.0
		 */
		protected function NotifyAdminOfFeedback($user, $email, $feedback)
		{
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			$feedback_emails = $ovSettings->FeedbackEmails();
			
			require_once 'ovemailer.php';
			$ovEmailer = new ovEmailer();
			
			if ($feedback_emails) {
				foreach($feedback_emails as $email) {
					$domain = $this->ovUtilities->GetDomain($ovSettings->RootURL());
					$ovEmailer->SetFromEmail("new_feedback@" . $domain);
					$ovEmailer->SetSource("New Feedback");
					
					$feedback = str_replace("\\r\\n", "<br/>", $feedback);

					$message = "<html><body>";
					$message .= "<pA new Feedback Message Has Been Left on " . $ovSettings->Title() . "</p>";
					$message .= "<p>$user ($email) has left the following message: </p>";
					$message .= "<p>$feedback</p>";
					$message .= "</body></html>";
					
					$ovEmailer->SendEmail($email, $message, "New Feedback");
				}
			}
		}
		
		/**
		 * Notifies the site admin when a new report is filed
		 * @access protected
		 * @since 3.0
		 */
		protected function NotifyAdminOfReport($report_object_type, $report_object_id, $reason, $details)
		{
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			$report_emails = $ovSettings->ReportEmails();
			
			switch($report_object_type) {
				case "User":
					$title = $this->GetUsername($report_object_id);
					break;
				case "Comment":
					$title = $this->GetCommentUsername($report_object_id);
					break;
				case "Submission":
					$title = $this->GetSubmissionTitle($report_object_id);
					break;
			}
			
			require_once 'ovemailer.php';
			$ovEmailer = new ovEmailer();
			
			if ($report_emails) {
				foreach($report_emails as $email) {
					$domain = $this->ovUtilities->GetDomain($ovSettings->RootURL());
					$ovEmailer->SetFromEmail("new_report@" . $domain);
					$ovEmailer->SetSource("New Report");
					
					$details = stripslashes(str_replace("\\n", "<br/>", $details));
				
					$message = "<html><body>";
					$message .= "<pA new Moderation Report Has Been Left on " . $ovSettings->Title() . "</p>";
					$message .= "<p><b>Type:</b> $report_object_type</p>";
					$message .= "<p><b>Reported:</b> $title</p>";
					$message .= "<p><b>Reported By:</b> " . $this->ovUserSecurity->LoggedInUsername() . "</p>";
					$message .= "<p><b>Reason:</b> $reason</p>";
					$message .= "<p><b>Details:</b><br/>$details</p>";
					$message .= "</body></html>";
				
					$ovEmailer->SendEmail($email, $message, "New Moderation Report");
				}
			}
		}
		
		/**
		 * Gets the title of a submission
		 * @param int $submission_id PK of submission
		 * @return string The title of the submission
		 * @access protected
		 * @since 3.0
		 */
		protected function GetSubmissionTitle($submission_id)
		{
			require_once 'ovsubmission.php';
			$ovSubmission = new ovSubmission();
			
			$submission = $ovSubmission->GetSubmissionDetails($submission_id);
			
			if ($submission) {
				return $submission['title'];
			} else {
				return "Unknown Submission";
			}
		}
		
		/**
		 * Gets the username of a user
		 * @param int $user_id PK of the user
		 * @return string The username of the user
		 * @access protected
		 * @since 3.0
		 */
		protected function GetUsername($user_id)
		{
			require_once 'ovouser.php';
			$ovoUser = new ovoUser($user_id);
			
			if ($ovoUser->Username()) {
				return $ovoUser->Username();
			} else {
				return "Unknown User";
			}
		}
		
		/**
		 * Gets the username of a user from the ID of the comment
		 * @param int $comment_id PK of the comment
		 * @return string The username of the user
		 * @access protected
		 * @since 3.0
		 */
		protected function GetCommentUsername($comment_id)
		{
			require_once 'ovcomment.php';
			$ovComment = new ovComment();
			
			$comment = $ovComment->GetCommentDetails($comment_id);
			
			if ($comment) {
				return "A Comment By " . $comment['username'];
			} else {
				return "A Comment By An Unknown User";
			}
		}
		
		/**
		 * Checks if the browser is on a mobile Device
		 * @return bool True if mobile, false if full
		 * @access public
		 * @since 3.2
		 */
		function IsMobileBrowser() {

			// Get the user agent

			$user_agent = $_SERVER['HTTP_USER_AGENT'];

			// Create an array of known mobile user agents
			// This list is from the 21 October 2010 WURFL File.
			// Most mobile devices send a pretty standard string that can be covered by
			// one of these.  I believe I have found all the agents (as of the date above)
			// that do not and have included them below.  If you use this function, you 
			// should periodically check your list against the WURFL file, available at:
			// http://wurfl.sourceforge.net/


			$mobile_agents = Array(


				"240x320",
				"acer",
				"acoon",
				"acs-",
				"abacho",
				"ahong",
				"airness",
				"alcatel",
				"amoi",	
				"android",
				"anywhereyougo.com",
				"applewebkit/525",
				"applewebkit/532",
				"asus",
				"audio",
				"au-mic",
				"avantogo",
				"becker",
				"benq",
				"bilbo",
				"bird",
				"blackberry",
				"blazer",
				"bleu",
				"cdm-",
				"compal",
				"coolpad",
				"danger",
				"dbtel",
				"dopod",
				"elaine",
				"eric",
				"etouch",
				"fly " ,
				"fly_",
				"fly-",
				"go.web",
				"goodaccess",
				"gradiente",
				"grundig",
				"haier",
				"hedy",
				"hitachi",
				"htc",
				"huawei",
				"hutchison",
				"inno",
				"ipaq",
				"ipod",
				"jbrowser",
				"kddi",
				"kgt",
				"kwc",
				"lenovo",
				"lg ",
				"lg2",
				"lg3",
				"lg4",
				"lg5",
				"lg7",
				"lg8",
				"lg9",
				"lg-",
				"lge-",
				"lge9",
				"longcos",
				"maemo",
				"mercator",
				"meridian",
				"micromax",
				"midp",
				"mini",
				"mitsu",
				"mmm",
				"mmp",
				"mobi",
				"mot-",
				"moto",
				"nec-",
				"netfront",
				"newgen",
				"nexian",
				"nf-browser",
				"nintendo",
				"nitro",
				"nokia",
				"nook",
				"novarra",
				"obigo",
				"palm",
				"panasonic",
				"pantech",
				"philips",
				"phone",
				"pg-",
				"playstation",
				"pocket",
				"pt-",
				"qc-",
				"qtek",
				"rover",
				"sagem",
				"sama",
				"samu",
				"sanyo",
				"samsung",
				"sch-",
				"scooter",
				"sec-",
				"sendo",
				"sgh-",
				"sharp",
				"siemens",
				"sie-",
				"softbank",
				"sony",
				"spice",
				"sprint",
				"spv",
				"symbian",
				"tablet",
				"talkabout",
				"tcl-",
				"teleca",
				"telit",
				"tianyu",
				"tim-",
				"toshiba",
				"tsm",
				"up.browser",
				"utec",
				"utstar",
				"verykool",
				"virgin",
				"vk-",
				"voda",
				"voxtel",
				"vx",
				"wap",
				"wellco",
				"wig browser",
				"wii",
				"windows ce",
				"wireless",
				"xda",
				"xde",
				"zte"
			);

			// Pre-set $is_mobile to false.

			$is_mobile = false;

			// Cycle through the list in $mobile_agents to see if any of them
			// appear in $user_agent.

			foreach ($mobile_agents as $device) {

				// Check each element in $mobile_agents to see if it appears in
				// $user_agent.  If it does, set $is_mobile to true.

				if (stristr($user_agent, $device)) {

					$is_mobile = true;

					// break out of the foreach, we don't need to test
					// any more once we get a true value.

					break;
				}
			}

			return $is_mobile;
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