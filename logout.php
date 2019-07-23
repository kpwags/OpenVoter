<?php
	require './ov-header.php';

	$previous_page = $_SERVER['HTTP_REFERER'];

	$ovUserSecurity->LogoutUser();
	
	header("Location: $previous_page");
	exit();
?>