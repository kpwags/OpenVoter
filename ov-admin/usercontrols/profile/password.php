<?php
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
?>

<div class="admin-form">
<h1>Change Your Password</h1>
<?php
	if (isset($_GET['error'])) {
		switch ($_GET['error']) {
			case 1:
				$error_message = "Invalid password";
				break;
			case 2:
				$error_message = "Passwords must be between 6 and 20 characters";
				break;
			case 3:
				$error_message = "New passwords don't match";
				break;
			case 4:
			default:
				$error_message = "Problem changing password";
				break;
		}
	}
	
	if (isset($_GET['success'])) {
?>
		<div class="success_text margin_tb_10">Password successfully changed</div>
<?php
	}
	
	if (isset($error_message)) {
?>
		<div class="error_text margin_tb_10"><?php echo $error_message; ?></div>
<?php
	}
?>
<form action="/ov-admin/php/profile_change_password.php" method="post">
	<div class="form-field">
		<label for="current_password">Current Password</label><br/>
		<input type="password" id="current_password" name="current_password" size="35" />
		<p class="form-hint">Your current password</p>
	</div>

	<div class="form-field">
		<label for="new_password_1">New Password</label><br/>
		<input type="password" id="new_password_1" name="new_password_1" size="35" />
		<p class="form-hint">Your new password (should be between 6-20 characters)</p>
	</div>
	
	<div class="form-field">
		<label for="new_password_2">Re-Enter New Password</label><br/>
		<input type="password" id="new_password_2" name="new_password_2" size="35" />
		<p class="form-hint">Re-enter new password</p>
	</div>
	
	<div class="form-field button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
