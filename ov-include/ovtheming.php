<?php
function get_theme_directory()
{
	
	return THEMEDIR . $theme_dir . '/';
}

function get_head()
{
	return get_theme_directory() . "head.php";
}

function get_header()
{
	return get_theme_directory() . "header.php";
}

function get_footer()
{
	return get_theme_directory() . "footer.php";
}

function get_comments()
{
	return get_theme_directory() . "comments.php";
}

function get_comment()
{
	return get_theme_directory() . "comment.php";
}

function get_mobile_head()
{
	return get_theme_directory() . "mobile/head.php";
}

function get_mobile_header()
{
	return get_theme_directory() . "mobile/header.php";
}

function get_mobile_footer()
{
	return get_theme_directory() . "mobile/footer.php";
}

function get_category_bar_open()
{
	return get_theme_directory() . "category-bar.php";
}

function get_admin_head()
{
	return 'ov-admin/admin-head.php';
}

function get_admin_header()
{
	return 'ov-admin/admin-header.php';
}

function get_admin_sidebar()
{
	return 'ov-admin/admin-sidebar.php';
}
?>