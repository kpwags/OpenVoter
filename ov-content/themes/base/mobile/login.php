<?php
if (isset($_REQUEST['redirecturl'])) {
	$previous_page = $_GET['redirecturl'];
} elseif (!isset($_SERVER['HTTP_REFERER']) || strripos($_SERVER['HTTP_REFERER'], "register")) {
	$previous_page = "/";
} else {
	$previous_page = $_SERVER['HTTP_REFERER'];
}

include (get_mobile_head());
?>
<title>Login | <?php echo $ovSettings->Title(); ?></title>
</head>
<body>
<?php
include (get_mobile_header());
?>
<h1>Login</h1>
<div class="login-form">
	<form action="/php/mobile/login-user.php" method="post">
		<?php
			if (isset($_GET['error'])) {
				if ($_GET['error'] == 1) { $message = "Invalid Username or Password"; }
				if ($_GET['error'] == 2) { $message = "Sorry but your account has been suspended"; }
				if ($_GET['error'] == 3) { $message = "Sorry but your account has been banned"; }
				if ($_GET['error'] == 4) { $message = "Sorry but your account has been banned"; }
				
		?>
				<div class="error-line"><?php echo $message; ?></div>
		<?php
			}
		?>
		<div class="form-field">
			<label for="username">Username or Email</label><br/>
			<input type="text" name="username" id="username" placeholder="username" />
		</div>
		<div class="form-field">
			<label for="password">Password</label><br/>
			<input type="password" name="password" id="password" placeholder="password" />
		</div>
		<div class="form-field">
			<input type="checkbox" id="remember" name="remember" value="yes" />&nbsp;&nbsp;<label for="remember">Remember Login</label>
		</div>
		<div class="form-field">
			<input type="hidden" value="<?php echo $previous_page; ?>" name="previous_page" />
			<button class="normal-button button-full-width" type="submit">Log In</button>
		</div>
	</form>
</div>
<?php include(get_mobile_footer()); ?>