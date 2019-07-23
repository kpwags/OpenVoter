<?php
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
	
	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();
	
	$keywords = $_POST['keywords'];

	if (isset($_POST['submission_type'])) {
		$sub_type = $_POST['submission_type'];
	} else {
		$sub_type = "all";
	}

	if (isset($_POST['submission_popular'])) {
		$popular = $_POST['submission_popular'];
	} else {
		$popular = "all";
	}

	if (isset($_POST['ordering'])) {
		$ordering = $_POST['ordering'];
	} else {
		$ordering = "date";
	}
	
	$keywords = str_replace(" ", "+", $keywords) ;
	
	header("Location: /search?q=$keywords&subtype=$sub_type&display_popular=$popular&ordering=$ordering");
	exit();
?>