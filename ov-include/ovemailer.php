<?php
//	ini_set("include_path", ".:./:./include:./../include:./../../include:./ov-admin/include:./../ov-admin/include:./../usercontrols:./usercontrols:./ov-admin/usercontrols:./../ov-admin/usercontrols:./themes:./../themes");
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
	 * OpenVoter Emailer Class
	 * Class dealing with sending out emails
	 *
	 * @package OpenVoter
	 * @subpackage Emailer
	 * @since 3.0
	 */
	class ovEmailer
	{
		/**
		 * Constructor
		 * @access public
		 * @param string $email_address Source Email Address - where the email will be sent from (optional)
		 * @param string $source Source Name (optional)
		 * @since 3.0
		 */
		function __construct($emailAddress = false, $source = false)
		{
			if ($emailAddress) {
				$this->_emailAddress = $emailAddress;
			}
			
			if ($source) {
				$this->_source = $source;
			}
		}
		
		/**
		 * Sets the source email address
		 * @access public
		 * @param string $email Email address to be set as the source email address
		 * @since 3.0
		 */
		public function SetFromEmail($email) { $this->_emailAddress = $email; }
		
		/**
		 * Sets the source name
		 * @access public
		 * @param string $source Name of the source
		 * @since 3.0
		 */
		public function SetSource($source) { $this->_source = $source; }

		/**
		 * Sends the email
		 * @access public
		 * @param string $sendTo Email address to send to
		 * @param string $message Body of the message
		 * @param string $subject Subject of the message
		 * @since 3.0
		 */
		public function SendEmail($sendTo, $message, $subject = "No Subject")
		{
			$emailExp = '/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/' ;
			if (!preg_match($emailExp, $sendTo)) {
				// invalid email address
				throw new Exception("Invalid E-Mail Address");
				return false;
			} else {
				$header = "From: ". $this->_source . " <" . $this->_emailAddress . ">\r\n" ;
				$header .= "MIME-Version: 1.0\r\n";
				$header .= "Content-Type: text/html; charset=UTF-8\r\n";
				ini_set('sendmail_from', $this->_emailAddress);
				mail($sendTo, $subject, $message, $header);
				return true;
			}
		}

		/**
		 * Source email address - where the email will be sent from
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_emailAddress;
		
		/**
		 * Source name - where the email will be sent from
		 * @access protected
		 * @var string
		 * @since 3.0
		 */
		protected $_source;
	}
?>