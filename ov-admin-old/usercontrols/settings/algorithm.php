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
<h1>Algorithm Settings</h1>
<form action="/ov-admin/php/settings_save_algorithm.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label for="algorithm">Algorithm</label>
		</div>
		<div class="form-input">
			<select id="algorithm" name="algorithm" onchange="toggleAlgorithmSettings()">
				<option value="static" <?php if ($algorithm_settings['algorithm'] == "static") { echo "selected"; } ?>>Static</option>
		        <option value="dynamic" <?php if ($algorithm_settings['algorithm'] == "dynamic") { echo "selected"; } ?>>Dynamic</option>
			</select>
			<div class="form-hint">
				Which popular algorithm do you want to use? This defines how a submission is determined to be 'popular'
				<br/><b>Static</b> - This algorithm makes a submission popular if it hits a set number of votes
				<br /><b>Dynamic</b> - This algorithm makes a submission popular if it reaches a score better than the average score of submissions over the previous week
			</div>
		</div>
		<div class="clearfix"></div>
	</div>

	<div class="settings-form-field" id="threshold_field" <?php if ($algorithm_settings['algorithm'] == "dynamic") { echo "style=\"display:none\""; } ?>>
		<div class="form-label">
			<label for="threshold">Static Threshold</label>
		</div>
		<div class="form-input">
			<input type="text" id="threshold" name="threshold" value="<?php echo $algorithm_settings['threshold']; ?>" size="35" maxlength="20" />
			<div class="form-hint">This is the threshold that makes a story popular for the static algorithm so if you want a submission to become popular when it hits 5 
				positive votes, put in 5 here. (MUST be an integer)</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
