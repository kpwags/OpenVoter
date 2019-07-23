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
	
	// the name of your database
	define('DB_NAME', 'openvoter');

	// the database user for the database.
	define('DB_USERNAME', 'user');

	// the password for your database user
	define('DB_PASSWORD', 'secret');

	// the database host, will likely be localhost, but make sure to check with your hosting provider.
	define('DB_HOST', 'localhost');
	
	// the id of the site (generally 1 if you only have one site)
	define('SITE_ID', '1');
	
	// the database table prefix, make sure it is unique if sharing a database.
	// If you don't use or want a prefix, leave it blank
	define('DB_PREFIX', 'ov_');
?>