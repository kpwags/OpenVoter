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

	$debug_mode = false;

	require_once 'ovusersecurity.php';
	$ovUserSecurity = new ovUserSecurity();
	
	$ovUserSecurity->ValidateSession();
	
	if ($ovUserSecurity->IsUserLoggedIn()) {
		$x1 = strip_tags($_POST['x1']);
		$y1 = strip_tags($_POST['y1']);
		$x2 = strip_tags($_POST['x2']);
		$y2 = strip_tags($_POST['y2']);
		$width = strip_tags($_POST['width']);
		$height = strip_tags($_POST['height']);

		$upload_dir = "./../ov-upload/tmp";
		$avatar_dir = "./../ov-upload/avatars";
		$upload_path = $upload_dir . "/";
		$avatar_path = $avatar_dir . "/";
		$tmp_image_name = strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".jpg");
		$avatar_image_name = strtolower($ovUserSecurity->LoggedInUsername() . ".jpg");
		$tmp_image_location = $upload_path . $tmp_image_name;
		$avatar_image_location = $avatar_path . $avatar_image_name;

		$cropped = resizeAvatarImage($avatar_image_location, $tmp_image_location, $width, $height, $x1, $y1);
		
		require_once 'ovouser.php';
		$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
		
		if (file_exists($tmp_image_location)) {
			unlink($tmp_image_location);
		}
		
		if (file_exists($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".jpg"))) {
			unlink($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".jpg"));
		}
		
		if (file_exists($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".jpeg"))) {
			unlink($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".jpeg"));
		}
		
		if (file_exists($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".png"))) {
			unlink($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".png"));
		}
		
		if (file_exists($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".gif"))) {
			unlink($upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".gif"));
		}
		
		$ovoUser->SaveAvatar("/ov-upload/avatars/" . $avatar_image_name);
		header("Location: /settings/avatar");
		exit();
		
	} else { // user not logged in
		header("Location: /login?redirecturl=" . urlencode("/settings/avatar"));
		exit();
	}
	
	function resizeAvatarImage($thumb_image_name, $image, $width, $height, $start_width, $start_height){
		$newImage = imagecreatetruecolor($width,$height);
		$source = imagecreatefromjpeg($image);
		imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$width,$height,$width,$height);
		imagejpeg($newImage,$thumb_image_name,90);
		chmod($thumb_image_name, 0777);
		return $thumb_image_name;
	}	
?>