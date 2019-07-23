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

<div class="settings-form">
<h1>Comment Settings</h1>
<form action="/ov-admin/php/settings_save_comment.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label for="edit_comments">Allow Editing</label>
		</div>
		<div class="form-input">
			<select id="edit_comments" name="edit_comments">
				<option value="0" <?php if ($comment_settings['comment_modify_time'] == "0") { echo "selected"; } ?>>No Editing</option>
				<option value="15" <?php if ($comment_settings['comment_modify_time'] == "15") { echo "selected"; } ?>>15 Minutes</option>
				<option value="30" <?php if ($comment_settings['comment_modify_time'] == "30") { echo "selected"; } ?>>30 Minutes</option>
				<option value="45" <?php if ($comment_settings['comment_modify_time'] == "45") { echo "selected"; } ?>>45 Minutes</option>
				<option value="60" <?php if ($comment_settings['comment_modify_time'] == "60") { echo "selected"; } ?>>1 Hour</option>
				<option value="-1" <?php if ($comment_settings['comment_modify_time'] == "-1") { echo "selected"; } ?>>Forever</option>
			</select>
			<div class="form-hint">
				Do you want users to be able to edit their comments? If so, for how long after posting?
			</div>
		</div>
		<div class="clearfix"></div>
	</div>
			
	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
