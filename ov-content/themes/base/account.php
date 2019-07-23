<?php
if (!$ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /login?redirecturl=/settings");
	exit();
}

$ovoUser = new ovoUser($ovUserSecurity->LoggedInUserID());
$account_page = $_GET['type'];

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title>Settings | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>
<link rel="stylesheet" type="text/css" href="/js/jquery.imgareaselect-0.9.8/css/imgareaselect-default.css" />
<script type="text/javascript" src="/js/jquery.imgareaselect-0.9.8/scripts/jquery.imgareaselect.pack.js"></script>
<script type="text/javascript">
	$(document).ready(function() {			
		$('#resizePhoto').imgAreaSelect({
			handles			: true,
			aspectRatio		: "1:1",
			minHeight		: 200,
			minWidth		: 200,
			x1				: 0,
			y1				: 0,
			x2				: 200,
			y2				: 200,
			persistent		: true,
			onSelectEnd		: function (img, selection) {
				$('#x1').val(selection.x1);
				$('#y1').val(selection.y1);
				$('#x2').val(selection.x2);
				$('#y2').val(selection.y2);
				$('#width').val(selection.width);
				$('#height').val(selection.height);
			}
		});

		$('#details').focus(function() {
			toggleUserBioTextArea('expand');
		});

		$('#details').blur(function() {
			toggleUserBioTextArea('collapse');
		});

		$('#profileForm').validate({
			rules: {
				email_address: {
					required: true,
					email: true
				}, 
				website: {
					url: true
				}
			},
			messages: {
				email_address: {
					required: "You must enter a valid email address",
					email: "You must enter a valid email address"
				},
				website: {
					url: "Your website should be a valid URL"
				}
			}
		});

		$('#passwordForm').validate({
			rules: {
				current_password: {
					required: true,
					rangelength: [6, 20]
				},
				new_password_1: {
					required: true,
					rangelength: [6, 20]
				},
				new_password_2: {
					required: true,
					rangelength: [6, 20],
					equalTo: "#new_password_1"
				}
			},
			messages: {
				current_password: {
					required: "You must enter your current password",
					rangelength: "Password must be between 6 and 20 characters"
				},
				new_password_1: {
					required: "You must enter a new password",
					rangelength: "Password must be between 6 and 20 characters"
				},
				new_password_2: {
					required: "You must enter your new password twice",
					rangelength: "Password must be between 6 and 20 characters",
					equalTo: "Passwords do not match"
				}
			}
		});
	});
