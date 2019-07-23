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

if (!is_dir(ABSOLUTEPATH . get_theme_directory() . 'mobile')) {
	header("Location: /");
	exit();
}

switch ($page_type)
{
	case "submission":
		include(ABSOLUTEPATH . get_theme_directory() . 'mobile/submission.php');
		break;
	case "login":
		include(ABSOLUTEPATH . get_theme_directory() . 'mobile/login.php');
		break;
	case "user":
		include(ABSOLUTEPATH . get_theme_directory() . 'mobile/user.php');
		break;
	case "notifications":
		include(ABSOLUTEPATH . get_theme_directory() . 'mobile/notifications.php');
		break;
	case "list":
	default:
		include(ABSOLUTEPATH . get_theme_directory() . 'mobile/list.php');
		break;
}

?>