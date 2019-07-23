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
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	if (!$ovAdminSecurity->IsAdminLoggedIn()) {
		header("Location: /ov-admin/login");
		exit();
	}
	
	if (!$ovAdminSecurity->CanAccessPreferences()) {
		header("Location: /ov-admin");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadmincategories.php';
	$ovAdminCategories = new ovAdminCategories();
	
	if (isset($_GET['category_id'])) {
		$category_page = "subcategories";
	} else {
		$category_page = "parent_categories";
	}
	
	switch($category_page) {
		case "subcategories":
			$parent_category_name = $ovAdminCategories->GetCategoryName($_GET['category_id']);
			$category_list = $ovAdminCategories->GetChildCategories($_GET['category_id']);
			break;
		case "parent_categories":
		default:
			$category_list = $ovAdminCategories->GetParentCategories();
			break;
	}
	
	$current_section = "categories";
?>

<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Admin Console | <?php echo $ovSettings->Title(); ?></title>
	<link rel="stylesheet" href="/css/jquery-ui/jquery-ui-1.8.5.custom.css" type="text/css" />
	<link rel="stylesheet" type="text/css" href="/ov-admin/css/master.css" />
	<link rel="shortcut icon" href="/img/favicon.ico" />
	<script type="text/javascript" src="/js/jquery-1.6.4.min.js"></script>
	<script type="text/javascript" src="/js/jquery-ui-1.8.5.custom.min.js"></script>
	<script type="text/javascript" src="/ov-admin/js/openvoteradmin.js"></script>
</head>
<body>
	<?php include 'admin_header.php'; ?>
	<div class="content">
		<div id="sidebar"><?php include 'admin_sidebar.php'; ?></div>
		<div id="main-content">
			<?php
				switch($category_page) {
					case "subcategories":
						include 'categories/child_categories.php';
						break;
					case "parent_categories":
					default:
						include 'categories/parent_categories.php';
						break;
				}
			?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
	
	<div class="modal_form" title="Add Category" id="add_category_form">
		<div class="margin_tb_15">
			<label for="category_name">Name</label>
			<br/>
			<input type="text" name="category_name" id="category_name" value="" maxlength="35" style="width:402px"/>
			<div class="modal_error_field error_text" id="category_name_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="category_url_name">URL Name</label>
			<br/>
			<input type="text" name="category_url_name" id="category_url_name" value="" maxlength="35" style="width:402px"/>
			<div class="modal_error_field error_text" id="category_url_name_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="category_sort_order">Sort Order</label>
			<br/>
			<input type="text" name="category_sort_order" id="category_sort_order" value="" maxlength="5" style="width:402px"/>
		</div>
		<div class="align_right">
			<input type="hidden" id="parent_category_id" name="parent_category_id" value="" />
			<button onclick="if (ValidateCategory()) { AddCategory(); }" class="normal-button">Add</button>
		</div>
	</div>
	
	<div class="modal_form" title="Edit Category" id="edit_category_form">
		<div class="margin_tb_15">
			<label for="edit_category_name">Name</label>
			<br/>
			<input type="text" name="edit_category_name" id="edit_category_name" value="" maxlength="35" style="width:402px"/>
			<div class="modal_error_field error_text" id="edit_category_name_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="edit_category_url_name">URL Name</label>
			<br/>
			<input type="text" name="edit_category_url_name" id="edit_category_url_name" value="" maxlength="35" style="width:402px"/>
			<div class="modal_error_field error_text" id="edit_category_url_name_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="edit_category_sort_order">Sort Order</label>
			<br/>
			<input type="text" name="edit_category_sort_order" id="edit_category_sort_order" value="" maxlength="5" style="width:402px"/>
		</div>
		<div class="align_right">
			<input type="hidden" id="edit_category_id" name="edit_category_id" value="" />
			<button onclick="EditCategory()" class="normal-button">Save</button>
		</div>
	</div>
</body>
</html>