</script>
</head>
<body>
	<?php include ('category-bar-hidden.php'); ?>

	<?php
	include (get_header());
	?>

	<div class="side-list">
		<ul>
			<li <?php if ($account_page == "profile") { ?> class="active-item"<?php } ?>><a href="/settings/profile">Profile</a></li>
			<li <?php if ($account_page == "preferences") { ?> class="active-item"<?php } ?>><a href="/settings/preferences">Preferences</a></li>
			<li <?php if ($account_page == "password") { ?> class="active-item"<?php } ?>><a href="/settings/password">Password</a></li>
			<li <?php if ($account_page == "avatar" || $account_page == "avatar-step-2") { ?> class="active-item"<?php } ?>><a href="/settings/avatar">Avatar</a></li>
			<li <?php if ($account_page == "notifications") { ?> class="active-item"<?php } ?>><a href="/settings/notifications">Notifications</a></li>
			<li <?php if ($account_page == "delete-account") { ?> class="active-item"<?php } ?>><a href="/settings/delete-account">Delete Account</a></li>
		</ul>
	</div>

	<div class="list-page-main-content">
	
		<!-- <div class="account-header">
			<h1>Account Settings</h1>
			<p>Here, you can manage your profile, settings, avatar and more.</p>
		</div> -->
		
		
		
		<?php if ($account_page == "preferences") { ?>
			<div class="settings-title">Preferences</div>
			<div class="account-form">
				<?php
					if (isset($_GET['success'])) {
				?>
						<div class="success-box margin_tb_10">Preferences successfully updated</div>
				<?php
					}
				?>
			
				<form action="/php/account_save_preferences.php" method="post">
					<div class="form-field">
						<div class="label-area"><label>Open Links In:</label></div>
						<div class="input-area">
							<input type="radio" id="new" name="open_links_in" value="_blank" <?php if ($ovUserSettings->OpenLinksIn() == "_blank") { echo "checked"; } ?> /> New Window/Tab<br/>
							<input type="radio" id="same" name="open_links_in" value="_self" <?php if ($ovUserSettings->OpenLinksIn() == "_self") { echo "checked"; } ?> /> Same Window
							<div class="form-hint">Do you want links to open in the same tab, or in a new tab?</div>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label>Subscribe To a Thread When I:</label></div>
						<div class="input-area">
							<input type="checkbox" id="on_submit" name="on_submit" value="yes" <?php if ($ovUserSettings->SubscribeOnSubmit()) { echo "checked"; } ?> /> Submit the link<br />
							<input type="checkbox" id="on_comment" name="on_comment" value="yes" <?php if ($ovUserSettings->SubscribeOnComment()) { echo "checked"; } ?> /> Comment on the thread
							<p class="form-hint">Do you want to subscribe to a comment thread when you post the submission and/or when you post a comment?</p>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label for="hide_comments">Hide Negatively Scored Comments</label></div>
						<div class="input-area">
							<select id="hide_comments" name="hide_comments">
								<option value="999" <?php if ($ovUserSettings->CommentThreshold() == "999") { echo "selected"; } ?>>Don't Hide</option>
								<option value="0" <?php if ($ovUserSettings->CommentThreshold() == "0") { echo "selected"; } ?>>0</option>
								<option value="-1" <?php if ($ovUserSettings->CommentThreshold() == "-1") { echo "selected"; } ?>>-1</option>
								<option value="-2" <?php if ($ovUserSettings->CommentThreshold() == "-2") { echo "selected"; } ?>>-2</option>
								<option value="-3" <?php if ($ovUserSettings->CommentThreshold() == "-3") { echo "selected"; } ?>>-3</option>
								<option value="-4" <?php if ($ovUserSettings->CommentThreshold() == "-4") { echo "selected"; } ?>>-4</option>
								<option value="-5" <?php if ($ovUserSettings->CommentThreshold() == "-5") { echo "selected"; } ?>>-5</option>
								<option value="-6" <?php if ($ovUserSettings->CommentThreshold() == "-6") { echo "selected"; } ?>>-6</option>
								<option value="-7" <?php if ($ovUserSettings->CommentThreshold() == "-7") { echo "selected"; } ?>>-7</option>
								<option value="-8" <?php if ($ovUserSettings->CommentThreshold() == "-8") { echo "selected"; } ?>>-8</option>
								<option value="-9" <?php if ($ovUserSettings->CommentThreshold() == "-9") { echo "selected"; } ?>>-9</option>
								<option value="-10" <?php if ($ovUserSettings->CommentThreshold() == "-10") { echo "selected"; } ?>>-10</option>
							</select>
							<p class="form-hint">
								Since comments can be voted up and down, do you want to have the &quot;bad&quot; comments hidden by default? (they can be unhidden) 
								If so, at which score should they be hidden?
							</p>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label>Replying to Comments</label></div>
						<div class="input-area">
							<input type="checkbox" id="prepopulate_reply" name="prepopulate_reply" value="yes" <?php if ($ovUserSettings->PrepopulateReply()) { echo "checked"; } ?> /> Pre-populate Reply Textbox
							<p class="form-hint">Do you want to have the reply textbox pre-populated with the user you're replying to?</p>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label>Likes and Dislikes</label></div>
						<div class="input-area">
							<input type="checkbox" id="publicly_display_likes" name="publicly_display_likes" value="yes" <?php if ($ovUserSettings->PubliclyDisplayLikes()) { echo "checked"; } ?> /> Publicly Display Likes &amp; Dislikes
							<p class="form-hint">Do you want your likes and dislikes (votes) to be viewable by everyone?</p>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area">&nbsp;</div>
						<div class="input-area">
							<button type="submit" class="normal-button">Save Changes</button>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			</div>
		<?php } elseif ($account_page == "password") { ?>
			<div class="settings-title">Change Your Password</div>
			
			<?php
				if (isset($_GET['error'])) {
					switch ($_GET['error']) {
						case 1:
							$error_message = "Invalid password";
							break;
						case 2:
							$error_message = "Passwords must be between 6 and 20 characters";
							break;
						case 3:
							$error_message = "New passwords don't match";
							break;
						case 4:
						default:
							$error_message = "Problem changing password";
							break;
					}
				}

				if (isset($_GET['success'])) {
			?>
					<div class="success-box margin_tb_10">Password successfully changed</div>
			<?php
				}

				if (isset($error_message)) {
			?>
					<div class="error-box margin_tb_10"><?php echo $error_message; ?></div>
			<?php
				}
			?>
			
			<div class="account-form">
				<form action="/php/account_change_password.php" method="post" id="passwordForm">
					<div class="form-field">
						<div class="label-area"><label for="current_password">Current Password <span class="error_text">*</span></label></div>
						<div class="input-area">
							<input type="password" id="current_password" name="current_password" />
						</div>
						<div class="clearfix"></div>
					</div>

					<div class="form-field">
						<div class="label-area"><label for="new_password_1">New Password <span class="error_text">*</span></label></div>
						<div class="input-area">
							<input type="password" id="new_password_1" name="new_password_1" />
						</div>
						<div class="clearfix"></div>
					</div>

					<div class="form-field">
						<div class="label-area"><label for="new_password_2">Re-Enter New Password <span class="error_text">*</span></label></div>
						<div class="input-area">
							<input type="password" id="new_password_2" name="new_password_2" />
						</div>
						<div class="clearfix"></div>
					</div>

					<div class="form-field">
						<div class="label-area"><span class="error_text">*</span> indicates required field</div>
						<div class="input-area">
							<button type="submit" id="submit" class="normal-button">Change Password</button>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			</div>
		<?php } elseif ($account_page == "avatar") { ?>
			<div class="settings-title">Edit Your Avatar</div>
			
			<div class="account-form">
				<?php if ($ovoUser->Avatar() != "" && $ovoUser->Avatar() != "/img/default_user.jpg") { ?>
					<div class="form-field align_center" id="user_avatar">
						<img src="<?php echo $ovoUser->Avatar(); ?>" alt="">
						<div class="margin_tb_10"><button onclick="if (ConfirmAction('Are you sure you want to delete your avatar?')) { DeleteAvatar(); }" class="cancel-button">Delete Avatar</a></div>
					</div>
				<?php } ?>
				
				
				<?php
					if (isset($_GET['error'])) {
						switch ($_GET['error']) {
							case 1:
								$error_message = "Not a valid image file. Avatars should be .jpg, .png, or .gif";
								break;
							case 2:
								$error_message = "File too large. Avatars should be 400KB or less";
							case 3:
							default:
								$error_message = "Unknown error";
								break;
						}
				?>
							<div class="error-box margin_tb_10"><?php echo $error_message; ?></div>
				<?php
					}
				?>
			
			
				<form action="/php/upload_avatar.php" enctype="multipart/form-data" method="post">
					<div class="form-field">
						<div class="label-area"><label for="avatar">Choose Your Avatar</label></div>
						<div class="input-area">
							<input id="avatar" name="avatar" size="35" type="file">
							<div class="form-hint">
								File should be smaller than 400k<br />
								Supported Types: .jpg, .png, .gif<br />
								Make sure you have the rights to use the image
							</div>
						</div>
						<div class="clearfix"></div>
					</div>

					<div class="form-field">
						<div class="label-area">&nbsp;</div>
						<div class="input-area">
							<button type="submit" id="submit" class="normal-button">Upload</button>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			</div>
		<?php } elseif ($account_page == "avatar-step-2") { ?>
			<div class="settings-title">Crop Your Avatar</div>
			<form action="/php/crop_avatar.php" method="post">
				<div class="form-field">
					<img id="resizePhoto" src="/ov-upload/tmp/tmp_<?php echo strtolower($ovUserSecurity->LoggedInUsername()); ?>.jpg" alt="Resize your Photo" />
				</div>

				<input type="hidden" name="x1" value="0" id="x1"/>
				<input type="hidden" name="y1" value="0" id="y1"/>
				<input type="hidden" name="x2" value="100" id="x2"/>
				<input type="hidden" name="y2" value="100" id="y2"/>
				<input type="hidden" name="width" value="100" id="width"/>
				<input type="hidden" name="height" value="100" id="height"/>

				<div class="form-field">
					<div class="label-area">&nbsp;</div>
					<div class="input-area">
						<button type="submit" id="submit" class="normal-button">Save</button>
					</div>
					<div class="clearfix"></div>
				</div>
			</form>
		<?php } elseif ($account_page == "notifications") { ?>
			<div class="settings-title">Manage Notification Settings</div>
			<div class="account-form" style="width:750px">
				
				<?php
					if (isset($_GET['success'])) {
				?>
						<div class="success-box margin_tb_10">Notification settings successfully updated</div>
				<?php
					}
				?>
				
				<form action="/php/account_save_notifications.php" method="post">
					<div class="form-field">
						<table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<th>&nbsp;</th>
								<th>None</th>
								<th>On Site</th>
								<th>Email</th>
								<th>Both</th>
							</tr>
							<tr>
								<td class="bold">When Someone Comments on a thread I'm following:</td>
								<td align="center"><input type="radio" name="alert_comments" value="NONE" <?php if ($ovUserSettings->AlertComments() == "NONE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_comments" value="SITE" <?php if ($ovUserSettings->AlertComments() == "SITE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_comments" value="EMAIL" <?php if ($ovUserSettings->AlertComments() == "EMAIL") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_comments" value="BOTH" <?php if ($ovUserSettings->AlertComments() == "BOTH") { echo "checked"; } ?> /></td>
							</tr>
							<tr>
								<td class="bold">When Someone Shares Something with Me:</td>
								<td align="center"><input type="radio" name="alert_shares" value="NONE" <?php if ($ovUserSettings->AlertShares() == "NONE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_shares" value="SITE" <?php if ($ovUserSettings->AlertShares() == "SITE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_shares" value="EMAIL" <?php if ($ovUserSettings->AlertShares() == "EMAIL") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_shares" value="BOTH" <?php if ($ovUserSettings->AlertShares() == "BOTH") { echo "checked"; } ?> /></td>
							</tr>
							<tr>
								<td class="bold">When Someone Starts Following Me:</td>
								<td align="center"><input type="radio" name="alert_followers" value="NONE" <?php if ($ovUserSettings->AlertFollowers() == "NONE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_followers" value="SITE" <?php if ($ovUserSettings->AlertFollowers() == "SITE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_followers" value="EMAIL" <?php if ($ovUserSettings->AlertFollowers() == "EMAIL") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_followers" value="BOTH" <?php if ($ovUserSettings->AlertFollowers() == "BOTH") { echo "checked"; } ?> /></td>
							</tr>
							<tr>
								<td class="bold">When Someone Favorites a Submission I Submitted:</td>
								<td align="center"><input type="radio" name="alert_favorites" value="NONE" <?php if ($ovUserSettings->AlertFavorites() == "NONE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_favorites" value="SITE" <?php if ($ovUserSettings->AlertFavorites() == "SITE") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_favorites" value="EMAIL" <?php if ($ovUserSettings->AlertFavorites() == "EMAIL") { echo "checked"; } ?> /></td>
								<td align="center"><input type="radio" name="alert_favorites" value="BOTH" <?php if ($ovUserSettings->AlertFavorites() == "BOTH") { echo "checked"; } ?> /></td>
							</tr>
						</table>
					</div>
					<div class="form-field">
						<button type="submit" id="submit" class="normal-button">Save Changes</button>
					</div>
				</form>
			</div>
		<?php } elseif ($account_page == "delete-account") { ?>
			<div class="settings-title">Delete Your Account</div>
			<?php
				if (isset($_GET['error'])) {
			?>
					<div class="error_text margin_tb_10">Invalid Password</div>
			<?php
				}
			?>
			<p class="account-delete">Are you sure you want to go? Why not stay around instead?  But OK, we understand.  Please keep in mind that you will be deleting your account. 
				This means that all traces of you will be removed from the site.  Your friends might even wonder what happened to you.  But all bets aside,
				this process is irreversible.  Once, you do it, you can't go back, not even the Admins can help you now.</p>
			<p class="account-delete">So, are you sure you want to do this?</p>
			<div class="final_warning">Are you sure you want to delete your account?</div>
			<div class="align_center">
				<button class="cancel-button" onclick="if(ConfirmAction('Are you SURE you want to delete your account?')){ ShowDeleteAccountPasswdConfirm(); }">Delete Your Account</button>
			</div>

			<div class="modal_form" title="Confirm Password" id="confirm_password_form">
				<form action="/php/delete_account.php" method="post">
					<p>Please confirm your password to delete your account.</p>
					<div class="margin_tb_15">
						<label for="confirm_password">Password</label>
						<br/>
						<input type="password" name="confirm_password" id="confirm_password" class="textbox_16" maxlength="20" style="width:396px"/>
					</div>
					<div class="align_right">
						<button type="submit" class="cancel-button">Delete Account</button>
					</div>
				</form>
			</div>
		<?php } else { ?>
			<div class="settings-title">Edit Your Profile</div>
			<div class="account-form">
				<div class="form-field">Please keep in mind that unless specified otherwise, whatever you enter here will be viewable by everyone.</div>
				
				<?php
					if (isset($_GET['error'])) {
				?>
					<div class="error-box margin_tb_10">Invalid email address</div>
				<?php
					}
					
					if (isset($_GET['success'])) {
				?>
						<div class="success-box margin_tb_10">Profile successfully updated</div>
				<?php
					}
				?>
			
			
				<form action="/php/account_save_profile.php" method="post" id="profileForm">
					<div class="form-field">
						<div class="label-area"><label for="details">Details</label></div>
						<div class="input-area">
							<textarea id="details" name="details" rows="12" cols="25"><?php echo $ovoUser->UnformattedDetails(); ?></textarea>
							<div class="form-hint">Tell us a little bit about yourself</div>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label for="email_address">Email Address <span class="error_text">*</span></label></div>
						<div class="input-area">
							<input type="email" name="email_address" id="email_address" maxlength="255" value="<?php echo $ovoUser->Email(); ?>" />
							<div class="form-hint">This will not be displayed publicly</div>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label for="website">Website</label></div>
						<div class="input-area">
							<input type="text" name="website" id="website" maxlength="255" value="<?php echo $ovoUser->Website(); ?>" />
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label for="location">Location</label></div>
						<div class="input-area">
							<input type="text" name="location" id="location" maxlength="255" value="<?php echo $ovoUser->Location(); ?>" />
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><label for="twitter_username">Twitter Username</label></div>
						<div class="input-area">
							<input type="text" name="twitter_username" id="twitter_username" maxlength="255" value="<?php echo $ovoUser->TwitterUsername(); ?>" />
							<div class="form-hint">Want to share your Twitter username?</div>
						</div>
						<div class="clearfix"></div>
					</div>
					<div class="form-field">
						<div class="label-area"><span class="error_text">*</span> indicates required field</div>
						<div class="input-area">
							<button type="submit" class="normal-button">Save Changes</button>
						</div>
						<div class="clearfix"></div>
					</div>
				</form>
			<div>
		<?php } ?>
	</div>
	<div class="clearfix"></div>
	
<?php
include (get_footer());
?>