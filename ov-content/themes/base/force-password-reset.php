<?php
if (!$ovUserSecurity->IsUserLoggedIn()) {
	header("Location: /login");
	exit();
}

$alert_count = $ovAlerting->GetAlertCount();
if ($alert_count > 0) {
	$alert_count_text = " ($alert_count) ";
} else {
	$alert_count_text = "";
}

include (get_head());
?>
<title>Reset Password | <?php echo $ovSettings->Title() . $alert_count_text; ?></title>

</head>
<body>
	<?php
	include (get_header());
	?>

	<div class="sign-up-form">
		<h2>Reset Password and Security Question</h2>
		<p>Recently, an admin reset your password.  Because of this, you must now change your password and security 
			question. You cannot do anything on the site until you do this.  If this was in error, please let the 
			<a href="/feedback">admins</a> know immediately!</p>
		
		<?php if (isset($_REQUEST['error'])) { ?>
			<?php $error_code = $_REQUEST['error']; ?>
			<?php if ($error_code == 1) { ?>
				<div class="margin_tb_15 error_text">You must enter a password</div>
			<?php } elseif ($error_code == 2) { ?>
				<div class="margin_tb_15 error_text">Passwords don't match</div>
			<?php } elseif ($error_code == 3) { ?>
				<div class="margin_tb_15 error_text">Passwords must be between 6 and 20 characters</div>
			<?php } elseif ($error_code == 4) { ?>
				<div class="margin_tb_15 error_text">You must enter a security answer</div>
			<?php } else { ?>
				<div class="margin_tb_15 error_text">An unknown error occurred</div>
			<?php } ?>
		<?php } ?>
		
		<form action="/php/reset_user_password.php" method="post" onsubmit="return ValidateResetUserPassword()">
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
				<button type="submit" class="normal-button">Reset Password</button>
			</div>
		</form>
	</div>
	
<?php
include (get_footer());
?>