<?php
if (!isset($ov_header)) {
	$ov_header = true;
	
	require_once( dirname(__FILE__) . '/ov-load.php');
	
	if ($page_type != "force-password-reset") {
		// don't do a validate if forcing a password reset
		$ovUserSecurity->ValidateSession();
	}
}

?>