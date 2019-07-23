<?php
	ini_set("include_path", ".:./:./ov-include:./../ov-include:./../../ov-include:./ov-admin/ov-include:./../ov-admin/ov-include:./../:./../../:./usercontrols:./../usercontrols:./../../usercontrols");
	
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
	
	require_once 'ovsettings.php';
	$ovSettings = new ovSettings();
	
	require_once 'ovadminsecurity.php';
	$ovAdminSecurity = new ovAdminSecurity();
	
	if (!$ovAdminSecurity->IsAdminLoggedIn()) {
		header("Location: /ov-admin/login");
		exit();
	}
	
	if (!$ovAdminSecurity->CanAccessAdmins()) {
		header("Location: /ov-admin");
		exit();
	}
	
	require_once 'ovadminreporting.php';
	$ovAdminReporting = new ovAdminReporting();
	
	require_once 'ovadminmanagement.php';
	$ovAdminManagement = new ovAdminManagement();

	$current_section = "admins";
	
	$admins = $ovAdminManagement->GetAdmins();
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
			<div class="margin_tb_10" style="display:none" id="error_line"></div>
			<?php if ($admins && count($admins) > 0) { ?>
				<table width="710" border="0" cellspacing="0" cellpadding="0" class="grid_table">
					<thead>
						<tr>
							<th width="150">Full Name</th>
							<th width="110">Username</th>
							<th width="150">Role</th>
							<th width="150">&nbsp;</th>
							<th width="75">&nbsp;</th>
							<th width="75">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($admins as $admin) { ?>
							<tr>
								<td><?php echo $admin['full_name']; ?></td>
								<td><?php echo $admin['username']; ?></td>
								<td><?php echo $admin['role_name']; ?></td>
								<td><a href="javascript:ShowResetForm('<?php echo $admin['id']; ?>')" title="Reset Password">Reset Password</a></td>
								<td><img src="/ov-admin/img/icons/edit.png" alt="">&nbsp;<a href="javascript:OpenEditAdminForm('<?php echo $admin['id']; ?>', '<?php echo $admin['full_name']; ?>', '<?php echo $admin['email']; ?>', '<?php echo $admin['role_name']; ?>')" title="Edit Admin">Edit</a></td>
								<td>
									<?php if ($admin['can_delete']) { ?>
										<img src="/ov-admin/img/icons/delete.png" alt="">&nbsp;<a onclick="return ConfirmAction('Are You sure you want to delete this admin?')" href="/ov-admin/php/delete_admin?id=<?php echo $admin['id']; ?>" title="Delete Admin">Delete</a></td>
									<?php } else { ?>
										&nbsp;
									<?php } ?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				<div class="add_row">
					<img><img src="/ov-admin/img/icons/add.png" alt=""><a href="javascript:OpenAddAdminForm()" title="Add Admin">Add Admin</a>
				</div>
			<?php } else { ?>
				<div class="margin_tb_15">There aren't any admins, how are you here?!?</div>
			<?php } ?>
		</div>
	</div>
	<?php //include 'footer.php'; ?>
	<div class="modal_form" title="Edit Admin" id="edit_admin_form">
		<div class="margin_tb_15">
			<label for="edit_full_name">Full Name</label>
			<br/>
			<input type="text" name="edit_full_name" id="edit_full_name" value="" maxlength="255" style="width:402px"/>
		</div>
		<div class="margin_tb_15">
			<label for="edit_email">Email</label>
			<br/>
			<input type="text" name="edit_email" id="edit_email" value="" maxlength="255" style="width:402px"/>
		</div>
		<div class="margin_tb_15">
			<input type="radio" name="role" id="edit_role_admin" value="1"> Administrator
			<br />
			<input type="radio" name="role" id="edit_role_mod" value="2"> Moderator
		</div>
		<div class="align_right">
			<input type="hidden" name="edit_admin_id" id="edit_admin_id" />
			<button onclick="EditAdmin()" class="normal-button">Save Changes</button>
		</div>
	</div>
	
	<div class="modal_form" title="Add Admin" id="add_admin_form">
		<div class="margin_tb_15">
			<label for="add_username">Username</label>
			<br/>
			<input type="text" name="add_username" id="add_username" value="" maxlength="255" style="width:402px"/>
			<div class="modal_error_field error_text" id="add_username_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="add_full_name">Full Name</label>
			<br/>
			<input type="text" name="add_full_name" id="add_full_name" value="" maxlength="255" style="width:402px"/>
			<div class="modal_error_field error_text" id="add_full_name_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="add_email">Email</label>
			<br/>
			<input type="text" name="add_email" id="add_email" value="" maxlength="255" style="width:402px"/>
			<div class="modal_error_field error_text" id="add_email_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="add_password_1">Password</label>
			<br/>
			<input type="password" name="add_password_1" id="add_password_1" value="" maxlength="20" style="width:402px"/>
			<div class="modal_error_field error_text" id="add_password_1_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="add_password_2">Re-Enter Password</label>
			<br/>
			<input type="password" name="add_password_2" id="add_password_2" value="" maxlength="20" style="width:402px"/>
			<div class="modal_error_field error_text" id="add_password_2_error"></div>
		</div>
		<div class="margin_tb_15" id="role_field">
			<input type="radio" name="role" id="add_role_admin" value="1" checked="checked"> Administrator
			<br />
			<input type="radio" name="role" id="add_role_mod" value="2"> Moderator
		</div>
		<div class="align_right">
			<button onclick="AddAdmin()" class="normal-button">Add</button>
		</div>
	</div>
	
	<div class="modal_form" title="Reset Password" id="reset_password_form">
		<div class="margin_tb_15">
			<label for="reset_password_1">Password</label>
			<br/>
			<input type="password" name="reset_password_1" id="reset_password_1" value="" maxlength="20" style="width:402px"/>
			<div class="modal_error_field error_text" id="reset_password_1_error"></div>
		</div>
		<div class="margin_tb_15">
			<label for="reset_password_2">Re-Enter Password</label>
			<br/>
			<input type="password" name="reset_password_2" id="reset_password_2" value="" maxlength="20" style="width:402px"/>
			<div class="modal_error_field error_text" id="reset_password_2_error"></div>
		</div>
		<div class="align_right">
			<input type="hidden" id="reset_admin_id" />
			<button onclick="if (ValidateReset()) { ResetPassword(); }" class="normal-button">Reset</button>
		</div>
	</div>
	
	<div id="modalMessageBox" title="Error">
		<div id="error_message_line">This is an Error</div>
	</div>
</body>
</html>