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

$current_theme = $ovAdminSettings->GetCurrentThemeInfo();
$available_themes = $ovAdminSettings->GetAvailableThemes();
?>

<div class="settings_form">
	<h1>Themes Settings</h1>
	<h3>Current Theme</h3>
		<div class="current_theme">
			<?php if ($current_theme) { ?>
				<div><img src="<?php echo $current_theme['screen']; ?>" alt="<?php echo htmlspecialchars($current_theme['name']); ?>" width="300" /></div>
				<div class="current_theme_name"><?php echo $current_theme['name']; ?></div>
			<?php } else { ?>
				<div class="current_theme_name">Current Theme Not Detected</div>
			<?php } ?>
		</div>
	<h3>Available Themes</h3>
	<div class="italic">Click on the theme to apply it</div>
	<?php if ($available_themes) { ?>
		<ul class="available_theme_list">
			<?php foreach ($available_themes as $theme) { ?>
				<li>
					<div>
						<a href="/ov-admin/php/apply_theme.php?xml=<?php echo urlencode($theme['xml']); ?>" onclick="return ConfirmAction('Are you sure you want to apply this theme?')">
							<img src="<?php echo $theme['screen']; ?>" alt="<?php echo htmlspecialchars($theme['name']); ?>" width="200" /></div>
						</a>
					<div>
						<a href="/ov-admin/php/apply_theme.php?xml=<?php echo urlencode($theme['xml']); ?>" onclick="return ConfirmAction('Are you sure you want to apply this theme?')">
							<?php echo htmlspecialchars($theme['name']); ?>
						</a>
					</div>
					<?php if ($theme['author'] != "" || $theme['website'] != "") { ?>
						<div class="theme_author">
							<?php if ($theme['author'] != "") { ?>
								<em>Designed By <strong><?php echo $theme['author']; ?></strong></em><br/>
							<?php } ?>
							<?php if ($theme['website'] != "") { ?>
								<a href="<?php echo $theme['website']; ?>" target="_blank">Website</a>
							<?php } ?>
						</div>
					<?php } ?>
				</li>
			<?php } ?>
		</ul>
		<div class="clearfix"></div>
	<?php } else { ?>
		<div class="margin_tb_10">No Other Themes Available</div>
	<?php } ?>
</div>