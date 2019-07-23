<?php
$page = "home";
if (isset($_GET['page'])) {
	$page_type = $_GET['page'];
} else {
	$page_type = "home";
}

require './../ov-header.php';

if (isset($_GET['page'])) {
	$page_number = $_GET['page'];
} else {
	$page_number = "1";
}

switch ($page_type)
{
	case "login":
		include(ABSOLUTEPATH . 'ov-admin/login.php');
		break;
	case "home":
	default:
		include(ABSOLUTEPATH . 'ov-admin/home.php');
		break;
}

?>