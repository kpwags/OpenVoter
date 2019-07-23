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
	
	$title = $_SESSION['title'];
	$summary = $_SESSION['summary'];
	$tags = $_SESSION['tags'];
	$thumbnail = $_SESSION['thumbnail'];
	$url = $_SESSION['url'];
	$type = $_SESSION['type'];
	$categories = $_SESSION['categories'];
	
	$category_array = explode(",", $categories);
	
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
	
	// clean up cropped photo
	//if (file_exists("./../img/img_temp/" + $temp_filename)) {
	//	@unlink("./../img/img_temp/" + $temp_filename);
	//}
	
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
		header("Location: /");
		exit();
	}
?>