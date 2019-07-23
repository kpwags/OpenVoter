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
	
	require_once 'ovadmincategories.php';
	$ovAdminCategories = new ovAdminCategories();
	
	require_once 'ovutilities.php';
	$ovUtilities = new ovUtilities();

	$debug_mode = true;

	$name = strip_tags($_GET['name']);
	$url_name = $ovUtilities->ConvertToUrl(strip_tags($_GET['url_name']), '_');
	$sort_order = strip_tags($_GET['sort']);
	$parent_id = "";
	
	if (isset($_GET['parent_id'])) {
		$parent_id = $_GET['parent_id'];
	}
	
	if ($parent_id == "") {
		$parent_id = false;
	}

	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessPreferences() && $ovAdminCategories->IsCategoryUrlAvailable($url_name))
	{		
		if (IsValidInput($name, $url_name)) {
			$ovAdminCategories->AddCategory($name, $url_name, $sort_order, $parent_id);
		}
	}
	
	echo "OK";
	
	function IsValidInput($name, $url_name)
	{
		$is_valid = true;
		
		if (trim($name) == "") {
			$is_valid = false;
		}
		
		if (trim($url_name) == "") {
			$is_valid = false;
		}
		
		return $is_valid;
	}
?>