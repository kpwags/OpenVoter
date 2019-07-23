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
	 * Class dealing with the connection to the MySQL DB
	 *
	 * @package OpenVoter
	 * @subpackage DBConnector
	 * @since 3.0
	 */
	class ovDBConnector
	{
		private function __construct() { }
		private function __destruct() {	}
		private function __clone() { }

		/**
		 * Gets the site ID
		 * @access public
		 * @return int SITE ID
		 * @since 3.0
		 */
		public static function SiteID() { return SITE_ID; }
		
		/**
		 * Accessor to mysqli object
		 * @access public
		 * @return mysqli MySQLi Object
		 * @since 3.0
		 */
		public static function DB()
		{
			if (self::$instance == null || !(self::$instance instanceOf mysqli)) {  
				self::$instance = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
				
				if(self::$instance->connect_error){  
					//throw new Exception('Error MySQL: ' . self::$instance->connect_error);  
					header("Location: /db-failure");
					exit();
				}  
			} 
			
			return self::$instance;
		}
		
		/**
		 * Executes a DB Command that does not return any rows (i.e. INSERT, UPDATE)
		 * @access public
		 * @param string $query SQL Query to execute
		 * @return bool true if successful, false if not
		 * @since 3.0
		 */
		public static function ExecuteNonQuery($query)
		{
			if (!$result = (self::DB()->query($query)))
			{
				// error with query
				printf("Error Message: %s\nQuery: %s", self::DB()->error, $query);
				return false;
			}
			else
			{
				return true;
			}
			
		}
		
		/**
		 * Executes a DB Command and returns the rows
		 * @access public
		 * @param string $query SQL Query to execute
		 * @return mysqli_result|false MySQL Improved Result Object with reuslt or false if error
		 * @since 3.0
		 */
		public static function Query($query)
		{
			if (!$result = (self::DB()->query($query)))	{				
				// error with query
				printf("Error Message: %s\nQuery: %s",self::DB()->error, $query);
				return false;
			}
			
			return $result;
		}
		
		/**
		 * Frees the results from the SQL Result Set since Stored Procedures are used.
		 * @access public
		 * @since 3.0
		 */
		public static function FreeResult()
		{
		    while( self::DB()->more_results() ) 
		    { 
		        if(self::DB()->next_result()) 
		        { 
		            $result = self::DB()->use_result(); 
		            unset($result); 
		        } 
		    } 
		}
		
		/**
		 * MySQLi Object
		 * @access private
		 * @var mysqli
		 * @since 3.0
		 */
		private static $instance;
	}
?>