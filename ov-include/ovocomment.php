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
	 * OpenVoter Comment Object Class
	 * Class holding the data from a comment
	 *
	 * @package OpenVoter
	 * @subpackage oComment
	 * @since 3.0
	 */
	class ovoComment
	{
		/**
		 * Instantiates the object
		 * @param array $comment_row Array of data from the SQL database
		 * @access public
		 * @since 3.1
		 */
		function __construct($comment_row)
		{			
			require_once 'ovusersecurity.php';
			$this->ovUserSecurity = new ovUserSecurity();
			
			require_once 'ovutilities.php';
			$this->ovUtilities = new ovUtilities();
			
			require_once 'ovcomment.php';
			$this->ovComment = new ovComment();
			
			require_once 'ovsettings.php';
			$this->ovSettings = new ovSettings();
			
			$this->LoadCommentData($comment_row);
		}
		
		function __destruct() 
		{
			
		}
		
		/**
		 * @return int The PK of the Comment
		 * @access public
		 * @since 3.0
		 */
		public function ID() { return $this->_id; }
		
		/**
		 * @return string he Body of the Comment
		 * @access public
		 * @since 3.0
		 */
		public function Body() { return $this->_body; }
		
		/**
		 * @return int The Score of the Comment
		 * @access public
		 * @since 3.0
		 */
		public function Score() { return $this->_score; }
		
		/**
		 * @return string The Date of the Comment
		 * @access public
		 * @since 3.0
		 */
		public function Date() { return $this->_date; }
		
		/**
		 * @return int The User who posted the comment's PK
		 * @access public
		 * @since 3.0
		 */
		public function UserID() { return $this->_user_id; }
		
		/**
		 * @return string The User who posted the comment's username
		 * @access public
		 * @since 3.0
		 */
		public function Username() { return $this->_username; }
		
		/**
		 * @return string The user who posted the comment's avatar
		 * @access public
		 * @since 3.0
		 */
		public function Avatar() { return $this->_avatar; }
		
		/**
		 * @return bool Whether the comment is still active
		 * @access public
		 * @since 3.0
		 */
		public function Active() { return $this->_active; }
		
		/**
		 * @return bool Whether the user who posted the comment is blocked by the user viewing
		 * @access public
		 * @since 3.1
		 */
		public function UserBlocked() { return $this->_user_blocked; }
		
		/**
		 * @return bool Whether the comment has been edited since posting
		 * @access public
		 * @since 3.1
		 */
		public function Edited() { return $this->_edited; }
		
		/**
		 * @return bool Whether the comment is still modifiable
		 * @access public
		 * @since 3.1
		 */
		public function Modifiable() { return $this->_modifiable; }
		
		/**
		 * @return bool Whether the comment if deleted was deleted by the user
		 * @access public
		 * @since 3.1
		 */
		public function DeletedByUser() { return $this->_deleted_by_user; }
		
		/**
		 * @param array Array of data from the SQL database
		 * @access public
		 * @since 3.0
		 */
		protected function LoadCommentData($comment_row)
		{
			$this->_id = $comment_row['id'];
			$this->_body = $this->ovUtilities->parseURL($comment_row['body']);
			$this->_score = $this->ovComment->GetCommentScore($this->_id);
			
			if ($this->ovSettings->CommentModifyTime() == -1) {
				$this->_modifiable = true;
			} elseif ($this->ovSettings->CommentModifyTime() == 0) {
				$this->_modifiable = false;
			} else {
				$to_time = time();
				$from_time = strtotime($comment_row['date']);
				$mins = round(abs($to_time - $from_time) / 60,2);
				
				if ($mins > $this->ovSettings->CommentModifyTime()) {
					$this->_modifiable = false;
				} else {
					$this->_modifiable = true;
				}
			}
			
			$this->_date = $this->ovUtilities->CalculateTimeAgo($comment_row['date']);
			$this->_user_id = $comment_row['user_id'];
			$this->_username = $comment_row['username'];
			$this->_avatar = $comment_row['avatar'];
			
			if ($comment_row['active'] == 1) {
				$this->_active = true;
			} else {
				$this->_active = false;
			}
			
			if ($comment_row['is_blocked'] == 0) {
				$this->_user_blocked = false;
			} else {
				$this->_user_blocked = true;
			}
			
			if ($comment_row['edited'] == 1) {
				$this->_edited = true;
			} else {
				$this->_edited = false;
			}
			
			if ($comment_row['deleted_by_user'] == 1) {
				$this->_deleted_by_user = true;
			} else {
				$this->_deleted_by_user = false;
			}
		}
		
		/**
		 * Votes on a comment
		 * @param int $direction Is the comment being upvoted or downvoted (1 or -1)
		 * @access public
		 * @since 3.0
		 */
		public function Vote($direction)
		{
			if ($this->ovUserSecurity->IsUserLoggedIn()) {
				$this->ovComment->AddVote($this->_id, $direction);
				return true;
			} else {
				return false;
			}
		}
		
		/**
		 * PK of Comment
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_id;
		
		/**
		 * Body of Comment
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_body;
		
		/**
		 * Score of Comment
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_score;
		
		/**
		 * Date of Comment
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_date;
		
		/**
		 * PK of User who posted comment
		 * @access protected
		 * @var int
		 * @since 3.0
		 */
		protected $_user_id;
		
		/**
		 * Username of user who posted comment
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_username;
		
		/**
		 * Avatar of user who posted Comment
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_avatar;
		
		/**
		 * Comment Active Flag
		 * @access protected
		 * @var bool
		 * @since 3.1
		 */
		protected $_active;
		
		/**
		 * User Blocked By Logged In User Flag
		 * @access protected
		 * @var bool
		 * @since 3.1
		 */
		protected $_user_blocked;
		
		/**
		 * Comment edited flag
		 * @access protected
		 * @var bool
		 * @since 3.1
		 */
		protected $_edited;
		
		/**
		 * Comment Modifiable Flag
		 * @access protected
		 * @var bool
		 * @since 3.1
		 */
		protected $_modifiable;
		
		/**
		 * Comment deleted by user flag
		 * @access protected
		 * @var bool
		 * @since 3.1
		 */
		protected $_deleted_by_user;
		
		/**
		 * Comment Class Object
		 * @access protected
		 * @var ovComment
		 * @since 3.0
		 */
		protected $ovComment;
		
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