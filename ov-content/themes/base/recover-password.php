<?php
include (get_head());
?>
<title>Recover Password | <?php echo $ovSettings->Title(); ?></title>
</head>
<body>
	<?php
	include (get_header());
	?>
	<h1>Recover Password</h1>
	<div class="recover-form">
		<div id="recover-step-1">
			<form action="#" method="post" onsubmit="GetSecurityQuestion();return false;">
				<div class="form-field">
					<label for="email">Please Enter Your E-Mail Address</label><br/>
					<input type="email" id="email" name="email" />
				</div>
				<div class="form-field"><button class="normal-button">Continue</button></div>
			</form>
		</div>
		<div id="recover-step-2" style="display:none">
			<div class="font_16" id="security-question"></div>
			<form action="#" method="post" onsubmit="CheckSecurityAnswer();return false;">
				<div class="form-field">
					<label for="answer">Answer</label><br/>
					<input type="text" id="answer" name="answer" />
				</div>
				<div class="form-field"><button class="normal-button">Continue</button></div>
			</form>
		</div>
		<div id="recover-step-3" style="display:none">
			<h3>Please Enter your New Password</h3>
			<div class="form-field">
				<label for="password-1">New Password</label><br/>
				<input type="password" id="password-1" name="password-1" />
				<div class="error-field" id="password-1-error"></div>
			</div>
			<div class="form-field">
				<label for="password-2">Re-Enter Password</label><br/>
				<input type="password" id="password-2" name="password-2" />
				<div class="error-field" id="password-2-error"></div>
			</div>
			<div class="form-field"><button class="normal-button" onclick="if(ValidateResetPassword()) { ResetPassword(); }">Continue</button></div>
		</div>
		<div id="recover-complete" style="display:none">
			<h3>Your password has been reset.</h3>
			<p>You can go to the <a href="/login" title="Login">Login Page</a> to Login to <?php echo $ovSettings->Title(); ?></p>
		</div>
		<input type="hidden" id="user-email" />
	</div>
	
	<!-- MESSAGE BOX -->
	<div id="modalMessageBox" title="Error">
		<div id="error_message_line">This is an Error</div>
	</div>
	<!-- END MESSAGE BOX -->
<?php
include (get_footer());
?>