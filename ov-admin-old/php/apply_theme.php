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
	
	if (!isset($_GET['xml'])) {
		header("Location: /ov-admin/settings?page=themes");
		exit();
	}
	
	$xml = $_GET['xml'];
	
	$xml_file = "./../../ov-content/themes/" . $xml;
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessPreferences())
	{
		$objDOM = new DOMDocument(); 
		$objDOM->load($xml_file);

		$root_theme_dir_node = $objDOM->getElementsByTagName("rootFolder");
		
		if ($root_theme_dir_node->length > 0) {
			$root_theme_dir = $root_theme_dir_node->item(0)->nodeValue; 
		}
		
		$ovAdminSettings->ApplyTheme($xml, $root_theme_dir);

		header("Location: /ov-admin/settings?page=themes");
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