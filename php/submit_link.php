<?php
	session_start();
	ini_set("include_path", ".:./:./../:./ov-include:./../ov-include");
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
	
	require_once 'ov-config.php';
	
	// DEBUG FLAG
	$debug_enabled = false;
	
	require_once 'ovcontent.php';
	$ovContent = new ovContent();
	
	require_once 'ovutilities.php';
	$ovUtilities = new ovUtilities();
	
	require_once 'ovsubmission.php';
	$ovSubmission = new ovSubmission();
	
	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();
	
	$title = strip_tags($_POST['title']);
	$summary = strip_tags($_POST['summary']);
	$tags = strtolower(strip_tags($_POST['tags']));
	$thumbnail = $_POST['submission-thumbnail-url'];
	$url = $_POST['submission-url'];
	$type = strtoupper(strip_tags($_POST['submission-type']));
	$categories = $_REQUEST['category'];
	
	$num_categories = 0;
	$category_array = array();
	$cat_string = "1,";
	array_push($category_array, 1);	// popular is ALWAYS a category
	foreach ($categories as $cat)
	{
		if($cat != "")
		{
			array_push($category_array, $cat);
			$cat_string .= $cat . ",";
			$num_categories++ ;
		}
	}
	
	$cat_string = substr($cat_string, 0, strlen($cat_string) - 1);
	
	if ($debug_enabled)
	{
		echo "<p>POST CONTENT</p>";
		echo "<div>";
			echo "Title: $title<br/>";
			echo "Summary: $summary<br/>";
			echo "Tags: $tags<br/>";
			echo "URL: $url<br/>";
			echo "Type: $type<br/>";
			echo "Thumbnail: $thumbnail<br/>";
			echo "Raw Categories: " . print_r($categories) . "<br/>";
			echo "Categories: " . print_r($category_array) . "<br/>";
		echo "</div>";
	}
	
	if (trim($type) == "") {
		// no type
		if ($debug_enabled) {
			echo "<p>Type Error</p>";
		} else {
			header ("Location: /submit?error=1");
			exit();
		}
	}
	
	// let's do some validation
	if (trim($url) == "" && $type != "SELF") {
		// no url
		if ($debug_enabled) {
			echo "<p>URL Error</p>";
		} else {
			header ("Location: /submit?error=1");
			exit();
		}
	} 
	
	
	
	if (trim($title) == "")	{
		// no title
		if ($debug_enabled) {
			echo "<p>Title Error</p>";
		} else {
			header ("Location: /submit?error=1&url=" . urlencode($url));
			exit();
		}
	}
	
	if (trim($tags) == "") {
		// no tags
		if ($debug_enabled) {
			echo "<p>Tags Error</p>";
		} else {
			header ("Location: /submit?error=4&url=" . urlencode($url));
			exit();
		}
	}
	
	if ($num_categories > 6 || $num_categories <= 0) {
		// no categories or too many
		
		if ($debug_enabled) {
			echo "<p>Categories Error</p>";
		} else {
			header ("Location: /submit?error=3&url=" . urlencode($url));
			exit();
		}
	}
	
	// check for dupes
	$keyword_string = str_replace(" ", "+", $title) ;
	$submission_count = $ovSubmission->SearchCount($keyword_string, "all", "all", "3 DAY");
	
	if ($submission_count > 0) {
		// store data in session
		
		$_SESSION['title'] = $title;
		$_SESSION['summary'] = $summary;
		$_SESSION['tags'] = $tags;
		$_SESSION['thumbnail'] = $thumbnail;
		$_SESSION['url'] = $url;
		$_SESSION['type'] = $type;
		$_SESSION['categories'] = $cat_string;
		$_SESSION['keyword_string'] = $keyword_string;
		
		header("Location: /submit/duplicates-detected");
		exit();
	}
	
	// ok, all good...moving on
	
	$tags = $ovContent->BreakDownTags($tags);
	
	$db_tags = $ovContent->AddTags($tags);
	
	if ($debug_enabled) 
	{
		echo "<p>Tags Broken Down</p>";
		echo "<div>Before DB: " . print_r($tags) . "</div>";
		echo "<div> After DB: " . print_r($db_tags) . "</div>";
		exit();
	}
	
	
	$submission_id = $ovSubmission->AddSubmission($title, $summary, $url, $type);
	
	if ($submission_id)
	{
		$filename = "submission-" . $submission_id . ".jpg";
		if ($type == "PHOTO") {
			$last_three = substr($url, -3);
			$last_four = substr($url, -4);
			if ($last_three == "jpg" || $last_three == "gif" || $last_three == "png" || $last_four == "jpeg")
			{
				$thumbnail = $url;
			}
			
			if ($thumbnail == "") {
				$ovSubmission->SetSubmissionDBThumbnail($submission_id, "/img/default_photo.jpg");
			} else {
				$last_three = substr($thumbnail, -3);
				$last_four = substr($thumbnail, -4);
				if ($last_three == "jpg" || $last_three == "gif" || $last_three == "png" || $last_four == "jpeg") {
					$ovSubmission->SetSubmissionThumbnail($submission_id, $thumbnail, $filename);
				} else {
					$ovSubmission->SetSubmissionDBThumbnail($submission_id, "/img/default_photo.jpg");
				}
			}
		}
		
		$ovSubmission->AddSubmissionCategories($submission_id, $category_array);
		$ovSubmission->AddSubmissionTags($submission_id, $db_tags);
		$ovSubmission->AddVote($submission_id, 1);
		
		header("Location: /" . strtolower($type) . "/$submission_id/" . $ovUtilities->ConvertToUrl($title));
		exit();
	}
	else
	{
		
	}
?>