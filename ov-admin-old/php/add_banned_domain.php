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
	
	require_once 'ovadminbans.php';
	$ovAdminBans = new ovAdminBans();

	$domain_name = strip_tags($_GET['domain_name']);
	$reason = strip_tags($_GET['reason']);
	
	if ($ovAdminSecurity->IsAdminLoggedIn() && $ovAdminSecurity->CanAccessContent())
	{		
		$ovAdminBans->AddBannedDomain($domain_name, $reason);
	}
	
	echo "OK";
?>