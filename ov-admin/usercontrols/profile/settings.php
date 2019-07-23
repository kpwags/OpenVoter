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

$settings = $ovAdminSecurity->GetProfileSettings();
?>

<div class="admin-form">
<h1>Your Settings</h1>
<form action="/ov-admin/php/profile_save_settings.php" method="post">
	
		<div class="form-field">
			<label for="full_name">Full Name</label><br/>
			<input type="text" id="full_name" name="full_name" value="<?php echo $settings['full_name']; ?>" size="35" />
			<p class="form-hint">Your full name</p>
		</div>

		<div class="form-field">
			<label for="email">Email</label><br/>
			<input type="text" id="email" name="email" value="<?php echo $settings['email']; ?>" size="35" />
			<p class="form-hint">Your email address</p>
		</div>
		
		<div class="form-field">
			<p><label>Email Notifications</label></p>
			<ul>
				<li>
					<label for="email_reports">
						<input type="checkbox" id="email_reports" name="email_reports" value="yes" <?php if ($settings['email_reports']) { echo "checked"; } ?> /> New Reports
					</label>
				</li>
				<li>
					<label for="email_feedback">
						<input type="checkbox" id="email_feedback" name="email_feedback" value="yes" <?php if ($settings['email_feedback']) { echo "checked"; } ?> /> New Feedback
					</label>
				</li>
			</ul>
			<p class="form-hint">Do you want to receive notification emails for new reports and feedback?</p>
		</div>
		
		<div class="form-field button-field">
			<button type="submit" class="normal-button">Save Changes</button>
		</div>
</form>
</div>
