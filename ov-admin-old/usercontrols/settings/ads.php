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
<h1>Ads And Google Analytics</h1>
<form action="/ov-admin/php/settings_save_ads.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label for="top_ad">Top Ad</label>
		</div>
		<div class="form-input">
			<textarea id="top_ad" name="top_ad" rows="25" cols="25" style="width:100%;height:150px"><?php echo $ads['top_ad']; ?></textarea>
			<div class="form-hint">Put the code for your top banner ad here. Recommended Size: 728x90</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="settings-form-field">
		<div class="form-label">
			<label for="side_ad">Side Ad</label>
		</div>
		<div class="form-input">
			<textarea id="side_ad" name="side_ad" rows="25" cols="25" style="width:100%;height:150px"><?php echo $ads['side_ad']; ?></textarea>
			<div class="form-hint">Put the code for your sidebar ad here. Recommended Size: 180x150</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="settings-form-field">
		<div class="form-label">
			<label for="google_analytics">Google Analytics</label>
		</div>
		<div class="form-input">
			<textarea id="google_analytics" name="google_analytics" rows="25" cols="25" style="width:100%;height:150px"><?php echo $ads['google_analytics_code']; ?></textarea>
			<p class="formHint">Put the code for your Google Analytics here</p>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>