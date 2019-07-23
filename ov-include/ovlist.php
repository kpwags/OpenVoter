<?php
	/**
	 * OpenVoter List Class
	 * Class dealing with lists for site
	 *
	 * @package OpenVoter
	 * @subpackage List
	 * @since 3.3
	 */
	class ovList
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
		 * Gets the lists of a user
		 * @param $user_id int PK of user
		 * @return array Array containing the lists
		 * @access public
		 * @since 3.3
		 */
		public function GetUserLists($user_id = false) 
		{
			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return false;
				}
			}

			// one last check to make sure user id is set
			if (!$user_id) {
				return false;
			}

			$user_id = mysql_escape_string($user_id);

			$query = "CALL GetUserLists(" . ovDBConnector::SiteID() . ", $user_id)";
			$result = ovDBConnector::Query($query);
			
			if(!$result)
			{
				// ERROR
				return false;
			}
			
			if ($result->num_rows > 0)
			{
				$lists = array();
				while ($row = $result->fetch_assoc())
				{
					$list['id'] = $row['id'];
					$list['name'] = stripslashes($row['name']);
					$list['unique_name'] = stripslashes($row['unique_name']);

					if ($row['is_private'] == 1) {
						$list['is_private'] = true;
					} else {
						$list['is_private'] = false;
					}
					
					array_push($lists, $list);
				}
				
				ovDBConnector::FreeResult();
				return $lists;
			}
			else
			{
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/**
		 * Gets the details of the list given the username and unique name
		 * @param $username string The username of the list's owner
		 * @param $list_unique_name string The URL-friendly name of the list
		 * @return array Array of list details or FALSE on error
		 * @access public
		 * @since 3.3
		 */
		public function GetListDetailsByUserAndName($username, $list_unique_name)
		{
			$username = mysql_escape_string($username);
			$list_unique_name = mysql_escape_string($list_unique_name);

			$query = "CALL GetListDetailsByUserAndName(" . ovDBConnector::SiteID() . ", '$username', '$list_unique_name')";
			$result = ovDBConnector::Query($query);

			if ($result && $result->num_rows > 0) {
				$row = $result->fetch_assoc();
				
				$list['id'] = $row['id'];
				$list['name'] = stripslashes($row['name']);
				$list['unique_name'] = stripslashes($row['unique_name']);
				$list['username'] = stripslashes($row['username']);
				
				if ($row['is_private'] == 1) {
					$list['is_private'] = true;
				} else {
					$list['is_private'] = false;
				}

				ovDBConnector::FreeResult();
				return $list;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/** 
		 * Checks to see if user already has a list with the given unique name
		 * @param $unique_name string Name to check
		 * @param $user_id int PK of user
		 * @param $list_id int PK of List if editing (to make sure the uniqueness doesn't check the list to edit)
		 * @return bool Exists = true, DNE = false
		 * @access public
		 * @since 3.3
		 */
		public function DoesListExistForUser($user_id, $unique_name, $list_id = false)
		{
			$list_id = mysql_escape_string($list_id);
			$user_id = mysql_escape_string($user_id);
			$unique_name = mysql_escape_string($unique_name);
			
			if (!$list_id) {
				$query = "CALL DoesListExist(" . ovDBConnector::SiteID() . ", $user_id, '$unique_name')";
			} else {
				$query = "CALL DoesListExistExcludeList(" . ovDBConnector::SiteID() . ",$list_id, $user_id, '$unique_name')";
			}

			$result = ovDBConnector::Query($query);

			if ($result) {
				$row = $result->fetch_assoc();
				$num_lists = $row['num_lists'];

				ovDBConnector::FreeResult();

				if ($num_lists > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/** 
		 * Adds a new list returning array for JSON
		 * @param $name string Name of List
		 * @param $is_private bool Flag to indicate whether list is public or private
		 * @param $user_id int PK of user
		 * @return array Array containing result
		 * @access public
		 * @since 3.3
		 */
		public function JSONAddNewList($name, $is_private, $user_id = false)
		{
			$unique_name = $this->ovUtilities->ConvertToUrl(mysql_escape_string($name));

			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return array('action' => 'add_list', 'status' => 'ERROR', 'errorMessage' => 'No User Logged In');
				}
			}

			if ($is_private) {
				$is_private = 1;
			} else {
				$is_private = 0;
			}

			if ($this->DoesListExistForUser($user_id, $unique_name)) {
				return array('action' => 'add_list', 'status' => 'ERROR', 'errorMessage' => 'A list already exists with this name');
			}

			$result = $this->AddList($name, $unique_name, $is_private, $user_id);

			if ($result) {
				return array('action' => 'add_list', 'status' => 'OK', 'listId' => $result['id'], 'listName' => $result['name'], 'listUrlName' => $result['unique_name']);
			} else {
				return array('action' => 'add_list', 'status' => 'ERROR', 'errorMessage' => 'An error might have occurred while adding the list, please refresh the page and try again.');
			}
		}

		/** 
		 * Adds a new list
		 * @param $name string Name of List
		 * @param $unique_name string Unique URL-friendly name for list
		 * @param $is_private bool Flag to indicate whether list is public or private
		 * @param $user_id int PK of user
		 * @return array Array containing list data
		 * @access protected
		 * @since 3.3
		 */
		protected function AddList($name, $unique_name, $is_private, $user_id) 
		{
			$name = mysql_escape_string($name);
			$unique_name = mysql_escape_string($unique_name);
			$is_private = mysql_escape_string($is_private);
			$user_id = mysql_escape_string($user_id);

			$query = "CALL AddList(" . ovDBConnector::SiteID() . ", '$name', '$unique_name', $is_private, $user_id)";
			$result = ovDBConnector::Query($query);

			if ($result && $result->num_rows > 0) {
				$row = $result->fetch_assoc();
				
				$list['id'] = $row['id'];
				$list['name'] = stripslashes($row['name']);
				$list['unique_name'] = stripslashes($row['unique_name']);
				
				if ($row['is_private'] == 1) {
					$list['is_private'] = true;
				} else {
					$list['is_private'] = false;
				}

				ovDBConnector::FreeResult();
				return $list;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/** 
		 * Edits a list
		 * @param $list_id int PK of List
		 * @param $name string Name of List
		 * @param $unique_name string Unique URL-friendly name for list
		 * @param $is_private bool Flag to indicate whether list is public or private
		 * @param $user_id int PK of user
		 * @return array Array containing list data
		 * @access protected
		 * @since 3.3
		 */
		protected function EditList($list_id, $name, $unique_name, $is_private, $user_id) 
		{
			$list_id = mysql_escape_string($list_id);
			$name = mysql_escape_string($name);
			$unique_name = mysql_escape_string($unique_name);
			$is_private = mysql_escape_string($is_private);
			$user_id = mysql_escape_string($user_id);

			$query = "CALL EditList(" . ovDBConnector::SiteID() . ", $list_id, '$name', '$unique_name', $is_private, $user_id)";
			$result = ovDBConnector::Query($query);

			if ($result && $result->num_rows > 0) {
				$row = $result->fetch_assoc();
				
				$list['id'] = $row['id'];
				$list['name'] = stripslashes($row['name']);
				$list['unique_name'] = stripslashes($row['unique_name']);
				
				if ($row['is_private'] == 1) {
					$list['is_private'] = true;
				} else {
					$list['is_private'] = false;
				}

				ovDBConnector::FreeResult();
				return $list;
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/** 
		 * Edits a list returning array for JSON
		 * @param $list_id int PK of List
		 * @param $name string Name of List
		 * @param $is_private bool Flag to indicate whether list is public or private
		 * @param $user_id int PK of user
		 * @return array Array containing result
		 * @access public
		 * @since 3.3
		 */
		public function JSONEditList($list_id, $name, $is_private, $user_id = false)
		{
			$unique_name = $this->ovUtilities->ConvertToUrl(mysql_escape_string($name));

			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return array('action' => 'edit_list', 'status' => 'ERROR', 'errorMessage' => 'No User Logged In');
				}
			}

			if ($is_private) {
				$is_private = 1;
			} else {
				$is_private = 0;
			}

			if ($this->DoesListExistForUser($user_id, $unique_name, $list_id)) {
				return array('action' => 'add_list', 'status' => 'ERROR', 'errorMessage' => 'A list already exists with this name');
			}

			if (!$this->IsUserListOwner($list_id, $user_id)) {
				return array('action' => 'add_list', 'status' => 'ERROR', 'errorMessage' => 'You do not have permission to edit this list');
			}

			$result = $this->EditList($list_id, $name, $unique_name, $is_private, $user_id);

			if ($result) {
				return array('action' => 'add_list', 'status' => 'OK', 'listId' => $result['id'], 'listName' => $result['name'], 'listUrlName' => $result['unique_name']);
			} else {
				return array('action' => 'add_list', 'status' => 'ERROR', 'errorMessage' => 'An error might have occurred while adding the list, please refresh the page and try again.');
			}
		}

		/** 
		 * Adds and removes users from lists
		 * @param $user_to_add int PK of user to add or remove
		 * @param $add_lists array[int] Array of List PK to add user to
		 * @param $delete_lists array[int] Array of List PK to remove user from
		 * @param $user_id int PK of user
		 * @return array Array containing results
		 * @access protected
		 * @since 3.3
		 */
		public function JSONAddRemoveUsersFromList($user_to_add, $add_lists, $delete_lists, $user_id = false)
		{
			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return array('action' => 'adjust_lists_for_user', 'status' => 'ERROR', 'errorMessage' => 'No User Logged In');
				}
			}	

			$not_owner = false;

			if (is_array($add_lists)) {
				foreach ($add_lists as $list_id) {
					if ($this->IsUserListOwner($list_id, $user_id)) {
						$this->AddUserToList($user_to_add, $list_id);
					} else {
						$not_owner = true;
					}
				}
			}

			if (is_array($delete_lists)) {
				foreach ($delete_lists as $list_id) {
					if ($this->IsUserListOwner($list_id, $user_id)) {
						$this->DeleteUserFromList($user_to_add, $list_id);
					} else {
						$not_owner = true;
					}
				}
			}

			if ($not_owner) {
				return array('action' => 'add_user_to_list', 'status' => 'WARNING', 'errorMessage' => 'At lease one list you were trying to add a user to is not owned by you');
			} else {
				return array('action' => 'add_user_to_list', 'status' => 'OK');
			}

		}

		/** 
		 * Deletes a list with a JSON array return
		 * @param $list_id int PK of list
		 * @param $user_id int PK of user
		 * @return array Array with status of AJAX call
		 * @access public
		 * @since 3.3
		 */
		public function JSONDeleteList($list_id, $user_id = false)
		{
			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return array('action' => 'delete_list', 'status' => 'ERROR', 'errorMessage' => 'No User Logged In');
				}
			}

			if (!$this->IsUserListOwner($list_id, $user_id)) {
				return array('action' => 'delete_list', 'status' => 'ERROR', 'errorMessage' => 'You do not have permission to delete this list');
			}

			$result = $this->DeleteList($list_id, $user_id);

			if ($result) {
				return array('action' => 'delete_list', 'status' => 'OK');
			} else {
				return array('action' => 'delete_list', 'status' => 'ERROR', 'errorMessage' => 'An error might have occurred while deleting the list, please refresh the page and try again.');
			}
		}

		/** 
		 * Deletes a list
		 * @param $list_id int PK of list
		 * @param $user_id int PK of user
		 * @return bool Result of query
		 * @access public
		 * @since 3.3
		 */
		public function DeleteList($list_id, $user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$list_id = mysql_escape_string($list_id);

			$query = "CALL DeleteList($list_id)";
			$result = ovDBConnector::ExecuteNonQuery($query);
			ovDBConnector::FreeResult();

			return $result;
		}

		 /** 
		 * Checks to see if user is in the specified list
		 * @param $list_id int PK of list
		 * @param $user_id int PK of user
		 * @return bool Owner = true / Not Owner = false
		 * @access public
		 * @since 3.3
		 */
		public function IsUserInList($list_id, $user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$list_id = mysql_escape_string($list_id);

			$query = "CALL IsUserInList(" . ovDBConnector::SiteID() . ", $user_id, $list_id)";
			$result = ovDBConnector::Query($query);

			if ($result) {
				$row = $result->fetch_assoc();
				$in_list = $row['in_list'];

				ovDBConnector::FreeResult();

				if ($in_list > 0) {
					return true;
				} else {
					return false;
				}
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/** 
		 * Checks to see if user is the owner of the specified list
		 * @param $list_id int PK of list
		 * @param $user_id int PK of user
		 * @return bool Owner = true / Not Owner = false
		 * @access public
		 * @since 3.3
		 */
		public function IsUserListOwner($list_id, $user_id)
		{
			$user_id = mysql_escape_string($user_id);
			$list_id = mysql_escape_string($list_id);

			$query = "CALL IsUserOwnerOfList(" . ovDBConnector::SiteID() . ", $list_id, $user_id)";
			$result = ovDBConnector::Query($query);

			if ($result) {
				$row = $result->fetch_assoc();
				$owner = $row['owner'];

				ovDBConnector::FreeResult();

				if ($owner == "YES") {
					return true;
				} else {
					return false;
				}
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/** 
		 * Adds a user to a list
		 * @param $user_to_add_id int PK of user to add
		 * @param $list_id int PK of list
		 * @param $user_id int PK of user
		 * @return false if error
		 * @access public
		 * @since 3.3
		 */
		public function AddUserToList($user_to_add_id, $list_id, $user_id = false)
		{
			$user_to_add_id = mysql_escape_string($user_to_add_id);
			$list_id = mysql_escape_string($list_id);

			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return false;
				}
			}				

			if ($this->IsUserListOwner($list_id, $user_id) && !$this->IsUserInList($list_id, $user_to_add_id)) {
				$query = "CALL AddUserToList(" . ovDBConnector::SiteID() . ", $list_id, $user_to_add_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}

		/** 
		 * Deletes a user from a list
		 * @param $user_to_remove_id int PK of user to delete
		 * @param $list_id int PK of list
		 * @param $user_id int PK of user
		 * @return false if error
		 * @access public
		 * @since 3.3
		 */
		public function DeleteUserFromList($user_to_remove_id, $list_id, $user_id = false)
		{
			$user_to_remove_id = mysql_escape_string($user_to_remove_id);
			$list_id = mysql_escape_string($list_id);

			if (!$user_id) {
				if ($this->ovUserSecurity->IsUserLoggedIn()) {
					$user_id = $this->ovUserSecurity->LoggedInUserID();
				} else {
					// user id not set and no user logged in, return false
					return false;
				}
			}

			if ($this->IsUserListOwner($list_id, $user_id)) {
				$query = "CALL DeleteUserFromList(" . ovDBConnector::SiteID() . ", $list_id, $user_to_remove_id)";
				ovDBConnector::ExecuteNonQuery($query);
				ovDBConnector::FreeResult();
			}
		}

		/** 
		 * Gets the members in a list
		 * @param $list_id int PK of list
		 * @return false if error
		 * @access public
		 * @since 3.3
		 */
		public function GetMembersInList($list_id)
		{
			$list_id = mysql_escape_string($list_id);

			$query = "CALL GetMembersInList(" . ovDBConnector::SiteID() . ", $list_id)";
			$result = ovDBConnector::Query($query);

			if ($result) {
				if ($result->num_rows > 0) {
					$users = array();
					while ($row = $result->fetch_assoc())
					{
						$user['id'] = $row['id'];
						$user['username'] = stripslashes($row['username']);
						$user['avatar'] = stripslashes($row['avatar']);
						
						array_push($users, $user);
					}
					
					ovDBConnector::FreeResult();
					return $users;
				} else {
					// no users in list
					ovDBConnector::FreeResult();
					return false;
				}
			} else {
				ovDBConnector::FreeResult();
				return false;
			}
		}

		/**
		 * Gets the number of submissions in a given list
		 * @param $list_id int The PK of list
		 * @param $type string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @return int Submission count
		 * @access public
		 * @since 3.3
		 */
		public function GetCountForCategory($list_id, $type)
		{
			$list_id = mysql_escape_string($list_id);
			$type = mysql_escape_string($type);
			
			if ($type == "all") {
				$type = "";
			}
			
			$query = "CALL GetSubmissionCountForList(" . ovDBConnector::SiteID() . ", $list_id, '$type')";
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
		 * Gets submissions for given list
		 * @param $list_id int PK of list
		 * @param string The type of submissions to count (STORY, PHOTO, VIDEO, PODCAST, SELF), pass in an empty string for ALL
		 * @param $page_number int The current page number
		 * @return array|false Array of submission data or false if error or no submissions
		 * @access public
		 * @since 3.3
		 */
		public function GetListSubmissions($list_id, $submission_type, $page_number = 1)
		{
			$subtype = "";
			switch (strtolower($submission_type)) {
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
			
			$num_subs = $this->GetCountForCategory($list_id, $submission_type);
			$limits = $this->ovUtilities->CalculateLimits($page_number, $num_subs);
			
			$offset = $limits[0];
			$limit = $limits[1];
			
			$list_id = mysql_escape_string($list_id);
			$submission_type = mysql_escape_string($submission_type);
			$offset = mysql_escape_string($offset);
			$limit = mysql_escape_string($limit);
			
			$query = "CALL GetSubmissionsForList(" . ovDBConnector::SiteID() . ", $list_id, '$subtype', $offset, $limit)";
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
		 * User Security Class Object
		 * @access protected
		 * @var ovUserSecurity
		 * @since 3.3
		 */
		protected $ovUserSecurity;
		
		/**
		 * Utilities Class Object
		 * @access protected
		 * @var ovUtilities
		 * @since 3.3
		 */
		protected $ovUtilities;
	}
?>