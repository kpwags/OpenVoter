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
	
	require_once 'ovadminsettings.php';
	$ovAdminSettings = new ovAdminSettings();

	if ($_POST['use_karma_system'] == "yes") {
		$use_karma_system = true;
	} else {
		$use_karma_system = false;
	}

	$karma_name = strip_tags($_POST['karma_name']);
	$points_submission = strip_tags($_POST['points_submission']);
	$points_comment = strip_tags($_POST['points_comment']);
	$points_comment_up = strip_tags($_POST['points_comment_up']);
	$points_comment_down = strip_tags($_POST['points_comment_down']);
	$points_vote = strip_tags($_POST['points_vote']);
	$points_popular = strip_tags($_POST['points_popular']);
	$karma_penalty_1_threshold = strip_tags($_POST['karma_penalty_1_threshold']);
	$karma_penalty_1_comments = strip_tags($_POST['karma_penalty_1_comments']);
	$karma_penalty_1_submissions = strip_tags($_POST['karma_penalty_1_submissions']);
	$karma_penalty_2_threshold = strip_tags($_POST['karma_penalty_2_threshold']);
	$karma_penalty_2_comments = strip_tags($_POST['karma_penalty_2_comments']);
	$karma_penalty_2_submissions = strip_tags($_POST['karma_penalty_2_submissions']);
	
	if ($_POST['karma_penalties'] == "yes") {
		$karma_penalties = true;
	} else {
		$karma_penalties = false;
	}
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessPreferences())
	{
		$ovAdminSettings->SaveKarmaSettings($use_karma_system, $karma_name, $points_submission, $points_comment, 
			$points_comment_up, $points_comment_down, $points_vote, $points_popular, 
			$karma_penalties, $karma_penalty_1_threshold, $karma_penalty_1_comments, $karma_penalty_1_submissions, 
			$karma_penalty_2_threshold, $karma_penalty_2_comments, $karma_penalty_2_submissions);
		
		header("Location: /ov-admin/settings?page=karma");
		exit();
	}
	elseif ($ovAdminSecurity->IsAdminLoggedIn() && !$ovAdminSecurity->CanAccessPreferences())
	{
		header("Location: /ov-admin");
		exit();
	}
	else
	{
		header("Location: /ov-admin/login?redirecturl=" . urlencode("/ov-admin/settings?page=karma"));
		exit();
	}
?>