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

// pagination, show_votes, show_down_votes, friends_page_enabled, show_voting_buttons_friends_page, show_voting_buttons_user_profile
?>

<div class="settings-form">
<h1>Submission Settings</h1>
<form action="/ov-admin/php/settings_save_submission.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label for="pagination">Pagination</label>
		</div>
		<div class="form-input">
			<input type="text" id="pagination" name="pagination" value="<?php echo $submission_settings['pagination']; ?>" size="35" />
			<div class="form-hint">How many submissions do you want to show per page?</div>
		</div>
		<div class="clearfix"></div>
	</div>

	<div class="settings-form-field">
		<div class="form-label">
			<label>Show Votes</label>
		</div>
		<div class="form-input">
			<input type="checkbox" id="show_votes" name="show_votes" value="yes" <?php if ($submission_settings['show_votes']) { echo "checked"; } ?> />&nbsp;&nbsp;<strong>Show Who Voted on a Submission</strong>
			<div class="form-hint">Do you want users to see who voted on a submission? How about those who voted down?</div>
		</div>
		<div class="clearfix"></div>
	</div>

	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>