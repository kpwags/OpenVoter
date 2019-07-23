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

<script type="text/javascript" >
tinyMCE.init({
        mode : "textareas",
        theme : "simple"   //(n.b. no trailing comma, this will be critical as you experiment later)
});
</script >

<div class="settings-form">
<h1>Policies</h1>
<form action="/ov-admin/php/settings_save_policies.php" method="post" class="uniForm">
	<div class="settings-form-field">
		<div class="bold">About</div>
		<textarea name="about_site" style="width:100%;height:200px" rows="40" class="mceEditor"><?php echo $policies['about_site']; ?></textarea>
	</div>

	<div class="settings-form-field">
		<div class="bold">Privacy Policy</div>
		<textarea name="privacy_policy" style="width:100%;height:200px" rows="40" class="mceEditor"><?php echo $policies['privacy_policy']; ?></textarea>
	</div>
	
	<div class="settings-form-field">
		<div class="bold">Terms of Use</div>
		<textarea name="terms_of_use" style="width:100%;height:200px" rows="40" class="mceEditor"><?php echo $policies['terms_of_use']; ?></textarea>
	</div>
	
	<div class="settings-form-field">
		<div class="bold">Help</div>
		<textarea name="site_help" style="width:100%;height:200px" rows="40" class="mceEditor"><?php echo $policies['site_help']; ?></textarea>
	</div>

	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
