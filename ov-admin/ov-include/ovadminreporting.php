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
	 * OpenVoter Admin Reporting Class
	 * Class dealing with handling reports for admins
	 *
	 * @package OpenVoter
	 * @subpackage AdminReporting
	 * @since 3.0
	 */
	class ovAdminReporting
	{
		function __construct()
		{
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			require_once 'ovadminsecurity.php';
			$this->ovAdminSecurity = new ovAdminSecurity();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets The Number of Reports
		 * @access public
		 * @param string $type Type of reports (submission, comment, user) (optional, defaults to NULL to signify ALL)
		 * @return int Report Count
		 * @since 3.0
		 */
		public function GetReportCount($type = "ALL")
		{
			$report_count = 0;
			
			$type = mysql_escape_string($type);
			
			$query = "CALL GetReportCount(" . ovDBConnector::SiteID() . ", '$type')";
			$result = ovDBConnector::Query($query);

			if (!$result)
			{
				// ERROR
				$report_count = 0;
			}
			
			if ($result->num_rows > 0)
			{			
				$row = $result->fetch_assoc();
				$report_count = $row['num_reports'];
			}
			else
			{
				$report_count = 0;
			}
				
			ovDBConnector::FreeResult();
			
			return $report_count;
		}
		
		/**
		 * Gets the reports for submissions
		 * @access public
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetSubmissionReports()
		{
			$query = "CALL GetSubmissionReports(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$reports = array();
				while ($row = $result->fetch_assoc()) {
					$report['id'] = $row['id'];
					$report['title'] = stripslashes($row['title']);

					array_push($reports, $report);
				}
				
				ovDBConnector::FreeResult();
				return $reports;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the reports for comments
		 * @access public
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetCommentReports()
		{
			$query = "CALL GetCommentReports(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$reports = array();
				while ($row = $result->fetch_assoc()) {
					$report['id'] = $row['id'];
					$report['title'] = stripslashes($row['title']);
					$report['username'] = stripslashes($row['username']);

					array_push($reports, $report);
				}
				
				ovDBConnector::FreeResult();
				return $reports;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the reports for users
		 * @access public
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetUserReports()
		{
			$query = "CALL GetUserReports(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$reports = array();
				while ($row = $result->fetch_assoc()) {
					$report['id'] = $row['id'];
					$report['username'] = stripslashes($row['username']);

					array_push($reports, $report);
				}
				
				ovDBConnector::FreeResult();
				return $reports;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the details for a submission report
		 * @access public
		 * @param int $report_object_id The PK of the report_object
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetSubmissionReportDetails($report_id)
		{
			$report_id = mysql_escape_string($report_id);
			$query = "CALL GetSubmissionReportDetails(" . ovDBConnector::SiteID() . ", $report_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				
				require_once 'ovsubmission.php';
				$ovSubmission = new ovSubmission();
				
				$report['id'] = $row['id'];
				$report['submission_id'] = $row['submission_id'];
				$report['submission_title'] = stripslashes($row['title']);
				
				$report['submission_summary'] = $this->ovUtilities->FormatBody($row['title'], true);
				$report['submission_user_id'] = $row['user_id'];
				$report['submission_user'] = stripslashes($row['username']);
				$report['submission_url'] = $row['url'];
				$report['page_url'] = "/" . strtolower($row['type']) . "/" . $row['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']);
				
				ovDBConnector::FreeResult();
				
				
				
				$report['reports'] = $this->GetReports($report['id']);
				ovDBConnector::FreeResult();
				$report['submission_score'] = $ovSubmission->GetSubmissionScore($report['submission_id']);
				ovDBConnector::FreeResult();
				
				return $report;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the details for a comment report
		 * @access public
		 * @param int $report_object_id The PK of the report_object
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetCommentReportDetails($report_id)
		{
			$report_id = mysql_escape_string($report_id);
			$query = "CALL GetCommentReportDetails($report_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				
				$report['id'] = $row['id'];
				$report['comment_id'] = $row['comment_id'];
				$report['comment_body'] = $this->ovUtilities->FormatBody($row['body']);
				$report['submission_id'] = $row['submission_id'];
				$report['submission_title'] = stripslashes($row['title']);
				$report['comment_user_id'] = $row['user_id'];
				$report['comment_user'] = stripslashes($row['username']);
				$report['page_url'] = "/" . strtolower($row['type']) . "/" . $row['submission_id'] . "/" . $this->ovUtilities->ConvertToUrl($row['title']);
				
				ovDBConnector::FreeResult();
				
				$report['reports'] = $this->GetReports($report['id']);
				
				return $report;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the details for a user report
		 * @access public
		 * @param int $report_object_id The PK of the report_object
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetUserReportDetails($report_id)
		{
			$report_id = mysql_escape_string($report_id);
			$query = "CALL GetUserReportDetails($report_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$row = $result->fetch_assoc();
				
				$report['id'] = $row['id'];
				$report['user_id'] = $row['user_id'];
				$report['username'] = stripslashes($row['username']);
				$report['email'] = stripslashes($row['email']);
				$report['details'] = $this->ovUtilities->FormatBody($row['details']);
				$report['website'] = stripslashes($row['website']);
				$report['avatar'] = stripslashes($row['avatar']);
				
				ovDBConnector::FreeResult();
				
				$report['reports'] = $this->GetReports($report['id']);
				
				return $report;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
		
		/**
		 * Gets the user reports for a report
		 * @access public
		 * @param int $report_object_id The PK of the report_object
		 * @return array|false Returns an array of reports or false on error or none
		 * @since 3.0
		 */
		public function GetReports($report_object_id)
		{
			$report_object_id = mysql_escape_string($report_object_id);
			$query = "CALL GetReports(" . ovDBConnector::SiteID() . ", $report_object_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$reports = array();
				while ($row = $result->fetch_assoc()) {
					$report['reason'] = $row['reason'];
					$report['details'] = $this->ovUtilities->FormatBody($row['details']);
					$report['username'] = stripslashes($row['username']);

					array_push($reports, $report);
				}
				
				ovDBConnector::FreeResult();
				return $reports;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}
	
		/**
		 * Ignores all reports for a reported object
		 * @access public
		 * @param int $report_id The PK of the report_object
		 * @since 3.0
		 */
		public function IgnoreReport($report_id)
		{
			$report_id = mysql_escape_string($report_id);
						
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessContent()) {
				$query = "CALL IgnoreReport(" . ovDBConnector::SiteID() . ", $report_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
	
		protected $ovAdminSecurity;
		protected $ovUtilities;
	}
?>