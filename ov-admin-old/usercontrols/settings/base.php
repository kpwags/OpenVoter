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
<h1>Base Settings</h1>
<form action="/ov-admin/php/settings_save_base.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label for="root_url">Root URL</label>
		</div>
		<div class="form-input">
			<input type="text" id="root_url" name="root_url" value="<?php echo $base_settings['root_url']; ?>" size="35" />
			<div class="form-hint">This the full URL of your site (e.g. http://www.example.com)</div>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<div class="settings-form-field">
		<div class="form-label">
			<label for="site_title">Title</label>
		</div>
		<div class="form-input">
			<input type="text" id="site_title" name="site_title" value="<?php echo $base_settings['title']; ?>" size="35" />
			<div class="form-hint">This is your site's title.</div>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<div class="settings-form-field">
		<div class="form-label">
			<label for="blog">Blog</label>
		</div>
		<div class="form-input">
			<input type="text" id="blog" name="blog" value="<?php echo $base_settings['blog']; ?>" size="35" />
			<div class="form-hint">Put the full URL of your site's blog here.</div>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<div class="settings-form-field">
		<div class="form-label">
			<label>API</label>
		</div>
		<div class="form-input">
			<input type="checkbox" id="enable_api" name="enable_api" value="yes" <?php if ($base_settings['enable_api']) { echo "checked"; } ?>/>&nbsp;&nbsp;<strong>Enable API</strong>
			<div class="form-hint">Enable the site's API to users.</div>
		</div>
		<div class="clearfix"></div>
	</div>
		
	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
