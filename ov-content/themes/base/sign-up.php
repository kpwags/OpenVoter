<?php
include (get_head());
?>
<?php if (isset($_GET['complete']) && $_GET['complete'] == "yes") { ?>
	<title>Registration Complete | <?php echo $ovSettings->Title(); ?></title>
<?php } else { ?>
	<title>Sign Up | <?php echo $ovSettings->Title(); ?></title>
<?php } ?>
<script type="text/javascript">
	<?php echo "var RecaptchaOptions = { theme : '" . $ovSettings->RecaptchaTheme() . "' };"; ?>
</script>
</head>
<body>
	<?php
	include (get_header());
	?>
	<?php if (isset($_GET['complete']) && $_GET['complete'] == "yes") { ?>
		<div class="margin_tb_20">Thank you for registering with <?php echo $ovSettings->Title(); ?>, to begin using the site, please go to the 
			<a href="/login" title="Login Page">Login Page</a></div>
	<?php } else { ?>
		<div class="sign-up-form">
			<h2>Sign Up Now</h2>
			
			<?php 
				/*
				 * ERROR CODES
				 * 1 - UNKNOWN
				 * 2 - NO USERNAME
				 * 3 - INVALID USERNAME
				 * 4 - USERNAME TAKEN
				 * 5 - NO EMAIL
				 * 6 - INVALID EMAIL
				 * 7 - EMAIL TAKEN
				 * 8 - NO PASSWORD
				 * 9 - PASSWORDS DON'T MATCH
				 * 10 - PASSWORDS NOT LONG ENOUGH
				 * 11 - NO ANSWER
				 * 12 - BAD RECAPTCHA
				 */

				if (isset($_GET['error'])) {
					switch ($_GET['error']) {
						case 2:
							$message = "You must enter a username";
							break;
						case 3:
							$message = "Invalid characters in username";
							break;
						case 4:
							$message = "Username already in use";
							break;
						case 5:
							$message = "You must enter your email";
							break;
						case 6:
							$message = "Invalid email address";
							break;
						case 7:
							$message = "Email already in use";
							break;
						case 8:
							$message = "You need a password";
							break;
						case 9: 
							$message = "Passwords don't match";
							break;
						case 10:
							$message = "Passwords need to be between 6 and 20 characters";
							break;
						case 11:
							$message = "You must give a security question answer";
							break;
						case 12:
							$message = "Incorrect Human CAPTCHA text";
							break;
						case 1:
						default:
							$message = "Unknown error";
							break;
					}
					
					echo "<div class=\"margin_tb_20 error_text\">$message</div>";
				} 
			?>
			
			<form action="/php/register_user.php" method="post" onsubmit="return ValidateRegistration()">
				<div class="form-field">
					<label for="username">Desired Username <span class="required">*</span></label><br/>
					<input type="text" name="username" id="username" onblur="ValidateUsername()" />
					<img class="input-icon" id="username_ok" src="/<?php echo get_theme_directory(); ?>img/icons/ok.png" alt="" />
					<img class="input-icon" id="username_not_ok" src="/<?php echo get_theme_directory(); ?>img/icons/not-ok.png" alt="" />
					<div class="error-field" id="username_error"></div>
				</div>
				<div class="form-field">
					<label for="email">Email Address <span class="required">*</span></label><br/>
					<input type="email" name="email" id="email" onblur="ValidateEmail()" />
					<img class="input-icon" id="email_ok" src="/<?php echo get_theme_directory(); ?>img/icons/ok.png" alt="" />
					<img class="input-icon" id="email_not_ok" src="/<?php echo get_theme_directory(); ?>img/icons/not-ok.png" alt="" />
					<div class="error-field" id="email_error"></div>
				</div>
				<div class="form-field">
					<label for="password1">Password <span class="required">*</span></label><br/>
					<input type="password" name="password1" id="password1" />
					<div class="error-field" id="password1_error"></div>
				</div>
				<div class="form-field">
					<label for="password2">Re-Enter Password <span class="required">*</span></label><br/>
					<input type="password" name="password2" id="password2" onblur="CheckPassword()" />
					<div class="error-field" id="password2_error"></div>
				</div>
				<div class="form-field">
					<label for="securityquestion">Security Question <span class="required">*</span></label><br/>
					<select id="securityquestion" name="securityquestion">
						<option value="What was your first car?">What was your first car?</option>
						<option value="What was your first pet's name?">What was your first pet's name?</option>
						<option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
					</select>
				</div>
				<div class="form-field">
					<label for="securityanswer">Answer <span class="required">*</span></label><br/>
					<input type="text" name="securityanswer" id="securityanswer" onblur="CheckSecurityQuestion()"/>
					<div class="error-field" id="answer_error"></div>
				</div>
				<div class="form-field">
					<label for="tou">Terms of Use</label><br/>
					<textarea cols="60" rows="15" name="tou" id="tou" readonly="readonly"><?php echo strip_tags($ovSettings->TermsOfUse()); ?></textarea>
				</div>
				<div class="form-field">
					<input type="checkbox" name="agreetou" id="agreetou" value="yes" />&nbsp;&nbsp;<label for="agreetou">I Agree to the <a href="/terms">Terms of Use</a></label>
					<div class="error-field" id="tou_error"></div>
				</div>
			
				<?php if ($ovSettings->EnableRecaptcha()) { ?>
					<div class="form-field">
						<label>Are You Human?</label><br/>
						<?php
							require_once('recaptcha/recaptchalib.php');
							$publickey = $ovSettings->RecaptchaPublicKey();
							echo recaptcha_get_html($publickey);
						?>
					</div>
				<?php } ?>
			
				<div class="form-field">
					<button type="submit" class="normal-button">Create Your Account</button>
				</div>
			</form>
		</div>
	
		<div class="sign-up-sidebar">
			<!-- 440px Sidebar -->
			<h3>Why Sign Up?</h3>
			<p>Sign up now to enjoy the full experience of the site. Comment, Vote, Share...get involved!</p>
		</div>
		<div class="clearfix"></div>
	<?php } ?>
	
<?php
include (get_footer());
?>