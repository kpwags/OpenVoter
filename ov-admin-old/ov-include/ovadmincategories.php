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
	 * OpenVoter Admin Categories Class
	 * Class dealing with handling categories
	 *
	 * @package OpenVoter
	 * @subpackage AdminCateogries
	 * @since 3.0
	 */
	class ovAdminCategories
	{
		function __construct()
		{
			require_once 'ovadminsecurity.php';
			$this->ovAdminSecurity = new ovAdminSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * Gets the parent categories
		 * @access public
		 * @return array|false Returns an array of the categories or false on error, no bans
		 * @since 3.0
		 */
		public function GetParentCategories()
		{
			$query = "CALL AdminGetCategories(" . ovDBConnector::SiteID() . ")";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$categories = array();
				while ($row = $result->fetch_assoc()) {
					$category['id'] = $row['id'];
					$category['url_name'] = stripslashes($row['url_name']);
					$category['name'] = stripslashes($row['name']);
					$category['num_subcategories'] = $row['num_subcategories'];
					$category['sort_order'] = $row['sort_order'];

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
		 * Gets the subcategories for a given category
		 * @access public
		 * @return array|false Returns an array of the categories or false on error, no bans
		 * @since 3.0
		 */
		public function GetChildCategories($parent_category_id)
		{
			$parent_category_id = mysql_escape_string($parent_category_id);
			$query = "CALL AdminGetSubCategories(" . ovDBConnector::SiteID() . ", $parent_category_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{	
				$categories = array();
				while ($row = $result->fetch_assoc()) {
					$category['id'] = $row['id'];
					$category['url_name'] = stripslashes($row['url_name']);
					$category['name'] = stripslashes($row['name']);
					$category['sort_order'] = $row['sort_order'];

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
		 * Checks to see if a URL name is available
		 * @access public
		 * @param string $url_name The URL to check
		 * @return bool Availability
		 * @since 3.0
		 */
		public function IsCategoryUrlAvailable($url_name)
		{
			$url_name = mysql_escape_string($url_name);
			
			$query = "CALL IsCategoryUrlAvailable(" . ovDBConnector::SiteID() . ", '$url_name')";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return false;
			}

			if ($result->num_rows > 0) {
				ovDBConnector::FreeResult();
				return false;
			} else {
				ovDBConnector::FreeResult();
				return true;
			}
		}
		
		/**
		 * Adds a category
		 * @access public
		 * @param string $name The name of the category
		 * @param string $url_name The URL name of the category
		 * @param int $sort_order The sort order for the category (optional)
		 * @param int $parent_id The PK of the parent category (optional)
		 * @since 3.0
		 */
		public function AddCategory($name, $url_name, $sort_order = 0, $parent_id = false)
		{
			$name = mysql_escape_string($name);
			$url_name = mysql_escape_string($url_name);
			$sort_order = mysql_escape_string($sort_order);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences() && $this->IsCategoryUrlAvailable($url_name)) {			
				if ($parent_id) {
					// child category
					$parent_id = mysql_escape_string($parent_id);
					$query = "CALL AddChildCategory(" . ovDBConnector::SiteID() . ", '$name', '$url_name', $sort_order, $parent_id)";
				} else {
					// parent category
					$query = "CALL AddParentCategory(" . ovDBConnector::SiteID() . ", '$name', '$url_name', $sort_order)";
				}
					
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Edits a category
		 * @access public
		 * @param int $category_id The PK of the category
		 * @param string $name The name of the category
		 * @param string $url_name The URL name of the category
		 * @param int $sort_order The sort order for the category (optional)
		 * @since 3.0
		 */
		public function EditCategory($category_id, $name, $url_name, $sort_order = 0)
		{
			$category_id = mysql_escape_string($category_id);
			$name = mysql_escape_string($name);
			$url_name = mysql_escape_string($url_name);
			$sort_order = mysql_escape_string($sort_order);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {			
				$query = "CALL EditCategory(" . ovDBConnector::SiteID() . ", $category_id, '$name', '$url_name', $sort_order)";	
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Deletes a category
		 * @access public
		 * @param int $category_id The PK of the category
		 * @since 3.0
		 */
		public function DeleteCategory($category_id)
		{
			$category_id = mysql_escape_string($category_id);
			if ($this->ovAdminSecurity->IsAdminLoggedIn() && $this->ovAdminSecurity->CanAccessPreferences()) {			
				$query = "CALL DeleteCategory(" . ovDBConnector::SiteID() . ", $category_id)";					
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}
		
		/**
		 * Gets the name of a category
		 * @access public
		 * @param int $category_id The PK of the category
		 * @return string Name of the category
		 * @since 3.0
		 */
		public function GetCategoryName($category_id)
		{
			$category_id = mysql_escape_string($category_id);
			
			$query = "CALL GetCategoryNameFromId(" . ovDBConnector::SiteID() . ", $category_id)";
			$result = ovDBConnector::Query($query);

			if (!$result) {
				// ERROR
				return "Unknown";
			}

			if ($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$category_name = stripslashes($row['name']);
				ovDBConnector::FreeResult();
				return $category_name;
			} else {
				ovDBConnector::FreeResult();
				return "Unknown";
			}
		}
				
		protected $ovAdminSecurity;
		protected $ovUtilities;
	}
?>