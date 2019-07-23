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
	
	require_once 'ovutilities.php';
	$ovUtilities = new ovUtilities();
	
	$ovUserSecurity->ValidateSession();
	
	if ($ovUserSecurity->IsUserLoggedIn()) {
		//Get the file information
		$userfile_name = $_FILES["avatar"]["name"];
		$userfile_tmp = $_FILES["avatar"]["tmp_name"];
		$userfile_size = $_FILES["avatar"]["size"];
		$filename = basename($_FILES["avatar"]["name"]);
		$file_ext = $ovUtilities->GetFileExtension($filename);

		$max_file = 409600;
		$upload_dir = "./../ov-upload/tmp";
		$upload_path = $upload_dir . "/";
		$image_name = strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . "." . $file_ext);
		$image_location = $upload_path . $image_name;
		$saved_image_location = $upload_path . strtolower("tmp_" . $ovUserSecurity->LoggedInUsername() . ".jpg");
		$max_width = 600;
		$max_height = 600;
		
		if(($_FILES["avatar"]["error"] == 0))
		{
			$file_type = $_FILES["avatar"]["type"];
			if ($file_type != "image/gif" && $file_type != "image/jpeg" && $file_type != "image/pjpeg" && $file_type != "image/png" && $file_type != "image/x-png") {
				header("Location: /settings/avatar?error=1");
				exit;
			}

			if ($userfile_size > $max_file) {
				header("Location: /settings/avatar?error=2");
				exit;
			}
						
			if ($debug_mode) {
				echo "<p>FILE TYPE VAL: " . $_FILES["avatar"]["type"] . "</p>";
				exit();
			}
		} else {
			if ($debug_mode) {
				echo "<p>ERROR VAL: " . $_FILES["avatar"]["error"] . "</p>";
				exit();
			}
		}
		
		if (isset($_FILES["avatar"]["name"]))
		{
			if(!is_dir($upload_dir)){
				mkdir($upload_dir, 0777);
				chmod($upload_dir, 0777);
			}

			move_uploaded_file($userfile_tmp, $image_location);
			chmod ($image_location, 0777);

			$width = getWidth($image_location);
			$height = getHeight($image_location);

			//Scale the image if it is greater than the width set above
			if ($width > $max_width){
				$scale = $max_width / $width;
				$uploaded = resizeImage($image_location,$saved_image_location,$width,$height,$scale,$file_ext);
			} else {
				$scale = 1;
				$uploaded = resizeImage($image_location,$saved_image_location,$width,$height,$scale,$file_ext);
			}
		}
		else
		{
			header("Location: /settings/avatar?error=3");
			exit;
		}
		
		header("Location: /settings/avatar-step-2");
		exit();
	} else { // user not logged in
		header("Location: /login?redirecturl=" . urlencode("/settings/avatar"));
		exit();
	}
	
	
	function resizeImage($image,$savedImage,$width,$height,$scale,$ext) {
		$newImageWidth = ceil($width * $scale);
		$newImageHeight = ceil($height * $scale);
		$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	
		if ($ext == "jpg" || $ext == "jpeg")
		{
			$source = imagecreatefromjpeg($image);
		}
		
		if ($ext == "png")
		{
			$source = imagecreatefrompng($image);
		}

		if ($ext == "gif")
		{
			$source = imagecreatefromgif($image);
		}
		
		
		imagecopyresampled($newImage,$source,0,0,0,0,$newImageWidth,$newImageHeight,$width,$height);
		imagejpeg($newImage,$savedImage,90);
		chmod($savedImage, 0777);
		return $savedImage;
	}

	function getHeight($image) {
		$sizes = getimagesize($image);
		$height = $sizes[1];
		return $height;
	}
	
	function getWidth($image) {
		$sizes = getimagesize($image);
		$width = $sizes[0];
		return $width;
	}
?>