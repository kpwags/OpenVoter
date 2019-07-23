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
	 * OpenVoter MetaTags Class
	 * Class dealing with getting the title and description from the remote site
	 *
	 * @package OpenVoter
	 * @subpackage MetaTags
	 * @since 3.1
	 */
	class ovMetaTags 
	{
		var $title = '';
		var $description = '';
		var $error_invalid_url = 'Error';   //message to display if the url can't be loaded
		var $error_no_title = '';	//display if title is not set
		var $error_no_desc = '';	//display if description is not set

		/**
		 * Gets a the metadata from a URL
		 * @param string $url The URL
		 * @access public
		 * @since 3.1
		 */
		function getmetadata($url)
		{
			//make sure URL is formated correctly
			if (strstr($url, 'http://') == false){
				$url = 'http://'.$url;
			}
			//get file contents
			$d = file_get_contents($url);
			//display error if site can't be loaded
			if (!$d) { 
				echo $this->error_invalid_url;
				exit();
			}
			//shorten string
			$line = substr($d, 0, 3000);
				//remove linebreaks from the string
				$linebreaks   = array("\r\n", "\n", "\r");
				//Processes \r\n's first so they aren't converted twice.
				$line = str_replace($linebreaks, '', $line);
				// This only works if the title and its tags are on one line 
				if (eregi ("<title>(.*)</title>", $line, $out)) {
					$this->title = $out[1];
				}
			//get description
			$desc = get_meta_tags($url);
			if (isset($desc['description'])) {
				$this->description = $desc['description'];
			}

		}
		
		/**
		 * Gets the title from the metadata collected
		 * @return string Title of the submission
		 * @access public
		 * @since 3.1
		 */
		function GetTitle() 
		{
			if(!$this->title){
				return $this->error_no_title;
			}else{
				return $this->title;	
			}	
		}
		
		/**
		 * Gets the description from the metadata collected
		 * @return string Description of the submission
		 * @access public
		 * @since 3.1
		 */
		function GetDescription()
		{
			if(!$this->description) {
				return $this->error_no_desc;
			} else {
				return htmlspecialchars_decode($this->description);
			}
		}
	}
?>