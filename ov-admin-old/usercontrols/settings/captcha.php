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
<h1>Captcha Settings</h1>
<form action="/ov-admin/php/settings_save_captcha.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label>CAPTCHA</label>
		</div>
		<div class="form-input">
			<input onclick="toggleCaptchaSettings(this)" type="checkbox" id="enable_recaptcha" name="enable_recaptcha" value="yes" <?php if ($captcha_settings['enable_recaptcha']) { echo "checked"; } ?> />&nbsp;&nbsp;<strong>Use reCAPTCHA</strong>
			<div class="form-hint">Check this if you want to use reCAPTCHA.  This is an anti-bot checker that you can use to help combat spam. It does require signing up 
				but it is free.  Visit <a href="http://www.google.com/recaptcha">http://www.google.com/recaptcha</a> for more information.</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div id="captcha_settings" <?php if (!$captcha_settings['enable_recaptcha']) { echo "style=\"display:none\""; } ?>>
		<div class="settings-form-field">
			<div class="form-label">
				<label for="recaptcha_public_key">Public Key</label>
			</div>
			<div class="form-input">
				<input type="text" id="recaptcha_public_key" name="recaptcha_public_key" value="<?php echo $captcha_settings['recaptcha_public_key']; ?>" size="35" />
				<div class="form-hint">This is the public key provided to you by reCAPTCHA.</div>
			</div>
			<div class="clearfix"></div>
		</div>

		<div class="settings-form-field">
			<div class="form-label">
				<label for="recaptcha_private_key">Private Key</label>
			</div>
			<div class="form-input">
				<input type="text" id="recaptcha_private_key" name="recaptcha_private_key" value="<?php echo $captcha_settings['recaptcha_private_key']; ?>" size="35" />
				<div class="form-hint">This is the private key provided to you by reCAPTCHA.</div>
			</div>
			<div class="clearfix"></div>
		</div>

		<div class="settings-form-field">
			<div class="form-label">
				<label for="recaptcha_theme">reCAPTCHA Theme</label>
			</div>
			<div class="form-input">
				<select id="recaptcha_theme" name="recaptcha_theme">
					<option value="red" <?php if ($captcha_settings['recaptcha_theme'] == "red") { echo "selected"; } ?>>Red</option>
			        <option value="white" <?php if ($captcha_settings['recaptcha_theme'] == "white") { echo "selected"; } ?>>White</option>
					<option value="blackglass" <?php if ($captcha_settings['recaptcha_theme'] == "blackglass") { echo "selected"; } ?>>Black Glass</option>
					<option value="clean" <?php if ($captcha_settings['recaptcha_theme'] == "clean") { echo "selected"; } ?>>Clean</option>
				</select>
				<div class="form-hint">What theme do you want reCAPTCHA to use?</div>
			</div>
			<div class="clearfix"></div>
		</div>
	</div>
	
	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
