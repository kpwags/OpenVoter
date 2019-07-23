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
	 * OpenVoter Utilities Class
	 * Class handling basic utilities
	 *
	 * @package OpenVoter
	 * @subpackage Utilities
	 * @since 3.0
	 */
	class ovUtilities
	{
		public function ovUtilities()
		{

		}

		/**
		 * Converts text into URL-Safe Text
		 * @param string $str The text to convert
		 * @param string $replacement_character The character used to replace (defaults to the dash '-')
		 * @return string
		 * @access public
		 * @since 3.0
		 */
		public function ConvertToUrl($str, $replacement_character = '-')
		{
			$str = strtolower(trim($str));
			$str = preg_replace('/[^a-z0-9-]/', $replacement_character, $str);
			$str = preg_replace('/-+/', $replacement_character, $str);
			return $str;
		}

		/**
		 * Returns the base domain of a given URL
		 * @param string $url The full URL
		 * @return string
		 * @access public
		 * @since 3.0
		 */
		public function GetDomain($url)
		{
			$nowww = ereg_replace('www\.','',$url);
			$domain = parse_url($nowww);
			if(!empty($domain["host"])) {
				return $domain["host"];
			} else {
				return $domain["path"];
			}
		}

		/**
		 * Returns the date in "time ago" format
		 * @param datetime $date date to calculate
		 * @access public
		 * @since 3.0
		 */
		public function CalculateTimeAgo($date, $granularity=1)
		{
			// DISPLAYS COMMENT POST TIME AS "1 year, 1 week ago" or "5 minutes, 7 seconds ago", etc...
			$date = strtotime($date);
			$difference = time() - $date;
			$retval = "";
			$periods = array('decade' => 315360000,
				'year' => 31536000,
				'month' => 2628000,
				'week' => 604800,
				'day' => 86400,
				'hour' => 3600,
				'minute' => 60,
				'second' => 1);

			foreach ($periods as $key => $value) {
				if ($difference >= $value) {
					$time = floor($difference/$value);
					$difference %= $value;
					$retval .= ($retval ? ' ' : '').$time.' ';
					$retval .= (($time > 1) ? $key.'s' : $key);
					$granularity--;
				}
				if ($granularity == '0') { break; }
			}

			if ($retval == "")
				$retval = "just a few seconds";

			return "about " . $retval . " ago";
		}

		/**
		 * Takes a body and strips the backslashes and inserts the newlines
		 * @param string $body the unformatted body text
		 * @param bool $enableLineBreaks flag to use line breaks
		 * @return string the formatted body text
		 * @access public
		 * @since 3.0
		 */
		public function FormatBody($body, $enableLineBreaks = true)
		{
			if ($enableLineBreaks) {
				$formattedBody =  nl2br(htmlspecialchars($body));
			} else {
				$formattedBody =  str_replace("\n", "", $body);
			}

			return stripslashes($formattedBody);
		}

		/**
		 * Adds HTML hyperlinks to plain text hyperlinks in text
		 * @param string $text Text to be parsed
		 * @return string
		 * @access public
		 * @since 3.0
		 */
		public function parseURL($text)
		{
			$pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
			   $callback = create_function('$matches', '
			       $url       = array_shift($matches);
			       $url_parts = parse_url($url);

			       $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
			       $text = preg_replace("/^www./", "", $text);

			       $last = -(strlen(strrchr($text, "/"))) + 1;
			       if ($last < 0) {
			           $text = substr($text, 0, $last) . "&hellip;";
			       }

			       return sprintf(\'<a rel="nowfollow" href="%s">%s</a>\', $url, $text);
			   ');

			   return preg_replace_callback($pattern, $callback, $text);
		}

		/**
		 * Gets the index of the first parameter in the URL
		 * @param string $page_title title of the .php file
		 * @return int
		 * @access public
		 * @since 3.0
		 */
		public function GetFirstParameter($page_title)
		{
			$page = explode("/", $_SERVER['REQUEST_URI']) ;

			for($i = 0; $i < count($page); $i++)
			{
				if($page[$i] == $page_title)
					return $i+1 ;
			}

			return false;
		}
		
		/**
		 * Calculate the pagination limits
		 * @param int $page Current Page
		 * @param int $count Total number of objects
		 * @param int $pagination_override (OPTIONAL) Override the number of items per page
		 * @return array Array containing offset, limit, and last page
		 * @access public
		 * @since 3.0
		 */
		public function CalculateLimits($page, $count, $pagination_override = false)
		{
			require_once 'ovsettings.php';
			$ovSettings = new ovSettings();
			
			if ($pagination_override) {
				$pagination = $pagination_override;
			} else {
				$pagination = $ovSettings->Pagination();
			}
			
			$last_page = ceil($count / $pagination) ;
			if ($last_page < 1) {
				$last_page = 1;
			}

			/* Checks to make sure $page is within range */
			$page = (int)$page ;
			if($page < 1) {
				$page = 1 ;
			} elseif ($page > $last_page) {
				$page = $last_page ;
			}

			$limit = ($page - 1) * $pagination;
			
			return array($limit, $pagination, $last_page);
		}

		/**
		 * Prints out the pagination line
		 * @param string $base_url the URL up to the page number
		 * @param int $current_page Current Page
		 * @param int $last_page Last Page
		 * @access public
		 * @since 3.0
		 */
		public function PrintPaginationRow($base_url, $current_page, $last_page)
		{
			echo "<div class=\"pagination-bar\">" ;

				$prev_page = $current_page - 1 ;
				$next_page = $current_page + 1 ;

				if($current_page != 1)
				{
					echo "<a href=\"" . $base_url . $prev_page . "\" title=\"Page $prev_page\">Previous</a>&nbsp;&nbsp;" ;
				}

				if($current_page > 6)
				{
					echo "<a href=\"" . $base_url . "1\" title=\"Page 1\">1</a>&nbsp;&nbsp;...&nbsp;&nbsp;" ;
				}
				if($current_page == 6)
				{
					echo "<a href=\"" . $base_url . "1\" title=\"Page 1\">1</a>&nbsp;&nbsp;" ;
				}

				$start_point = $current_page - 4 ;

				while($start_point < $current_page)
				{
					if($start_point > 0)
					{
						echo "<a href=\"" . $base_url . $start_point . "\" title=\"Page $start_point\">$start_point</a>&nbsp;&nbsp;" ;
					}

					$start_point++ ;
				}

				echo "<span class=\"current-page\">$current_page</span>&nbsp;&nbsp;" ;

				$end_point = $current_page + 5 ;
				$start_point = $current_page + 1 ;

				while($start_point < $end_point)
				{
					if($start_point <= $last_page)
					{
						echo "<a href=\"" . $base_url . $start_point . "\" title=\"Page $start_point\">$start_point</a>&nbsp;&nbsp;" ;
					}

					$start_point++ ;
				}

				if($start_point < $last_page)
				{
					echo "...&nbsp;&nbsp;<a href=\"" . $base_url . $last_page . "\" title=\"Page $last_page\">$last_page</a>&nbsp;&nbsp;" ;
				}
				if($start_point == $last_page)
				{
					echo "<a href=\"" . $base_url . $last_page . "\" title=\"Page $last_page\">$last_page</a>&nbsp;&nbsp;" ;
				}

				if($current_page != $last_page)
				{
					echo "<a href=\"" . $base_url . $next_page . "\" title=\"Page $next_page\">Next</a>&nbsp;&nbsp;" ;
				}

			echo "</div>" ;
		}

		/**
		 * Converts a comma deliniated string into an array
		 * @param string $string comma deliniated list
		 * @return array
		 * @access public
		 * @since 3.0
		 */
		public function ConvertToArray($string)
		{
			return explode(',', $string);
		}

		/**
		 * Gets the height of an image
		 * @param $image string Image file
		 * @return int Image height
		 * @access protected
		 * @since 3.0
		 */
		public function GetHeight($image) {
			$sizes = getimagesize($image);
			$height = $sizes[1];
			return $height;
		}
		
		/**
		 * Gets the width of an image
		 * @param $image string Image file
		 * @return int Image width
		 * @access protected
		 * @since 3.0
		 */
		public function GetWidth($image) {
			$sizes = getimagesize($image);
			$width = $sizes[0];
			return $width;
		}

		/** 
		 * Resizes an image to scale
		 * @param $image string Image to resize
		 * @param $savedImage string Where to save the image
		 * @param $width int Width of new image
		 * @param $height int Height of new image
		 * @param $scale double Scale of new image
		 * @param $ext string Extension of image
		 * @return string New Image
		 * @access protected
		 * @since 3.0
		 */
		public function resizeScaleImage($image,$savedImage,$width,$height,$scale,$ext) {
			$newImageWidth = ceil($width * $scale);
			$newImageHeight = ceil($height * $scale);
			$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);

			if ($ext == "jpg" || $ext == "jpeg")
			{
				$source = imagecreatefromjpeg($image);
			}

			if ($ext == "png")
			{
				$source = imagecreatefrompng($image);
			}

			if ($ext == "gif")
			{
				$source = imagecreatefromgif($image);
			}


			imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
			imagejpeg($newImage,$savedImage,90);
			chmod($savedImage, 0777);
			return $savedImage;
		}
		
		/**
		 * Gets the file extension of an image
		 * @param $filename string Image file
		 * @return string File extension
		 * @access public
		 * @since 3.0
		 */
		public function GetFileExtension($filename)
		{
			return strtolower(substr(@strrchr($filename, "."),1));
		}
		
		/**
		 * Formats a Date to MM/DD/YY H:M AM/PM
		 * @param $date string Date
		 * @return string Formatted date
		 * @access public
		 * @since 3.1
		 */
		public function FormatDate($date)
		{
			return date("m/d/y h:i A", strtotime($date));
		}
	}
?>