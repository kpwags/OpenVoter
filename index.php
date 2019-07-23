<?php
/*
	OpenVoter 3.2
*/


$page = "list";
if (isset($_GET['page'])) {
	$page_type = $_GET['page'];
} else {
	$page_type = "list";
}

require './ov-header.php';

$type = "index";
if (isset($_GET['type'])) {
	$type = $_GET['type'];
} else {
	$type = "index";
}

if (isset($_GET['page'])) {
	$page_number = $_GET['page'];
} else {
	$page_number = "1";
}

switch ($page_type)
{
	case "login":
		include(ABSOLUTEPATH . get_theme_directory() . 'login.php');
		break;
	case "account":
		include(ABSOLUTEPATH . get_theme_directory() . 'account.php');
		break;
	case "notifications":
	case "alerts":
		include(ABSOLUTEPATH . get_theme_directory() . 'notifications.php');
		break;
	case "register":
	case "sign-up":
		include(ABSOLUTEPATH . get_theme_directory() . 'sign-up.php');
		break;
	case "info":
		include(ABSOLUTEPATH . get_theme_directory() . 'info.php');
		break;
	case "tools":
		include(ABSOLUTEPATH . get_theme_directory() . 'tools.php');
		break;
	case "powered-by-openvoter":
		include(ABSOLUTEPATH . get_theme_directory() . 'powered.php');
		break;
	case "submission":
		$submission_id = $_GET['id'];
		include(ABSOLUTEPATH . get_theme_directory() . 'submission.php');
		break;
	case "user":
		include(ABSOLUTEPATH . get_theme_directory() . 'user.php');
		break;
	case "submit":
		include(ABSOLUTEPATH . get_theme_directory() . 'submit.php');
		break;
	case "error":
		include(ABSOLUTEPATH . get_theme_directory() . 'error.php');
		break;
	case "recover-password":
		include(ABSOLUTEPATH . get_theme_directory() . 'recover-password.php');
		break;
	case "force-password-reset":
		include(ABSOLUTEPATH . get_theme_directory() . 'force-password-reset.php');
		break;
	case "manage-lists":
		include(ABSOLUTEPATH . get_theme_directory() . 'manage-lists.php');
		break;
	case "list":
	default:
		include(ABSOLUTEPATH . get_theme_directory() . 'list.php');
		break;
}

?>