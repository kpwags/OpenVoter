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
<h1>Karma Settings</h1>
<form action="/ov-admin/php/settings_save_karma.php" method="post">
	<div class="settings-form-field">
		<div class="form-label">
			<label>Karma</label>
		</div>
		<div class="form-input">
			<input onclick="toggleKarmaSettings(this)" type="checkbox" id="use_karma_system" name="use_karma_system" value="yes" <?php if ($karma_settings['use_karma_system']) { echo "checked"; } ?> />&nbsp;&nbsp;<strong>Use Karma System</strong>
			<div class="form-hint">Do you want to have a karma system for the users?</div>
		</div>
		<div class="clearfix"></div>
	</div>
	<div id="karma_settings" <?php if (!$karma_settings['use_karma_system']) { echo "style=\"display:none\""; } ?>>
		<div class="settings-form-field">
			<div class="form-label">
				<label for="karma_name">Karma Points Name</label>
			</div>
			<div class="form-input">
				<input type="text" id="karma_name" name="karma_name" value="<?php echo $karma_settings['karma_name']; ?>" size="35" maxlength="20" />
				<div class="form-hint">The name of the karma points</div>
			</div>
			<div class="clearfix"></div>
		</div>
	
		<div class="settings-form-field">
			<div class="form-label">
				<label for="points_submission">Points Per Submission</label>
			</div>
			<div class="form-input">
				<input type="text" id="points_submission" name="points_submission" value="<?php echo $karma_settings['points_submission']; ?>" size="35" maxlength="5" />
				<div class="form-hint">How many points should the user get for each submission? (Can range from -9.99 to 9.99)</div>
			</div>
			<div class="clearfix"></div>
		</div>
	
		<div class="settings-form-field">
			<div class="form-label">
				<label for="points_comment">Points Per Comment</label>
			</div>
			<div class="form-input">
				<input type="text" id="points_comment" name="points_comment" value="<?php echo $karma_settings['points_comment']; ?>" size="35" maxlength="5" />
				<div class="form-hint">How many points should the user get for each comment? (Can range from -9.99 to 9.99)</div>
			</div>
			<div class="clearfix"></div>
		</div>
		
		<div class="settings-form-field">
			<div class="form-label">
				<label for="points_comment_up">Points Per Comment Up Vote</label>
			</div>
			<div class="form-input">
				<input type="text" id="points_comment_up" name="points_comment_up" value="<?php echo $karma_settings['points_comment_up_vote']; ?>" size="35" maxlength="5" />
				<div class="form-hint">How many points should the user get for every positive vote on one of their comments? (Can range from -9.99 to 9.99)</div>
			</div>
			<div class="clearfix"></div>
		</div>
		
		<div class="settings-form-field">
			<div class="form-label">
				<label for="points_comment_down">Points Per Comment Down Vote</label>
			</div>
			<div class="form-input">
				<input type="text" id="points_comment_down" name="points_comment_down" value="<?php echo $karma_settings['points_comment_down_vote']; ?>" size="35" maxlength="5" />
				<div class="form-hint">How many points should the user get for every negative vote on one of their comments? (Can range from -9.99 to 9.99)</div>
			</div>
			<div class="clearfix"></div>
		</div>
	
		<div class="settings-form-field">
			<div class="form-label">
				<label for="points_vote">Points Per Vote</label>
			</div>
			<div class="form-input">
				<input type="text" id="points_vote" name="points_vote" value="<?php echo $karma_settings['points_vote']; ?>" size="35" maxlength="5" />
				<div class="form-hint">How many points should the user get for each vote? (Can range from -9.99 to 9.99)</div>
			</div>
			<div class="clearfix"></div>
		</div>
	
		<div class="settings-form-field">
			<div class="form-label">
				<label for="points_popular">Points Per Popular Submission</label>
			</div>
			<div class="form-input">
				<input type="text" id="points_popular" name="points_popular" value="<?php echo $karma_settings['points_popular']; ?>" size="35" maxlength="5" />
				<div class="form-hint">How many points should the user get for each submission that makes popular status? (Can range from -9.99 to 9.99)</div>
			</div>
			<div class="clearfix"></div>
		</div>
		
		<div class="settings-form-field">
			<div class="form-label">
				<label>Karma Penalties</label>
			</div>
			<div class="form-input">
				<input onclick="toggleKarmaPenaltySettings(this)" type="checkbox" id="karma_penalties" name="karma_penalties" value="yes" <?php if ($karma_settings['karma_penalties']) { echo "checked"; } ?> />&nbsp;&nbsp;<strong>Penalize for Negative Karma</strong>
				<div class="form-hint">Check this if you want to penalize users for negative karma.</div>
			</div>
			<div class="clearfix"></div>
		</div>
		
		<div id="karma_penalty_settings" <?php if (!$karma_settings['karma_penalties']) { echo "style=\"display:none\""; } ?>>
			<div class="settings-form-field">
				<div class="form-label">
					<label for="karma_penalty_1_threshold">First Threshold</label>
				</div>
				<div class="form-input">
					<select id="karma_penalty_1_threshold" name="karma_penalty_1_threshold" onchange="toggleKarmaThreshold1Settings()">
						<option value="999" <?php if ($karma_settings['karma_penalty_1_threshold'] == "999") { echo "selected"; } ?>>N/A</option>
						<option value="0" <?php if ($karma_settings['karma_penalty_1_threshold'] == "0") { echo "selected"; } ?>>0</option>
						<option value="-25" <?php if ($karma_settings['karma_penalty_1_threshold'] == "-25") { echo "selected"; } ?>>-25</option>
						<option value="-50" <?php if ($karma_settings['karma_penalty_1_threshold'] == "-50") { echo "selected"; } ?>>-50</option>
						<option value="-75" <?php if ($karma_settings['karma_penalty_1_threshold'] == "-75") { echo "selected"; } ?>>-75</option>
						<option value="-100" <?php if ($karma_settings['karma_penalty_1_threshold'] == "-100") { echo "selected"; } ?>>-100</option>
						<option value="-150" <?php if ($karma_settings['karma_penalty_1_threshold'] == "-150") { echo "selected"; } ?>>-150</option>
					</select>
					<div class="form-hint">
						Where must the user's karma be to be hit by the first penalty threshold?
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		
			
			<div id="karma_penalty_1" <?php if ($karma_settings['karma_penalty_1_threshold'] == 999) { echo "style=\"display:none\""; } ?>>
				<div class="settings-form-field">
					<div class="form-label">
						<label for="karma_penalty_1_submissions">Submissions Allowed Per 24 Hours</label>
					</div>
					<div class="form-input">
						<select id="karma_penalty_1_submissions" name="karma_penalty_1_submissions">
							<option value="999" <?php if ($karma_settings['karma_penalty_1_submissions'] == "999") { echo "selected"; } ?>>Unlimited</option>
							<option value="15" <?php if ($karma_settings['karma_penalty_1_submissions'] == "15") { echo "selected"; } ?>>15</option>
							<option value="10" <?php if ($karma_settings['karma_penalty_1_submissions'] == "10") { echo "selected"; } ?>>10</option>
							<option value="9" <?php if ($karma_settings['karma_penalty_1_submissions'] == "9") { echo "selected"; } ?>>9</option>
							<option value="8" <?php if ($karma_settings['karma_penalty_1_submissions'] == "8") { echo "selected"; } ?>>8</option>
							<option value="7" <?php if ($karma_settings['karma_penalty_1_submissions'] == "7") { echo "selected"; } ?>>7</option>
							<option value="6" <?php if ($karma_settings['karma_penalty_1_submissions'] == "6") { echo "selected"; } ?>>6</option>
							<option value="5" <?php if ($karma_settings['karma_penalty_1_submissions'] == "5") { echo "selected"; } ?>>5</option>
							<option value="4" <?php if ($karma_settings['karma_penalty_1_submissions'] == "4") { echo "selected"; } ?>>4</option>
							<option value="3" <?php if ($karma_settings['karma_penalty_1_submissions'] == "3") { echo "selected"; } ?>>3</option>
							<option value="2" <?php if ($karma_settings['karma_penalty_1_submissions'] == "2") { echo "selected"; } ?>>2</option>
							<option value="1" <?php if ($karma_settings['karma_penalty_1_submissions'] == "1") { echo "selected"; } ?>>1</option>
							<option value="0" <?php if ($karma_settings['karma_penalty_1_submissions'] == "0") { echo "selected"; } ?>>0</option>
						</select>
						<div class="form-hint">
							How many submissions are allowed per 24 hours
						</div>
					</div>
					<div class="clearfix"></div>
				</div>		
				<div class="settings-form-field">
					<div class="form-label">
						<label for="karma_penalty_1_comments">Comments Allowed Per 24 Hours</label>
					</div>
					<div class="form-input">
						<select id="karma_penalty_1_comments" name="karma_penalty_1_comments">
							<option value="999" <?php if ($karma_settings['karma_penalty_1_comments'] == "999") { echo "selected"; } ?>>Unlimited</option>
							<option value="15" <?php if ($karma_settings['karma_penalty_1_comments'] == "15") { echo "selected"; } ?>>15</option>
							<option value="10" <?php if ($karma_settings['karma_penalty_1_comments'] == "10") { echo "selected"; } ?>>10</option>
							<option value="9" <?php if ($karma_settings['karma_penalty_1_comments'] == "9") { echo "selected"; } ?>>9</option>
							<option value="8" <?php if ($karma_settings['karma_penalty_1_comments'] == "8") { echo "selected"; } ?>>8</option>
							<option value="7" <?php if ($karma_settings['karma_penalty_1_comments'] == "7") { echo "selected"; } ?>>7</option>
							<option value="6" <?php if ($karma_settings['karma_penalty_1_comments'] == "6") { echo "selected"; } ?>>6</option>
							<option value="5" <?php if ($karma_settings['karma_penalty_1_comments'] == "5") { echo "selected"; } ?>>5</option>
							<option value="4" <?php if ($karma_settings['karma_penalty_1_comments'] == "4") { echo "selected"; } ?>>4</option>
							<option value="3" <?php if ($karma_settings['karma_penalty_1_comments'] == "3") { echo "selected"; } ?>>3</option>
							<option value="2" <?php if ($karma_settings['karma_penalty_1_comments'] == "2") { echo "selected"; } ?>>2</option>
							<option value="1" <?php if ($karma_settings['karma_penalty_1_comments'] == "1") { echo "selected"; } ?>>1</option>
							<option value="0" <?php if ($karma_settings['karma_penalty_1_comments'] == "0") { echo "selected"; } ?>>0</option>
						</select>
						<div class="form-hint">
							How many comments are allowed per 24 hours
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
			
			<div class="settings-form-field">
				<div class="form-label">
					<label for="karma_penalty_2_threshold">Second Threshold</label>
				</div>
				<div class="form-input">
					<select id="karma_penalty_2_threshold" name="karma_penalty_2_threshold" onchange="toggleKarmaThreshold2Settings()">
						<option value="999" <?php if ($karma_settings['karma_penalty_2_threshold'] == "999") { echo "selected"; } ?>>N/A</option>
						<option value="0" <?php if ($karma_settings['karma_penalty_2_threshold'] == "0") { echo "selected"; } ?>>0</option>
						<option value="-25" <?php if ($karma_settings['karma_penalty_2_threshold'] == "-25") { echo "selected"; } ?>>-25</option>
						<option value="-50" <?php if ($karma_settings['karma_penalty_2_threshold'] == "-50") { echo "selected"; } ?>>-50</option>
						<option value="-75" <?php if ($karma_settings['karma_penalty_2_threshold'] == "-75") { echo "selected"; } ?>>-75</option>
						<option value="-100" <?php if ($karma_settings['karma_penalty_2_threshold'] == "-100") { echo "selected"; } ?>>-100</option>
						<option value="-150" <?php if ($karma_settings['karma_penalty_2_threshold'] == "-150") { echo "selected"; } ?>>-150</option>
					</select>
					<div class="form-hint">
						Where must the user's karma be to be hit by the second penalty threshold?
					</div>
				</div>
				<div class="clearfix"></div>
			</div>
		
			<div id="karma_penalty_2" <?php if ($karma_settings['karma_penalty_2_threshold'] == 999) { echo "style=\"display:none\""; } ?>>
				<div class="settings-form-field">
					<div class="form-label">
						<label for="karma_penalty_2_submissions">Submissions Allowed Per 24 Hours</label>
					</div>
					<div class="form-input">
						<select id="karma_penalty_2_submissions" name="karma_penalty_2_submissions">
							<option value="999" <?php if ($karma_settings['karma_penalty_2_submissions'] == "999") { echo "selected"; } ?>>Unlimited</option>
							<option value="15" <?php if ($karma_settings['karma_penalty_2_submissions'] == "15") { echo "selected"; } ?>>15</option>
							<option value="10" <?php if ($karma_settings['karma_penalty_2_submissions'] == "10") { echo "selected"; } ?>>10</option>
							<option value="9" <?php if ($karma_settings['karma_penalty_2_submissions'] == "9") { echo "selected"; } ?>>9</option>
							<option value="8" <?php if ($karma_settings['karma_penalty_2_submissions'] == "8") { echo "selected"; } ?>>8</option>
							<option value="7" <?php if ($karma_settings['karma_penalty_2_submissions'] == "7") { echo "selected"; } ?>>7</option>
							<option value="6" <?php if ($karma_settings['karma_penalty_2_submissions'] == "6") { echo "selected"; } ?>>6</option>
							<option value="5" <?php if ($karma_settings['karma_penalty_2_submissions'] == "5") { echo "selected"; } ?>>5</option>
							<option value="4" <?php if ($karma_settings['karma_penalty_2_submissions'] == "4") { echo "selected"; } ?>>4</option>
							<option value="3" <?php if ($karma_settings['karma_penalty_2_submissions'] == "3") { echo "selected"; } ?>>3</option>
							<option value="2" <?php if ($karma_settings['karma_penalty_2_submissions'] == "2") { echo "selected"; } ?>>2</option>
							<option value="1" <?php if ($karma_settings['karma_penalty_2_submissions'] == "1") { echo "selected"; } ?>>1</option>
							<option value="0" <?php if ($karma_settings['karma_penalty_2_submissions'] == "0") { echo "selected"; } ?>>0</option>
						</select>
						<div class="form-hint">
							How many submissions are allowed per 24 hours
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
				<div class="settings-form-field">
					<div class="form-label">
						<label for="karma_penalty_2_comments">Comments Allowed Per 24 Hours</label>
					</div>
					<div class="form-input">
						<select id="karma_penalty_2_comments" name="karma_penalty_2_comments">
							<option value="999" <?php if ($karma_settings['karma_penalty_2_comments'] == "999") { echo "selected"; } ?>>Unlimited</option>
							<option value="15" <?php if ($karma_settings['karma_penalty_2_comments'] == "15") { echo "selected"; } ?>>15</option>
							<option value="10" <?php if ($karma_settings['karma_penalty_2_comments'] == "10") { echo "selected"; } ?>>10</option>
							<option value="9" <?php if ($karma_settings['karma_penalty_2_comments'] == "9") { echo "selected"; } ?>>9</option>
							<option value="8" <?php if ($karma_settings['karma_penalty_2_comments'] == "8") { echo "selected"; } ?>>8</option>
							<option value="7" <?php if ($karma_settings['karma_penalty_2_comments'] == "7") { echo "selected"; } ?>>7</option>
							<option value="6" <?php if ($karma_settings['karma_penalty_2_comments'] == "6") { echo "selected"; } ?>>6</option>
							<option value="5" <?php if ($karma_settings['karma_penalty_2_comments'] == "5") { echo "selected"; } ?>>5</option>
							<option value="4" <?php if ($karma_settings['karma_penalty_2_comments'] == "4") { echo "selected"; } ?>>4</option>
							<option value="3" <?php if ($karma_settings['karma_penalty_2_comments'] == "3") { echo "selected"; } ?>>3</option>
							<option value="2" <?php if ($karma_settings['karma_penalty_2_comments'] == "2") { echo "selected"; } ?>>2</option>
							<option value="1" <?php if ($karma_settings['karma_penalty_2_comments'] == "1") { echo "selected"; } ?>>1</option>
							<option value="0" <?php if ($karma_settings['karma_penalty_2_comments'] == "0") { echo "selected"; } ?>>0</option>
						</select>
						<div class="form-hint">
							How many comments are allowed per 24 hours
						</div>
					</div>
					<div class="clearfix"></div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="button-field">
		<button type="submit" class="normal-button">Save Changes</button>
	</div>
</form>
</div>
