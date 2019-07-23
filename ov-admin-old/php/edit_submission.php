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
	require_once 'ov-config.php';

	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	require_once 'ovadmincontent.php';
	$ovAdminContent = new ovAdminContent();

	$title = strip_tags($_POST['edit_title']);
	$summary = strip_tags($_POST['edit_summary']);
	$url = strip_tags($_POST['edit_submission_url']);
	$submission_id = $_POST['edit_submission_id'];
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessContent())
	{		
		if (IsValidInput($title, $summary, $url)) {
			$ovAdminContent->EditSubmission($submission_id, $title, $summary, $url);
		}
	}

	header("Location: /ov-admin/content?type=submission&id=$submission_id");
	exit();
	
	function IsValidInput($title, $summary, $url)
	{
		$is_valid = true;
		
		if (trim($title) == "") {
			$is_valid = false;
		}
		
		if (trim($summary) == "") {
			$is_valid = false;
		}
		
		if (trim($url) == "") {
			$is_valid = false;
		}
		
		return $is_valid;
	}
?>